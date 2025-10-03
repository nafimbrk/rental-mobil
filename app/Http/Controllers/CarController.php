<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CarController extends Controller
{
   public function index(Request $request)
{
    $keySearch = $request->input('search');
    $status = $request->input('status');

    $cars = Car::select('id', 'brand', 'model', 'plate_number', 'color', 'year', 'status', 'price_per_day', 'image')
        ->when($keySearch, function ($query, $keySearch) {
            $query->where(function ($q) use ($keySearch) {
                $q->where('brand', 'like', "%$keySearch%")
                  ->orWhere('model', 'like', "%$keySearch%")
                  ->orWhere('plate_number', 'like', "%$keySearch%")
                  ->orWhere('color', 'like', "%$keySearch%")
                  ->orWhere('year', 'like', "%$keySearch%")
                  ->orWhere('status', 'like', "%$keySearch%");
            });
        })
        ->when($status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->orderBy('id', 'desc')
        ->paginate(10)
        ->withQueryString();

    return inertia('Admin/Car/Index', [
        'cars' => $cars,
        'filters' => [
            'search' => $keySearch,
            'status' => $status,
        ],
    ]);
}



    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'brand' => 'required',
            'model' => 'required',
            'plate_number' => 'required',
            'color' => 'required',
            'year' => 'required',
            'status' => 'required',
            'price_per_day' => 'required'
        ], [
            'image.image' => 'Harus berupa gambar',
            'image.mimes' => 'Gambar harus berekstensi: jpg/png/jpeg/gif/svg',
            'image.max' => 'Gambar maksimal berukuran 2048Kb',
            'brand.required' => 'Merek tidak boleh kosong',
            'model.required' => 'Model tidak boleh kosong',
            'plate_number.required' => 'Nomor Plate tidak boleh kosong',
            'color.required' => 'Warna tidak boleh kosong',
            'year.required' => 'Tahun tidak boleh kosong',
            'status.required' => 'Status harus dipilih',
            'price_per_day.required' => 'Harga/Hari tidak boleh kosong'
        ]);

        if ($request->file('image') != null) {
            $image = $request->file('image');
            $nameResult = $image->hashName();

            $image->storeAs('car', $nameResult);
        }

        try {
            Car::create([
                'image' => $nameResult ?? null,
                'brand' => $request->input('brand'),
                'model' => $request->input('model'),
                'plate_number' => $request->input('plate_number'),
                'color' => $request->input('color'),
                'year' => $request->input('year'),
                'status' => $request->input('status'),
                'price_per_day' => $request->input('price_per_day')
            ]);
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
        }

        return to_route('admin.car.index');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'brand' => 'required',
            'model' => 'required',
            'plate_number' => 'required',
            'color' => 'required',
            'year' => 'required',
            'status' => 'required',
            'price_per_day' => 'required'
        ], [
            'image.image' => 'Harus berupa gambar',
            'image.mimes' => 'Gambar harus berekstensi: jpg/png/jpeg/gif/svg',
            'image.max' => 'Gambar maksimal berukuran 2048Kb',
            'brand.required' => 'Merek tidak boleh kosong',
            'model.required' => 'Model tidak boleh kosong',
            'plate_number.required' => 'Nomor Plate tidak boleh kosong',
            'color.required' => 'Warna tidak boleh kosong',
            'year.required' => 'Tahun tidak boleh kosong',
            'status.required' => 'Status harus dipilih',
            'price_per_day.required' => 'Harga/Hari tidak boleh kosong'
        ]);

        $car = Car::findOr($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $nameResult = $image->hashName();

            $image->storeAs('car', $nameResult);

            Storage::delete('car/' . $car->image);

            try {
                $car->update([
                    'image' => $nameResult ?? null,
                    'brand' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'plate_number' => $request->input('plate_number'),
                    'color' => $request->input('color'),
                    'year' => $request->input('year'),
                    'status' => $request->input('status'),
                    'price_per_day' => $request->input('price_per_day')
                ]);
            } catch (\Throwable $e) {
                Log::info($e->getMessage());
            }
        } else {
            try {
                $car->update([
                    'brand' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'plate_number' => $request->input('plate_number'),
                    'color' => $request->input('color'),
                    'year' => $request->input('year'),
                    'status' => $request->input('status'),
                    'price_per_day' => $request->input('price_per_day')
                ]);
            } catch (\Throwable $e) {
                Log::info($e->getMessage());
            }
        }

        return to_route('admin.car.index');
    }


    public function destroy($id)
    {
        $car = Car::findOr($id);

        $car->delete();

        Storage::delete('car/' . $car->image);

        return to_route('admin.car.index');
    }
}
