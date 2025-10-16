<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $keySearch = $request->input('search');

        $maintenances = Maintenance::with('car:id,model,plate_number')
            ->select('id', 'car_id', 'description', 'maintenance_date', 'cost')
            ->when($keySearch, function ($query, $keySearch) {
                $query->where(function ($q) use ($keySearch) {
                    $q->where('description', 'like', "%$keySearch%")
                        ->orWhere('maintenance_date', 'like', "%$keySearch%")
                        ->orWhere('cost', 'like', "%$keySearch%");
                    $q->orWhereHas('car', function ($qry) use ($keySearch) {
                        $qry->where('model', 'like', "%$keySearch%")
                            ->orWhere('plate_number', 'like', "%$keySearch%");
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $carList = Car::select('id', 'model', 'price_per_day', 'plate_number')->where('status', 'available')->get();

        return inertia('Admin/Maintenance/Index', [
            'maintenances' => $maintenances,
            'carList' => $carList,
            'filters' => [
                'search' => $keySearch
            ]
        ]);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'car_id' => 'required',
            'description' => 'nullable',
            'maintenance_date' => 'required',
            'cost' => 'required'
        ], [
            'car_id.required' => 'Pilih satu mobil',
            'maintenance_date.required' => 'Pilih tanggal perbaikan',
            'cost.required' => 'Biaya tidak boleh kosong'
        ]);

        try {
            DB::transaction(function () use($request) {
                $maintenance = Maintenance::create([
                    'car_id' => $request->input('car_id'),
                    'description' => $request->input('description'),
                    'maintenance_date' => $request->input('maintenance_date'),
                    'cost' => $request->input('cost')
                ]);

                Car::where('id', $maintenance->car_id)->update([
                    'status' => 'maintenance'
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }

    }
}
