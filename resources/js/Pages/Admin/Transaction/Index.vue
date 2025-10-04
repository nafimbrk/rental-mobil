<script setup>
import { reactive, ref } from 'vue';
import LayoutAdmin from '../../../Components/Layout/LayoutAdmin.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';


const props = defineProps({
    transactionList: Object,
    customerList: Object,
    carList: Object
})


const customer = reactive({
    name: "",
    age: "",
    gender: "",
    email: "",
    phone: ""
})


let modalCustomer = ref(false)

const openModalCustomer = (dataCustomer) => {
    modalCustomer.value = !modalCustomer.value

    customer.name = dataCustomer.name
    customer.age = dataCustomer.age
    customer.gender = dataCustomer.gender
    customer.email = dataCustomer.email
    customer.phone = dataCustomer.phone
}



const car = reactive({
    brand: "",
    model: "",
    plate_number: "",
    color: "",
    year: "",
    status: "",
    price_per_day: "",
    image: ""
})


let modalCar = ref(false)

const openModalCar = (dataCar) => {
    modalCar.value = !modalCar.value

    car.brand = dataCar.brand
    car.model = dataCar.model
    car.plate_number = dataCar.plate_number
    car.color = dataCar.color
    car.year = dataCar.year
    car.status = dataCar.status
    car.price_per_day = dataCar.price_per_day
    car.image = dataCar.image
}


const formatRupiah = (angka) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0, // hilangin nol dibelakang
        maximumFractionDigits: 0,
    }).format(angka);
};
   
let modalCreate = ref(false)

const openModalCreate = () => {
    modalCreate.value = !modalCreate.value
}

let formCreate = useForm({
    customer_id: "",
    car_id: "",
    start_date: "",
    end_date: "",
    total_price: ""
})

const hitungTotal = () => {
    if (formCreate.start_date && formCreate.end_date && formCreate.car_id) {
        const start = new Date(formCreate.start_date);
        const end = new Date(formCreate.end_date);

        // cari mobil yang sesuai dari car_id
        const selectedCar = props.carList.find(
            (car) => car.id === parseInt(formCreate.car_id)
        );

        // hitung selisih dalam hari
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 0 && selectedCar) {
            formCreate.total_price = diffDays * selectedCar.price_per_day;
        } else {
            formCreate.total_price = 0;
        }

    }
};


const createTransaction = () => {
    console.log(JSON.stringify(formCreate));
    formCreate.post(route('admin.transaction.store'), {
        onSuccess: () => {
            formCreate.reset()
            modalCreate.value = !modalCreate.value
        }
    })
}


</script>




