<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Payment;
use App\Models\Rental;
use App\Events\PembayaranSewaSelesai; // 👈 Import Event
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        Log::info('Midtrans Callback:', $request->all());

        try {
            $orderId = $request->input('order_id');
            $status = $request->input('transaction_status');
            $paymentType = $request->input('payment_type');
            $grossAmount = $request->input('gross_amount');

            // ✅ Cari by uuid
            $rental = Rental::where('uuid', $orderId)->first();

            if (!$rental) {
                Log::warning("Rental not found with uuid: $orderId");
                return response()->json(['error' => 'Rental not found'], 404);
            }

            $paymentStatus = match ($status) {
                'settlement', 'capture' => 'paid',
                'pending' => 'pending',
                'deny', 'expire', 'cancel' => 'failed',
                default => 'pending',
            };

            DB::transaction(function () use ($rental, $grossAmount, $paymentStatus, $paymentType) {
                $payment = Payment::updateOrCreate(
                    ['rental_id' => $rental->uuid],
                    [
                        'amount' => (int) $grossAmount,
                        'method' => $paymentType,
                        'status' => $paymentStatus,
                    ]
                );

                if ($paymentStatus === 'paid') {
                    $rental->update(['status' => 'paid']);
                    
                    // 🚀 TRIGGER EVENT KIRIM WHATSAPP
                    $customer = $rental->customer; // Pastikan ada relasi di model
                    event(new PembayaranSewaSelesai($rental, $customer, $payment));
                }
            });

            Log::info("Payment processed successfully for rental uuid: $orderId");
            return response()->json(['success' => true], 200);
            
        } catch (\Throwable $e) {
            Log::error('Callback error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}