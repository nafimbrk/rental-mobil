<script setup>
import dayjs from "dayjs";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";

const { carOrder } = defineProps({
    carOrder: Object,
});

const formatRupiah = (number) => {
    if (!number) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
};

const bayarSekarang = () => {
    const token = carOrder.payment.snap_token;
    console.log("Token:", token);


    if (!window.snap) {
        alert("Midtrans belum siap");
        return;
    }

    window.snap.pay(token, {
        onSuccess: (result) => {
            console.log("✅ Success", result);
            router.visit(route('car.index'))
        },
        onPending: (result) => {
            console.log("⏳ Pending", result);
        },
        onError: (result) => {
            console.log("❌ Error", result);
        },
        onClose: () => {
            console.log("❌ Popup ditutup user");
        },
    });
};

</script>

<template>
    <div class="max-w-5xl mx-auto bg-white shadow-xl rounded-2xl mt-10 p-6">
        <!-- Header -->
        <div class="border-b pb-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                ✅ Detail Pesanan
            </h2>
            <p class="text-gray-500 text-sm">ID Order: {{ carOrder.uuid }}</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Kolom Mobil -->
            <div>
                <img
                    :src="carOrder.car?.image ? `/storage/car/${carOrder.car.image}` : '/storage/car/default-car.jpg'"
                    alt="Car"
                    class="w-full h-64 object-cover rounded-xl mb-4 shadow"
                />

                <h3 class="text-lg font-semibold text-gray-800">
                    {{ carOrder.car?.brand }} {{ carOrder.car?.model }}
                </h3>
                <p class="text-gray-500 text-sm mb-2">
                    Nomor Plat: {{ carOrder.car?.plate_number }}
                </p>

                <ul class="text-gray-600 text-sm space-y-1">
                    <li><span class="font-medium">Warna:</span> {{ carOrder.car?.color }}</li>
                    <li><span class="font-medium">Tahun:</span> {{ carOrder.car?.year }}</li>
                    <li>
                        <span class="font-medium">Harga / Hari:</span>
                        <span class="text-green-600 font-semibold">
                            {{ formatRupiah(carOrder.car?.price_per_day) }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Kolom Detail Pemesan & Pembayaran -->
            <div class="space-y-6">
                <!-- Detail Pemesan -->
                <div>
                    <h3 class="text-blue-600 font-bold flex items-center gap-1">
                        👤 Detail Pemesan
                    </h3>
                    <ul class="text-gray-600 text-sm mt-2 space-y-1">
                        <li><span class="font-medium">Nama:</span> {{ carOrder.customer?.name }}</li>
                        <li><span class="font-medium">Email:</span> {{ carOrder.customer?.email || '-' }}</li>
                        <li><span class="font-medium">Nomor Hp:</span> {{ carOrder.customer?.phone }}</li>
                        <li><span class="font-medium">Umur:</span> {{ carOrder.customer?.age }}</li>
                        <li>
                            <span class="font-medium">Jenis Kelamin:</span>
                            {{ carOrder.customer?.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </li>
                        <li><span class="font-medium">Mulai:</span> {{ dayjs(carOrder.start_date).format('DD/MM/YYYY') }}</li>
                        <li><span class="font-medium">Selesai:</span> {{ dayjs(carOrder.end_date).format('DD/MM/YYYY') }}</li>
                    </ul>
                </div>

                <!-- Detail Pembayaran -->
                <div>
                    <h3 class="text-blue-600 font-bold flex items-center gap-1">
                        💳 Pembayaran
                    </h3>
                    <ul class="text-gray-600 text-sm mt-2 space-y-1">
                        <li><span class="font-medium">Status:</span>
                            <span
                                class="px-2 py-1 text-xs rounded-lg"
                                :class="carOrder.payment?.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                            >
                                {{ carOrder.payment?.status }}
                            </span>
                        </li>
                        <li>
                            <span class="font-medium">Metode:</span>
                            {{ carOrder.payment?.method || 'Belum dipilih' }}
                        </li>
                        <li>
                            <span class="font-medium">Snap Token:</span>
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ carOrder.payment?.snap_token }}</code>
                        </li>
                    </ul>
                </div>

                <!-- Harga Total -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4 shadow-inner">
                    <p class="text-gray-700 font-medium text-lg">💰 Total Harga:</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ formatRupiah(carOrder.total_price) }}
                    </p>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-end gap-3">
                    <button
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg transition"
                    >
                        Kembali
                    </button>
                    <button @click="bayarSekarang" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
  Bayar Sekarang
</button>



                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
