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
    public function index()
    {
        $transactionList = Rental::with('customer', 'car', 'payment')->orderByDesc('created_at')->paginate(10);
        $customerList = Customer::select('id', 'name')->get();
        $carList = Car::select('id', 'model', 'price_per_day', 'plate_number')->get();

        return inertia('Admin/Transaction/Index', [
            'transactionList' => $transactionList,
            'customerList' => $customerList,
            'carList' => $carList
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
            'method' => 'required'
        ], [
            'customer_id.required' => 'Customer harus dipilih',
            'car_id.required' => 'Mobil harus dipilih',
            'start_date.required' => 'Mulai sewa tidak boleh kosong',
            'end_date.required' => 'Selesai sewa tidak boleh kosong',
            'total_price.required' => 'Total price tidak boleh kosong',
            'method.required' => 'Metode pembayaran tidak boleh kosong'
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
                    'status' => 'paid'
                ]);

                Payment::create([
                    'rental_id' => $rental->uuid,
                    'amount' => $rental->total_price,
                    'method' => $request->input('method'),
                    'status' => 'paid'
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
                    'total_price' => $request->input('total_price'),
                    'status' => 'paid'
                ]);

                $rowPaymentUpdated->update([
                    'amount' => $rowRentalUpdated->total_price,
                    'method' => $request->input('method'),
                    'status' => 'paid'
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }

        return to_route('admin.transaction.index');
    }



    public function destroy($uuid)
    {
        try {
            $rowRental = Rental::findOr($uuid);
            $rowRental->delete();
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }
}
