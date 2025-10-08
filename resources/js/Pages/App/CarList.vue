<script setup>
import { computed, reactive, ref } from "vue";
import Layout from "../../Components/Layout/Layout.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import dayjs from "dayjs";

defineProps({
    cars: Array,
});

let detailCar = reactive({
    brand: "",
    model: "",
    plate_number: "",
    color: "",
    year: "",
    status: "",
    price_per_day: "",
    image: "",
});

let detailModal = ref(false);

const openDetailModal = (car) => {
    detailModal.value = true;

    detailCar.brand = car.brand;
    detailCar.model = car.model;
    detailCar.plate_number = car.plate_number;
    detailCar.color = car.color;
    detailCar.year = car.year;
    detailCar.status = car.status;
    detailCar.price_per_day = car.price_per_day;
    detailCar.image = car.image;
};

const closeDetailModal = () => {
    detailModal.value = false;
};

let modalPesan = ref(false);

const formPesan = useForm({
    name: "",
    email: "",
    phone: "",
    age: "",
    gender: "",
    car_id: "",
    start_date: "",
    end_date: "",
    total_price: "",
});

const carData = reactive({
    brand: "",
    model: "",
    plate_number: "",
    color: "",
    year: "",
    price_per_day: "",
    image: null,
});

const openModalPesan = (car) => {
    modalPesan.value = !modalPesan.value;

    formPesan.car_id = car.id;

    carData.brand = car.brand;
    carData.model = car.model;
    carData.plate_number = car.plate_number;
    carData.color = car.color;
    carData.year = car.year;
    carData.image = car.image;

    carData.price_per_day = car.price_per_day;
};

const hitungTotal = () => {
    if (formPesan.start_date && formPesan.end_date) {
        const start = new Date(formPesan.start_date);
        const end = new Date(formPesan.end_date);

        // hitung selisih dalam hari
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 0) {
            formPesan.total_price = diffDays * carData.price_per_day;
        } else {
            formPesan.total_price = 0;
        }
    }
};

const isFormValid = computed(() => {
    // field wajib
    if (
        !formPesan.name ||
        !formPesan.phone ||
        !formPesan.age ||
        !formPesan.gender ||
        !formPesan.start_date ||
        !formPesan.end_date ||
        !formPesan.total_price
    ) {
        return false;
    }

    // validasi email hanya kalau ada isinya
    if (formPesan.email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formPesan.email)) {
            return false;
        }
    }

    return true;
});

let modalAfterPesan = ref(false);

const openModalAfterPesan = () => {
    modalAfterPesan.value = !modalAfterPesan.value;
    modalPesan.value = false;
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0, // hilangin nol dibelakang
        maximumFractionDigits: 0,
    }).format(angka);
};

const pesanMobil = () => {
    formPesan.post(route('car.order'))
}
</script>

