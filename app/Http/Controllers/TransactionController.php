<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactionList = Rental::with('customer', 'car')->orderByDesc('created_at')->paginate(10);

        return inertia('Admin/Transaction/Index', [
            'transactionList' => $transactionList
        ]);
    }
}
