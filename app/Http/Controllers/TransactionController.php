<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        $transactionList = Rental::with('customer', 'car')->orderByDesc('created_at')->paginate(10);
        $customerList = Customer::select('id', 'name')->get();
        $carList = Car::select('id', 'model', 'price_per_day')->get();

        return inertia('Admin/Transaction/Index', [
            'transactionList' => $transactionList,
            'customerList' => $customerList,
            'carList' => $carList
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'car_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'total_price' => 'required'
        ], [
            'customer_id.required' => 'Customer harus dipilih',
            'car_id.required' => 'Mobil harus dipilih',
            'start_date.required' => 'Mulai sewa tidak boleh kosong',
            'end_date.required' => 'Selesai sewa tidak boleh kosong',
            'total_price.required' => 'Total price tidak boleh kosong'
        ]);

        Rental::create([
            'uuid' => Str::uuid(),
            'customer_id' => $request->input('customer_id'),
            'car_id' => $request->input('car_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'total_price' => $request->input('total_price'),
            'status' => 'pending'
        ]);

        return to_route('admin.transaction.index');
    }
}
