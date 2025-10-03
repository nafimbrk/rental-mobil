<?php

namespace App\Listeners;

use App\Events\PembayaranSewaSelesai;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KirimNotifikasiWhatsApp
{
    public function __construct()
    {
        //
    }

    public function handle(PembayaranSewaSelesai $event)
    {
        $rental = $event->rental;
        $customer = $event->customer;
        $payment = $event->payment;
        
        // Format nomor HP
        $nomor = $this->formatNomorHP($customer->phone);
        
        // Buat pesan
        $pesan = $this->buatPesan($rental, $customer, $payment);
        
        // Kirim ke WhatsApp
        $this->kirimWA($nomor, $pesan);
    }
    
    private function formatNomorHP($nomor)
    {
        // Hilangkan karakter non-numeric
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        
        // Jika diawali 0, ganti dengan 62
        if (substr($nomor, 0, 1) == '0') {
            $nomor = '62' . substr($nomor, 1);
        }
        
        // Jika belum diawali 62, tambahkan
        if (substr($nomor, 0, 2) != '62') {
            $nomor = '62' . $nomor;
        }
        
        return $nomor;
    }
    
    private function buatPesan($rental, $customer, $payment)
    {
        $car = $rental->car; // Asumsi ada relasi di model
        
        $startDate = Carbon::parse($rental->start_date)->format('d M Y');
        $endDate = Carbon::parse($rental->end_date)->format('d M Y');
        
        $pesan = "*KONFIRMASI PEMBAYARAN RENTAL MOBIL* ✅\n\n";
        $pesan .= "Halo *{$customer->name}*,\n\n";
        $pesan .= "Pembayaran rental mobil Anda telah berhasil!\n\n";
        $pesan .= "📋 *Detail Pesanan:*\n";
        $pesan .= "🚗 Mobil: {$car->brand} {$car->model}\n";
        $pesan .= "📅 Mulai: {$startDate}\n";
        $pesan .= "📅 Selesai: {$endDate}\n";
        $pesan .= "💰 Total: Rp " . number_format($rental->total_price, 0, ',', '.') . "\n";
        $pesan .= "💳 Metode: " . strtoupper($payment->method) . "\n";
        $pesan .= "✅ Status: *LUNAS*\n\n";
        $pesan .= "📌 *Order ID:* {$rental->uuid}\n\n";
        $pesan .= "Terima kasih telah mempercayai layanan kami! 🙏\n";
        $pesan .= "Silakan tunjukkan pesan ini saat pengambilan mobil.\n\n";
        $pesan .= "_Pesan otomatis, tidak perlu dibalas._";
        
        return $pesan;
    }
    
    private function kirimWA($nomor, $pesan)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.api_key')
            ])->post(config('services.fonnte.url'), [
                'target' => $nomor,
                'message' => $pesan,
                'countryCode' => '62',
            ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp berhasil dikirim ke: ' . $nomor);
            } else {
                Log::error('Gagal kirim WhatsApp: ' . $response->body());
            }
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Error kirim WhatsApp: ' . $e->getMessage());
        }
    }
}