<template>
    <Layout>
        <!-- Card mobil -->
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6 mt-20"
        >
            <div
                v-for="car in cars"
                :key="car.id"
                class="group max-w-sm rounded-2xl overflow-hidden shadow-md bg-white hover:shadow-xl transition-all duration-300 border border-gray-100"
            >
                <!-- Gambar mobil -->
                <div class="relative">
                    <img
                        :src="
                            car.image
                                ? `/storage/car/${car.image}`
                                : '/storage/car/default-car.jpg'
                        "
                        alt="Car"
                        class="w-full h-48 object-cover rounded-t-2xl group-hover:scale-105 transition-transform duration-300"
                    />

                    <!-- Badge status di pojok kanan atas -->
                    <span
                        v-if="car.status == 'available'"
                        class="absolute top-3 right-3 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 shadow"
                    >
                        ✔ Tersedia
                    </span>
                    <span
                        v-else-if="car.status == 'rented'"
                        class="absolute top-3 right-3 px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600 shadow"
                    >
                        🚫 Disewa
                    </span>
                    <span
                        v-else
                        class="absolute top-3 right-3 px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700 shadow"
                    >
                        🔧 Perbaikan
                    </span>
                </div>

                <!-- Info mobil -->
                <div class="p-4">
                    <h2
                        class="text-lg font-bold text-gray-800 group-hover:text-blue-600 transition"
                    >
                        {{ car.brand }} {{ car.model }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        Tahun: {{ car.year }} | Warna: {{ car.color }}
                    </p>

                    <p class="text-xl font-semibold text-green-600 mt-2">
                        {{ formatRupiah(car.price_per_day) }} / hari
                    </p>

                    <!-- Tombol -->
                    <div class="mt-4 flex gap-2">
                        <button
                            @click="openModalPesan(car)"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                            :disabled="car.status !== 'available'"
                        >
                            Pesan
                        </button>

                        <button
                            @click="openDetailModal(car)"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2 px-4 rounded-lg transition shadow-sm"
                        >
                            Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail -->
        <div
            v-if="detailModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl max-w-lg w-full relative animate-fadeIn"
            >
                <!-- Tombol close -->
                <button
                    @click="closeDetailModal"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-700"
                >
                    ✖
                </button>

                <!-- Isi modal -->
                <div class="p-6">
                    <img
                        :src="
                            detailCar.image
                                ? `/storage/car/${detailCar.image}`
                                : '/storage/car/default-car.jpg'
                        "
                        alt="Car Detail"
                        class="w-full h-56 object-cover rounded-lg mb-4"
                    />

                    <!-- Header judul + status -->
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ detailCar.brand }} {{ detailCar.model }}
                        </h2>
                        <span
                            v-if="detailCar.status == 'available'"
                            class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-600 font-semibold"
                        >
                            ✔ Tersedia
                        </span>
                        <span
                            v-else-if="detailCar.status == 'rented'"
                            class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-600 font-semibold"
                        >
                            ⛔ Disewa
                        </span>
                        <span
                            v-else
                            class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-600 font-semibold"
                        >
                            🛠 Perbaikan
                        </span>
                    </div>

                    <!-- Detail info -->
                    <div class="space-y-2 text-gray-600 mb-4">
                        <p>
                            <span class="font-semibold">🚘 Plat Nomor:</span>
                            {{ detailCar.plate_number }}
                        </p>
                        <p>
                            <span class="font-semibold">🎨 Warna:</span>
                            {{ detailCar.color }}
                        </p>
                        <p>
                            <span class="font-semibold">📅 Tahun:</span>
                            {{ detailCar.year }}
                        </p>
                    </div>

                    <!-- Harga -->
                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                        <p class="text-lg font-bold text-blue-700">
                            {{ formatRupiah(detailCar.price_per_day) }}/hari
                        </p>
                    </div>
                </div>

                <!-- Footer modal -->
                <div
                    class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3"
                >
                    <button
                        @click="closeDetailModal"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition"
                    >
                        Tutup
                    </button>
                    <button
                        @click="openModalPesan(detailCar)"
                        :disabled="detailCar.status !== 'available'"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg transition"
                    >
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>

        <!-- modal pesan -->

        <div
            v-if="modalPesan"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="modalPesan = !modalPesan"
        >
            <div
                class="bg-white/95 backdrop-blur-md rounded-xl shadow-2xl w-full max-w-2xl p-6 relative"
            >
                <!-- Tombol Close -->
                <button
                    @click="modalPesan = !modalPesan"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
                >
                    ✕
                </button>

                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    Pesan Mobil
                </h2>

                <!-- Grid formStore -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Nama</label
                        >
                        <input
                            type="text"
                            v-model="formPesan.name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Email (opsional)</label
                        >
                        <input
                            type="email"
                            v-model="formPesan.email"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Nomor Hp</label
                        >
                        <input
                            type="tel"
                            v-model="formPesan.phone"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Umur</label
                        >
                        <input
                            type="number"
                            v-model="formPesan.age"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                        />
                    </div>

                    <!-- Status -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Jenis Kelamin</label
                        >
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="formPesan.gender"
                                    value="L"
                                />
                                <span>Laki-laki</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="formPesan.gender"
                                    value="P"
                                />
                                <span>Perempuan</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Mulai</label
                        >
                        <input
                            type="date"
                            v-model="formPesan.start_date"
                            @change="hitungTotal"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Selesai</label
                        >
                        <input
                            type="date"
                            v-model="formPesan.end_date"
                            @change="hitungTotal"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Total Harga
                        </label>
                        <input
                            type="text"
                            :value="formatRupiah(formPesan.total_price)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                            readonly
                        />
                    </div>
                </div>

                <!-- Tombol -->
                <div class="flex justify-end gap-3 pt-4">
                    <button
                        type="button"
                        @click="openModalAfterPesan"
                        :disabled="!isFormValid"
                        class="px-4 py-2 rounded-lg text-white transition-colors"
                        :class="
                            isFormValid
                                ? 'bg-blue-600 hover:bg-blue-700'
                                : 'bg-gray-400 cursor-not-allowed'
                        "
                    >
                        Pesan
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal After Pesan -->
        <div
            v-if="modalAfterPesan"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-2 sm:p-4"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full relative animate-fadeIn max-h-[90vh] overflow-y-auto"
            >
                <!-- Tombol close -->
                <button
                    @click="modalAfterPesan = !modalAfterPesan"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-lg"
                >
                    ✖
                </button>

                <form @submit.prevent="pesanMobil">
                    <!-- Header -->
                    <div class="px-6 pt-6 border-b pb-3">
                        <h2 class="text-2xl font-bold text-gray-800">
                            ✅ Konfirmasi Pesanan
                        </h2>
                        <p class="text-sm text-gray-500">
                            Pastikan data berikut sudah benar
                        </p>
                    </div>

                    <!-- Isi modal -->
                    <div class="grid md:grid-cols-2 gap-6 p-6">
                        <!-- Foto & info mobil -->
                        <div>
                            <img
                                :src="
                                    carData.image
                                        ? `/storage/car/${carData.image}`
                                        : '/storage/car/default-car.jpg'
                                "
                                alt="Car Detail"
                                class="w-full h-64 object-cover rounded-lg mb-4"
                            />
                            <h2
                                class="text-lg font-semibold text-gray-800 mt-4"
                            >
                                {{ carData.brand }} {{ carData.model }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                Nomor Plat: {{ carData.plate_number }}
                            </p>
                        </div>

                        <!-- Detail -->
                        <div class="space-y-5">
                            <!-- Detail Mobil -->
                            <div>
                                <h3
                                    class="text-blue-600 font-bold flex items-center gap-1"
                                >
                                    🚗 Detail Mobil
                                </h3>
                                <ul
                                    class="text-gray-600 text-sm mt-2 space-y-1"
                                >
                                    <li>
                                        <span class="font-medium">Warna:</span>
                                        {{ carData.color }}
                                    </li>
                                    <li>
                                        <span class="font-medium">Tahun:</span>
                                        {{ carData.year }}
                                    </li>
                                    <li>
                                        <span class="font-medium"
                                            >Harga / Hari:</span
                                        >
                                        <span
                                            class="text-green-600 font-semibold"
                                        >
                                            {{
                                                formatRupiah(
                                                    carData.price_per_day
                                                )
                                            }}
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Detail Pemesan -->
                            <div>
                                <h3
                                    class="text-blue-600 font-bold flex items-center gap-1"
                                >
                                    👤 Detail Pemesan
                                </h3>
                                <ul
                                    class="text-gray-600 text-sm mt-2 space-y-1"
                                >
                                    <li>
                                        <span class="font-medium">Nama:</span>
                                        {{ formPesan.name }}
                                    </li>
                                    <li>
                                        <span class="font-medium">Email:</span>
                                        {{ formPesan.email || "-" }}
                                    </li>
                                    <li>
                                        <span class="font-medium"
                                            >Nomor Hp:</span
                                        >
                                        {{ formPesan.phone }}
                                    </li>
                                    <li>
                                        <span class="font-medium">Umur:</span>
                                        {{ formPesan.age }}
                                    </li>
                                    <li>
                                        <span class="font-medium"
                                            >Jenis Kelamin:</span
                                        >
                                        {{
                                            formPesan.gender === "L"
                                                ? "Laki-laki"
                                                : "Perempuan"
                                        }}
                                    </li>
                                    <li>
                                        <span class="font-medium">Mulai:</span>
                                        {{
                                            dayjs(formPesan.start_date).format(
                                                "DD/MM/YYYY"
                                            )
                                        }}
                                    </li>
                                    <li>
                                        <span class="font-medium"
                                            >Selesai:</span
                                        >
                                        {{
                                            dayjs(formPesan.end_date).format(
                                                "DD/MM/YYYY"
                                            )
                                        }}
                                    </li>
                                </ul>
                            </div>

                            <!-- Harga Total -->
                            <div
                                class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4 shadow-inner"
                            >
                                <p class="text-gray-700 font-medium text-lg">
                                    💰 Total Harga:
                                </p>
                                <p class="text-2xl font-bold text-blue-700">
                                    {{ formatRupiah(formPesan.total_price) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3"
                    >
                        <button
                            @click="modalAfterPesan = !modalAfterPesan"
                            type="button"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg transition"
                        >
                            Tutup
                        </button>

                        <button
                            type="submit"
                            class="px-5 py-2 rounded-lg text-white font-medium transition-colors bg-blue-600 hover:bg-blue-700"
                        >
                            <span>Konfirmasi Pesanan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>

<style>
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
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}
</style>
