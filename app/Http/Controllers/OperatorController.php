<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function index(Request $request)
    {
        $keySearch = $request->input('search');

        return $operators = Operator::with('user:id,name,email,role')
            ->select('id', 'user_id', 'age', 'gender', 'phone')->get();



            // ->when($keySearch, function ($query, $keySearch) {
            //     $query->where(function ($q) use ($keySearch) {
            //         $q->where('name', 'like', "%$keySearch%")
            //             ->orWhere('email', 'like', "%$keySearch%")
            //             ->orWhere('phone', 'like', "%$keySearch%")
            //             ->orWhere('age', 'like', "%$keySearch%");

            //         // 🔎 khusus gender
            //         if (str_contains(strtolower($keySearch), 'laki')) {
            //             $q->orWhere('gender', 'L');
            //         } elseif (str_contains(strtolower($keySearch), 'perempuan')) {
            //             $q->orWhere('gender', 'P');
            //         }
            //     });
            // })
            // ->orderBy('id', 'desc')
            // ->paginate(10)
            // ->withQueryString();

        return inertia('Admin/Customer/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $keySearch
            ]
        ]);
    }
}
