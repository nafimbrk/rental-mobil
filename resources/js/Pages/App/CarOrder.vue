<script setup>
import dayjs from "dayjs";
import { route } from "ziggy-js";
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

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
            router.reload({ only: ['carOrder'] })
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


const modalCancel = ref(false)
let orderIdDeleted = ref(null)

const openModalCancel = (carOrderId) => {
    modalCancel.value = !modalCancel.value
    orderIdDeleted.value = carOrderId
}

const formCancel = useForm({})

const cancel = () => {
    formCancel.delete(route('car.order.cancel', orderIdDeleted.value))
}

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
                <img :src="carOrder.car?.image ? `/storage/car/${carOrder.car.image}` : '/storage/car/default-car.jpg'"
                    alt="Car" class="w-full h-64 object-cover rounded-xl mb-4 shadow" />

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
                        <li><span class="font-medium">Mulai:</span> {{ dayjs(carOrder.start_date).format('DD/MM/YYYY')
                        }}</li>
                        <li><span class="font-medium">Selesai:</span> {{ dayjs(carOrder.end_date).format('DD/MM/YYYY')
                        }}</li>
                    </ul>
                </div>

                <!-- Detail Pembayaran -->
                <div>
                    <h3 class="text-blue-600 font-bold flex items-center gap-1">
                        💳 Pembayaran
                    </h3>
                    <ul class="text-gray-600 text-sm mt-2 space-y-1">
                        <li><span class="font-medium">Status:</span>
                            <span class="px-2 py-1 text-xs rounded-lg"
                                :class="carOrder.payment?.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                                {{ carOrder.payment?.status }}
                            </span>
                        </li>
                        <li>
                            <span class="font-medium">Metode:</span>
                            {{ carOrder.payment?.method || 'Belum dibayar' }}
                        </li>
                    </ul>
                </div>

                <!-- Harga Total -->
                <div
                    class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4 shadow-inner">
                    <p class="text-gray-700 font-medium text-lg">💰 Total Harga:</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ formatRupiah(carOrder.total_price) }}
                    </p>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-end gap-3">
                    <button v-if="carOrder.payment.status === 'pending'" @click="openModalCancel(carOrder.uuid)"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg transition">
                        Batalkan Pesanan
                    </button>
                    <button v-if="carOrder.payment.status === 'pending'" @click="bayarSekarang"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Bayar Sekarang
                    </button>
                    <Link v-else :href="route('car.index')" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Daftar Mobil
                    </Link>


                </div>
            </div>
        </div>
    </div>



<!-- modal cancel -->
    <div
            v-if="modalCancel"
            class="fixed inset-0 flex items-start justify-center pt-20 z-50 bg-black/30 backdrop-blur-sm"
        >
            <!-- Modal Box -->
            <div
                class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg w-full max-w-md p-6 relative"
            >
                <!-- Tombol X -->
                <button
                    @click="modalCancel = false"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
                >
                    ✕
                </button>

                <!-- Judul -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    Konfirmasi Hapus
                </h2>

                <!-- Isi -->
                <p class="text-gray-600 mb-6">
                    Yakin ingin menghapus order:
                    <span class="font-bold text-red-500">{{
                        orderIdDeleted
                    }}</span>
                    ?
                </p>

                <!-- Tombol aksi -->
                <div class="flex justify-end space-x-3">
                    <button
                        @click="modalCancel = false"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                    >
                        Batal
                    </button>
                    <button
                        @click="cancel"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    >
                        Hapus
                    </button>
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
