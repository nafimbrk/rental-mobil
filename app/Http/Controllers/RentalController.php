<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Str;

class RentalController extends Controller
{
    public function order(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable',
            'phone' => 'required',
            'age' => 'required',
            'gender' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'phone.required' => 'Nomor Hp tidak boleh kosong',
            'age.required' => 'Umur tidak boleh kosong',
            'gender.required' => 'Jenis kelamin harus dipilih',
            'start_date.required' => 'Mulai sewa tidak boleh kosong',
            'end_date.required' => 'Selesai sewa tidak boleh kosong'
        ]);


        try {
            DB::beginTransaction();

            $newCustomer = Customer::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'age' => $request->input('age'),
                'gender' => $request->input('gender')
            ]);

            $lastIdCustomer = $newCustomer->id;

            $rental = Rental::create([
                'uuid' => Str::uuid(),
                'customer_id' => $lastIdCustomer,
                'car_id' => $request->input('car_id'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'total_price' => $request->input('total_price'),
                'status' => 'pending',
                'status_car' => 'rented'
            ]);

            // Midtrans Config
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $rental->uuid,
                    'gross_amount' => (int) $rental->total_price,
                ],
                'customer_details' => [
                    'first_name' => $newCustomer->name,
                    'email' => $newCustomer->email,
                    'phone' => $newCustomer->phone,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            Payment::create([
                'rental_id' => $rental->uuid,
                'amount' => $rental->total_price,
                'status' => 'pending',
                'snap_token' => $snapToken
            ]);

            $carUpdated = Car::findOr($rental->car_id);
            $carUpdated->update([
                'status' => 'rented'
            ]);

            DB::commit();

            // return response()->json([
            //     'success' => true,
            //     'snap_token' => $snapToken
            // ]);

            return to_route('car.order.show', ['uuid' => $rental->uuid]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th->getMessage());
        }
    }

    public function showCarOrder($uuid)
    {
        $carOrder = Rental::with('customer', 'payment', 'car')->where('uuid', $uuid)->first();
        return inertia('App/CarOrder', [
            'carOrder' => $carOrder
        ]);
    }

    public function orderCancel($uuid)
    {
        try {
            DB::transaction(function () use ($uuid) {
                $orderDeleted = Rental::where('uuid', $uuid)->first();
                $orderDeleted->delete();

                $customerDeleted = Customer::where('id', $orderDeleted->customer_id);
                $customerDeleted->delete();

                $carUpdated = Car::where('id', $orderDeleted->car_id);
                $carUpdated->update([
                    'status' => 'available'
                ]);
            });

            return to_route('car.index');
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return;
        }
    }
}
