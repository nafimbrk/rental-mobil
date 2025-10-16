<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Operator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OperatorController extends Controller
{
    public function index(Request $request)
    {
        $keySearch = $request->input('search');

        $operators = Operator::with('user:id,name,email,role')
            ->select('id', 'user_id', 'age', 'gender', 'phone')
            ->when($keySearch, function ($query, $keySearch) {
                $query->where(function ($q) use ($keySearch) {
                    $q->where('age', 'like', "%$keySearch%")
                        ->orWhere('phone', 'like', "%$keySearch%");

                    // 🔎 Khusus gender
                    if (str_contains(strtolower($keySearch), 'laki')) {
                        $q->orWhere('gender', 'L');
                    } elseif (str_contains(strtolower($keySearch), 'perempuan')) {
                        $q->orWhere('gender', 'P');
                    }

                    // 🔍 Relasi user
                    $q->orWhereHas('user', function ($qry) use ($keySearch) {
                        $qry->where('name', 'like', "%$keySearch%")
                            ->orWhere('email', 'like', "%$keySearch%");
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Admin/Operator/Index', [
            'operators' => $operators,
            'filters' => [
                'search' => $keySearch
            ]
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'age' => 'required',
            'gender' => 'required',
            'phone' => 'required'
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'password.required' => 'Password tidak boleh kosong',
            'age.required' => 'Umur tidak boleh kosong',
            'gender.required' => 'Pilih satu jenis kelamin',
            'phone.required' => 'No telp tidak boleh kosong'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'role' => 'operator'
                ]);

                Operator::create([
                    'user_id' => $user->id,
                    'age' => $request->input('age'),
                    'gender' => $request->input('gender'),
                    'phone' => $request->input('phone')
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // dd($id, $request->input('operator_id'));
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'age' => 'required',
            'gender' => 'required',
            'phone' => 'required'
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'age.required' => 'Umur tidak boleh kosong',
            'gender.required' => 'Pilih satu jenis kelamin',
            'phone.required' => 'No telp tidak boleh kosong'
        ]);

        $operatorUserId = $request->input('operator_id');

        try {
            $userUpdated = User::findOr($id);
            $operatorUpdated = Operator::where('user_id', $operatorUserId);
            DB::transaction(function () use ($request, $userUpdated, $operatorUpdated) {
                $userUpdated->update([
                    'name' => $request->input('name'),
                    'email' => $request->input('email')
                ]);

                $operatorUpdated->update([
                    'age' => $request->input('age'),
                    'gender' => $request->input('gender'),
                    'phone' => $request->input('phone')
                ]);
            });
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
        }
    }


    public function destroy($id)
    {
        User::findOr($id)->delete();
    }


    public function changePassword(Request $request, $id)
    {
        // dd($request->all(), $id);
        $request->validate([
            'password' => 'required'
        ], [
            'password.required' => 'Password harus diisi'
        ]);

        User::find($id)->update([
            'password' => Hash::make($request->input('password'))
        ]);
    }
}