<template>

    <LayoutAdmin>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <span class="material-icons text-blue-600">people</span>
                List Customer
            </h2>

            <div class="flex gap-2">
                <!-- Search -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-2 flex items-center text-gray-400">
                        <span class="material-icons text-sm">search</span>
                    </span>
                    <input type="text" v-model="search" placeholder="Cari customer..."
                        class="border rounded-lg pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Tambah Mobil -->
                <button @click="openModalCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-md transition">
                    <span class="material-icons text-sm">add</span>
                    Buat Transaksi
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full text-sm text-gray-600">
                <thead class="bg-gray-100 text-gray-700 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Customer</th>
                        <th class="px-4 py-3 text-left">Nama Mobil</th>
                        <th class="px-4 py-3 text-left">Mulai Sewa</th>
                        <th class="px-4 py-3 text-left">Selesai Sewa</th>
                        <th class="px-4 py-3 text-left">Total Harga</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="tl in transactionList.data" :key="tl.id" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800"><button
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700"
                                @click="openModalCustomer(tl.customer)">{{ tl.customer.name }}</button></td>
                        <td class="px-4 py-3"><button @click="openModalCar(tl.car)"
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{
                                    tl.car.model }}</button></td>
                        <td class="px-4 py-3">{{ tl.start_date }}</td>
                        <td class="px-4 py-3">{{ tl.end_date }}</td>
                        <td class="px-4 py-3">{{ tl.total_price }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex space-x-2">
                                <!-- <button
              @click="openDeleteModal(tl)"
              class="p-2 rounded-lg bg-red-500 text-white hover:bg-red-600 flex items-center justify-center"
              title="Hapus Mobil"
            >
              <span class="material-icons text-sm">delete</span>
            </button> -->
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-end items-center space-x-1">
            <!-- Prev -->
            <button @click="
                transactionList.prev_page_url &&
                router.get(
                    transactionList.prev_page_url,
                    {},
                    { preserveState: true, preserveScroll: true }
                )
                " class="px-3 py-1.5 rounded-lg border text-sm transition-all duration-200" :class="transactionList.prev_page_url
                    ? 'bg-white text-gray-600 border-gray-300 hover:bg-blue-50 hover:text-blue-600'
                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    " :disabled="!transactionList.prev_page_url">
                «
            </button>

            <!-- Numbered Pages -->
            <button v-for="link in transactionList.links.filter((l) => !isNaN(l.label))" :key="link.url ?? link.label"
                @click="
                    router.get(
                        link.url,
                        {},
                        { preserveState: true, preserveScroll: true }
                    )
                    " v-html="link.label"
                class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-all duration-200" :class="[
                    link.active
                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                        : 'bg-white text-gray-600 border-gray-300 hover:bg-blue-50 hover:text-blue-600',
                ]" />

            <!-- Next -->
            <button @click="
                transactionList.next_page_url &&
                router.get(
                    transactionList.next_page_url,
                    {},
                    { preserveState: true, preserveScroll: true }
                )
                " class="px-3 py-1.5 rounded-lg border text-sm transition-all duration-200" :class="transactionList.next_page_url
                    ? 'bg-white text-gray-600 border-gray-300 hover:bg-blue-50 hover:text-blue-600'
                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    " :disabled="!transactionList.next_page_url">
                »
            </button>
        </div>








        <!-- Modal Customer -->
        <div v-if="modalCustomer"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            @click.self="modalCustomer = !modalCustomer">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full relative animate-fadeIn">
                <!-- Tombol close -->
                <button @click="modalCustomer = !modalCustomer"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                    ✖
                </button>

                <!-- Isi modal -->
                <div class="p-6">
                    <!-- Header nama -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        👤 {{ customer.name }}
                    </h2>

                    <!-- Detail customer -->
                    <div class="space-y-3 text-gray-600">
                        <p>
                            <span class="font-semibold">📧 Email:</span>
                            {{ customer.email }}
                        </p>
                        <p>
                            <span class="font-semibold">📱 Telepon:</span>
                            {{ customer.phone }}
                        </p>
                        <p>
                            <span class="font-semibold">🎂 Usia:</span>
                            {{ customer.age }} tahun
                        </p>
                        <p>
                            <span class="font-semibold">⚧ Gender:</span>
                            <span v-if="customer.gender === 'male'"
                                class="px-2 py-1 text-sm rounded-full bg-blue-100 text-blue-600 font-medium">
                                Laki-laki
                            </span>
                            <span v-else class="px-2 py-1 text-sm rounded-full bg-pink-100 text-pink-600 font-medium">
                                Perempuan
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button @click="modalCustomer = !modalCustomer"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>




        <!-- Modal Car -->
        <div v-if="modalCar"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            @click.self="modalCar = !modalCar">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full relative animate-fadeIn">
                <!-- Tombol close -->
                <button @click="modalCar = !modalCar" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                    ✖
                </button>

                <!-- Isi modal -->
                <div class="p-6">
                    <!-- Gambar mobil -->
                    <img :src="car.image ? `/storage/car/${car.image}` : '/storage/car/default-car.jpg'" alt="Car Image"
                        class="w-full h-56 object-cover rounded-lg mb-4" />

                    <!-- Header nama mobil -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-800">
                            🚘 {{ car.brand }} {{ car.model }}
                        </h2>

                        <!-- Badge status -->
                        <span v-if="car.status === 'available'"
                            class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-600 font-semibold">
                            ✔ Tersedia
                        </span>
                        <span v-else-if="car.status === 'rented'"
                            class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-600 font-semibold">
                            ⛔ Disewa
                        </span>
                        <span v-else class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-600 font-semibold">
                            🛠 Perbaikan
                        </span>
                    </div>

                    <!-- Detail info -->
                    <div class="space-y-2 text-gray-600 mb-4">
                        <p>
                            <span class="font-semibold">🔖 Plat Nomor:</span>
                            {{ car.plate_number }}
                        </p>
                        <p>
                            <span class="font-semibold">🎨 Warna:</span>
                            {{ car.color }}
                        </p>
                        <p>
                            <span class="font-semibold">📅 Tahun:</span>
                            {{ car.year }}
                        </p>
                    </div>

                    <!-- Harga -->
                    <div class="bg-blue-50 p-3 rounded-lg text-center">
                        <p class="text-lg font-bold text-blue-700">
                            💰 {{ formatRupiah(car.price_per_day) }}/hari
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button @click="modalCar = !modalCar"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>




        <!-- modal create -->
        <div v-if="modalCreate"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="modalCreate = !modalCreate">
            <div class="bg-white/95 backdrop-blur-md rounded-xl shadow-2xl w-full max-w-2xl p-6 relative">
                <!-- Tombol Close -->
                <button @click="modalCreate = !modalCreate"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    Pesan Mobil
                </h2>

                <!-- Grid formStore -->
                <form @submit.prevent="createTransaction">
                    <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <select v-model="formCreate.customer_id">
                            <template v-for="customer in customerList">
                                <option :value="customer.id">{{ customer.name }}</option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (opsional)</label>
                        <select v-model="formCreate.car_id" @change="hitungTotal">
                            <option v-for="car in carList" :key="car.id" :value="car.id">
                                {{ car.model }}
                            </option>
                        </select>

                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mulai</label>
                        <input type="date" v-model="formCreate.start_date" @change="hitungTotal"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selesai</label>
                        <input type="date" v-model="formCreate.end_date" @change="hitungTotal"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Total Harga
                        </label>
                        <input type="text" :value="formatRupiah(formCreate.total_price)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200"
                            readonly />
                    </div>
                </div>

                 <!-- Tombol -->
                <div class="flex justify-end gap-3 pt-4">
                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-white transition-colors bg-blue-600 hover:bg-blue-700">
                        Pesan
                    </button>
                </div>
                </form>

               
            </div>
        </div>
    </LayoutAdmin>

</template>