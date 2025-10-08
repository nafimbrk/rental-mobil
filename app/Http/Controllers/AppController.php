<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Rental;

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
        return inertia('Admin/Dashboard', [
            'totalCar' => $totalCar,
            'totalCustomer' => $totalCustomer,
            'totalTransaction' => $totalTransaction
        ]);
    }
}
