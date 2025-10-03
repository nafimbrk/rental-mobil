<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class AppController extends Controller
{
    public function getCar()
    {
        $cars = Car::select('id', 'brand', 'model', 'plate_number', 'color', 'year', 'status', 'price_per_day', 'image')
            ->get();
        return inertia('App/CarList', [
            'cars' => $cars
        ]);
    }

    public function dashboard()
    {
        $totalCar = Car::get()->count();
        $totalCustomer = Customer::get()->count();
        return inertia('Admin/Dashboard', [
            'totalCar' => $totalCar,
            'totalCustomer' => $totalCustomer
        ]);
    }
}
