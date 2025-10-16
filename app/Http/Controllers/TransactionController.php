<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $keySearch = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $transactionList = Rental::with('customer', 'car', 'payment')
            ->when($keySearch, function ($query, $keySearch) {
                $query->where(function ($q) use ($keySearch) {
                    $q->where('start_date', 'like', "%$keySearch%")
                        ->orWhere('end_date', 'like', "%$keySearch%")
                        ->orWhere('total_price', 'like', "%$keySearch%")
                        ->orWhereHas('customer', function ($qry) use ($keySearch) {
                            $qry->where('name', 'like', "%$keySearch%");
                        })
                        ->orWhereHas('car', function ($qry) use ($keySearch) {
                            $qry->where('model', 'like', "%$keySearch%");
                        })
                        ->orWhereHas('payment', function ($qry) use ($keySearch) {
                            $qry->where('method', 'like', "%$keySearch%");
                        });

                    if (str_contains(strtolower($keySearch), 'dibayar')) {
                        $q->orWhere('status', 'paid');
                    } elseif (str_contains(strtolower($keySearch), 'tertunda')) {
                        $q->orWhere('status', 'pending');
                    }
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate]);
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $customerList = Customer::select('id', 'name')->get();
        $carList = Car::select('id', 'model', 'price_per_day', 'plate_number')->where('status', 'available')->get();

        return inertia('Admin/Transaction/Index', [
            'transactionList' => $transactionList,
            'customerList' => $customerList,
            'carList' => $carList,
            'filters' => [
                'search' => $keySearch,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'customer_id' => 'required',
            'car_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'total_price' => 'required',
            'method' => 'nullable',
            'status' => 'required'
        ], [
            'customer_id.required' => 'Customer harus dipilih',
            'car_id.required' => 'Mobil harus dipilih',
            'start_date.required' => 'Mulai sewa tidak boleh kosong',
            'end_date.required' => 'Selesai sewa tidak boleh kosong',
            'total_price.required' => 'Total price tidak boleh kosong',
            'status.required' => 'Pilih status'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $rental = Rental::create([
                    'uuid' => Str::uuid(),
                    'customer_id' => $request->input('customer_id'),
                    'car_id' => $request->input('car_id'),
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                    'total_price' => $request->input('total_price'),
                    'status' => $request->input('status')
                ]);

                Payment::create([
                    'rental_id' => $rental->uuid,
                    'amount' => $rental->total_price,
                    'method' => $request->input('method'),
                    'status' => $rental->status
                ]);

                Car::where('id', $rental->car_id)->update([
                    'status' => 'rented'
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }

        return to_route('admin.transaction.index');
    }


    public function update(Request $request, $uuid)
    {
        $request->validate([
            'customer_id' => 'required',
            'car_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'total_price' => 'required',
            'method' => 'required'
        ], [
            'customer_id.required' => 'Customer harus dipilih',
            'car_id.required' => 'Mobil harus dipilih',
            'start_date.required' => 'Mulai sewa tidak boleh kosong',
            'end_date.required' => 'Selesai sewa tidak boleh kosong',
            'total_price.required' => 'Total price tidak boleh kosong',
            'method.required' => 'Metode pembayaran tidak boleh kosong'
        ]);

        $rowRentalUpdated = Rental::findOr($uuid);
        $rowPaymentUpdated = Payment::where('rental_id', $uuid);

        try {
            DB::transaction(function () use ($request, $rowRentalUpdated, $rowPaymentUpdated) {
                $rowRentalUpdated->update([
                    'customer_id' => $request->input('customer_id'),
                    'car_id' => $request->input('car_id'),
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                    'total_price' => $request->input('total_price')
                ]);

                $rowPaymentUpdated->update([
                    'amount' => $rowRentalUpdated->total_price,
                    'method' => $request->input('method')
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }

        return to_route('admin.transaction.index');
    }



    public function destroy($uuid, Request $request)
    {
        $carId = $request->input('car_id');
        try {
            DB::transaction(function () use ($uuid, $carId) {
                $rowRental = Rental::findOr($uuid);
                $rowRental->delete();

                Car::where('id', $carId)->update([
                    'status' => 'available'
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }


    public function returnedCar($uuid)
    {
        $rental = Rental::where('uuid', $uuid)->first();

        $rental->update([
            'status_car' => 'returned',
            'return_date' => now()
        ]);

        Car::where('id', $rental->car_id)->update([
            'status' => 'available'
        ]);
    }


    public function lunasPayment(Request $request, $uuid)
    {
        // dd($request->all());
        $request->validate([
            'method' => 'required'
        ], [
            'method.required' => 'Pilih metode pembayaran'
        ]);

        try {
            DB::transaction(function () use($uuid, $request) {
                Rental::where('uuid', $uuid)->update([
                    'status' => 'paid'
                ]);
                Payment::where('rental_id', $uuid)->update([
                    'status' => 'paid',
                    'method' => $request->input('method')
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }
}
