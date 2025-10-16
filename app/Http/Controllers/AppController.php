<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Operator;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    public function getCar()
    {
        $cars = Car::select('id', 'brand', 'model', 'plate_number', 'color', 'year', 'status', 'price_per_day', 'image')
            ->orderBy('id', 'desc')
            ->get();
        return inertia('App/CarList', [
            'cars' => $cars
        ]);
    }

    public function dashboard()
    {
        $totalCar = Car::get()->count();
        $totalCustomer = Customer::get()->count();
        $totalTransaction = Rental::get()->count();
        $totalOperator = Operator::get()->count();

        $recentTransactions = Rental::with(['customer', 'car'])
            ->latest()
            ->take(5)
            ->get();
        
        return inertia('Admin/Dashboard', [
            'totalCar' => $totalCar,
            'totalCustomer' => $totalCustomer,
            'totalTransaction' => $totalTransaction,
            'totalOperator' => $totalOperator,
            'recentTransactions' => $recentTransactions
        ]);
    }
}
