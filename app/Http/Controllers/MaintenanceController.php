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
            ->select('id', 'car_id', 'description', 'maintenance_date', 'cost', 'status', 'end_date')
            ->when($keySearch, function ($query, $keySearch) {
                $query->where(function ($q) use ($keySearch) {
                    $q->where('description', 'like', "%$keySearch%")
                        ->orWhere('maintenance_date', 'like', "%$keySearch%")
                        ->orWhere('cost', 'like', "%$keySearch%");
                    $q->orWhereHas('car', function ($qry) use ($keySearch) {
                        $qry->where('model', 'like', "%$keySearch%")
                            ->orWhere('plate_number', 'like', "%$keySearch%");
                    });
                    if (str_contains(strtolower($keySearch), 'progress')) {
                        $q->orWhere('status', 'in_progress');
                    } elseif (str_contains(strtolower($keySearch), 'selesai')) {
                        $q->orWhere('status', 'completed');
                    }
                    $q->where('end_date', 'like', "%$keySearch%");
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
            DB::transaction(function () use ($request) {
                $maintenance = Maintenance::create([
                    'car_id' => $request->input('car_id'),
                    'description' => $request->input('description'),
                    'maintenance_date' => $request->input('maintenance_date'),
                    'cost' => $request->input('cost'),
                    'status' => 'in_progress'
                ]);

                Car::where('id', $maintenance->car_id)->update([
                    'status' => 'maintenance'
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        // dd($request->all(), $id);
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
            DB::transaction(function () use ($id, $request) {
                $maintenance = Maintenance::find($id);
                $oldCarId = $maintenance->car_id;

                $maintenance->update([
                    'car_id' => $request->input('car_id'),
                    'description' => $request->input('description'),
                    'maintenance_date' => $request->input('maintenance_date'),
                    'cost' => $request->input('cost'),
                ]);

                if ($oldCarId != $request->input('car_id')) {
                    Car::where('id', $oldCarId)->update(['status' => 'available']);

                    Car::where('id', $request->input('car_id'))->update(['status' => 'maintenance']);
                }
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }





    public function endMaintenance($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $maintenance = Maintenance::find($id);
                $maintenance->update([
                    'status' => 'completed',
                    'end_date' => now()
                ]);
                Car::where('id', $maintenance->car_id)->update([
                    'status' => 'available'
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            DB::transaction(function () use($id) {
                $maintenance = Maintenance::find($id);
                $maintenance->delete();

                Car::where('id', $maintenance->car_id)->update([
                    'status' => 'available'
                ]);
            });
        } catch (\Throwable $th) {
            $th->getMessage();
        }
    }
}
