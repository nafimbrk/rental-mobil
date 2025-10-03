<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $keySearch = $request->input('search');

        $customers = Customer::select('id', 'name', 'email', 'phone', 'age', 'gender')
            ->when($keySearch, function ($query, $keySearch) {
                $query->where(function ($q) use ($keySearch) {
                    $q->where('name', 'like', "%$keySearch%")
                        ->orWhere('email', 'like', "%$keySearch%")
                        ->orWhere('phone', 'like', "%$keySearch%")
                        ->orWhere('age', 'like', "%$keySearch%");

                    // 🔎 khusus gender
                    if (str_contains(strtolower($keySearch), 'laki')) {
                        $q->orWhere('gender', 'L');
                    } elseif (str_contains(strtolower($keySearch), 'perempuan')) {
                        $q->orWhere('gender', 'P');
                    }
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Customer/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $keySearch
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable',
            'phone' => 'required',
            'age' => 'required',
            'gender' => 'required'
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'phone.required' => 'Nomor Telepon tidak boleh kosong',
            'age.required' => 'Umur tidak boleh kosong',
            'gender.required' => 'Jenis Kelamin harus dipilih'
        ]);

        try {
            Customer::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'age' => $request->input('age'),
                'gender' => $request->input('gender')
            ]);
        } catch (\Throwable $e) {
            Log::info('Error ' . $e->getMessage());
        }

        return to_route('admin.customer.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable',
            'phone' => 'required',
            'age' => 'required',
            'gender' => 'required'
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'phone.required' => 'Nomor Telepon tidak boleh kosong',
            'age.required' => 'Umur tidak boleh kosong',
            'gender.required' => 'Jenis Kelamin harus dipilih'
        ]);

        $customer = Customer::findOr($id);

        try {
            $customer->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'age' => $request->input('age'),
                'gender' => $request->input('gender')
            ]);
        } catch (\Throwable $e) {
            Log::info('Error ' . $e->getMessage());
        }

        return to_route('admin.customer.index');
    }


    public function destroy($id)
    {
        $customer = Customer::findOr($id);

        $customer->delete();

        return to_route('admin.customer.index');
    }
}
