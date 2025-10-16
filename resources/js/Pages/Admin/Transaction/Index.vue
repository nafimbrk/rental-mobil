<script setup>
import { computed, reactive, ref, watch } from 'vue';
import LayoutAdmin from '../../../Components/Layout/LayoutAdmin.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import vSelect from "vue3-select";
import "vue3-select/dist/vue3-select.css";
import dayjs from "dayjs";

const props = defineProps({
    transactionList: Array,
    customerList: Array,
    carList: Array,
    filters: Object
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
    total_price: "",
    status: "",
    method: ""
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

const hitungTotalEdit = () => {
    if (formEdit.start_date && formEdit.end_date && formEdit.car_id) {
        const start = new Date(formEdit.start_date);
        const end = new Date(formEdit.end_date);

        const selectedCar = props.carList.find(
            (car) => car.id === parseInt(formEdit.car_id)
        );

        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 0 && selectedCar) {
            formEdit.total_price = diffDays * selectedCar.price_per_day;
        } else {
            formEdit.total_price = 0;
        }
    }
};



const createTransaction = () => {
    formCreate.post(route('admin.transaction.store'), {
        onSuccess: () => {
            formCreate.reset()
            modalCreate.value = !modalCreate.value
        }
    })
}

const formattedCarList = computed(() =>
    props.carList.map(car => ({
        ...car,
        display: `${car.model} - ${car.plate_number}`,
    }))
);


const paymentMethods = [
    { label: "Bank Transfer (Manual)", value: "bank_transfer" },
    { label: "Gopay", value: "gopay" },
    { label: "QRIS", value: "qris" },
    { label: "Alfamart / Indomaret", value: "alfa_or_indo" },
    { label: "OVO", value: "ovo" },
    { label: "Dana", value: "dana" },
    { label: "ShopeePay", value: "shopeepay" },
    { label: "Kartu Kredit / Debit", value: "credit_card" }
];


let formEdit = useForm({
    customer_id: "",
    car_id: "",
    start_date: "",
    end_date: "",
    total_price: "",
    status: "",
    method: ""
})

let modalEdit = ref(false)
let uuidUpdated = ref("")

const openModalEdit = (tl) => {
    console.log(tl.uuid);

    modalEdit.value = !modalEdit.value

    formEdit.customer_id = tl.customer_id
    formEdit.car_id = tl.car_id
    formEdit.start_date = tl.start_date
    formEdit.end_date = tl.end_date
    formEdit.total_price = tl.total_price
    formEdit.status = tl.status
    formEdit.method = tl.payment.method

    uuidUpdated.value = tl.uuid
}

const closeModalEdit = () => {
    modalEdit.value = !modalEdit.value
    formEdit.reset()
}


const editTransaction = () => {
    formEdit.put(route('admin.transaction.update', uuidUpdated.value), {
        onSuccess: () => {
            formEdit.reset(),
                modalEdit.value = !modalEdit.value
        }
    })
}


let modalDelete = ref(false)
let customerTrx = ref("")
let uuidTrx = ref("")
let carId = ref("false")

const openModalDelete = (tl) => {
    modalDelete.value = !modalDelete.value

    customerTrx.value = tl.customer.name
    uuidTrx.value = tl.uuid
    carId.value = tl.car_id
}

const formDelete = useForm({})

const closeModalDelete = () => {
    modalDelete.value = !modalDelete.value
    formDelete.reset()
}

const deleteTrx = () => {

    formDelete.delete(`/admin/transaction/${uuidTrx.value}?car_id=${carId.value}`, {
        onSuccess: closeModalDelete
    })
}


let search = ref(props.filters?.search ?? "");
let start_date = ref(props.filters?.start_date ?? "");
let end_date = ref(props.filters?.end_date ?? "");

// 🔎 search realtime + debounce langsung di watch
let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route("admin.transaction.index"),
            { search: value, start_date: start_date.value, end_date: end_date.value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300); // delay 300ms
});

// ✅ Auto filter tanggal — hanya jalan kalau dua-duanya terisi
watch([start_date, end_date], ([newStart, newEnd]) => {
    if (newStart && newEnd) {
        router.get(
            route("admin.transaction.index"),
            {
                search: search.value,
                start_date: newStart,
                end_date: newEnd,
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    } else if (!newStart && !newEnd) {
        // Reset filter kalau dua-duanya dikosongin
        router.get(route("admin.transaction.index"), {
            search: search.value,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }
});

const page = usePage()
const user = computed(() => page.props.auth.user)

const resetFilter = () => {
    search.value = ""
    start_date.value = ""
    end_date.value = ""
}


let modalReturned = ref(false)
let plateNumberReturned = ref("")
let uuidRental = ref("")

const openModalReturned = (tl) => {
    modalReturned.value = !modalReturned.value
    plateNumberReturned.value = tl.car.plate_number
    uuidRental.value = tl.uuid
}

const returnCar = () => {
    router.post(route('admin.transaction.return.car', uuidRental.value), {
        onSuccess: () => {
            modalReturned.value = false // pastikan ini langsung false
            // kasih jeda sedikit biar modal tertutup dulu
            setTimeout(() => {
                router.reload({ only: ['transactionList'] })
            }, 300)
        },
    })
}


let modalDetail = ref(false)

let transactionDetail = reactive({
    nameCustomer: "",
    nameCar: "",
    startSewa: "",
    endSewa: "",
    totalPrice: "",
    paymentStatus: "",
    paymentMethod: "",
    statusCar: "",
    timeReturned: ""
})

let openModalDetail = (tl) => {
    modalDetail.value = !modalDetail.value
    transactionDetail.nameCustomer = tl.customer.name
    transactionDetail.nameCar = tl.car.model
    transactionDetail.startSewa = tl.start_date
    transactionDetail.endSewa = tl.end_date
    transactionDetail.totalPrice = tl.total_price
    transactionDetail.paymentStatus = tl.payment.status
    transactionDetail.paymentMethod = tl.payment.method
    transactionDetail.statusCar = tl.status_car
    transactionDetail.timeReturned = tl.return_date
}

let modalLunas = ref(false)
const formLunas = useForm({
    method: ""
})
let uuidTr = ref("")
let totalPrice = ref("")

const openModalLunas = (tl) => {
    modalLunas.value = !modalLunas.value

    formLunas.method = tl.payment.method
    totalPrice.value = tl.total_price

    uuidTr.value = tl.uuid
}

const lunasPayment = () => {
    formLunas.post(route('admin.transaction.lunas.payment', uuidTr.value), {
        onSuccess: () => {
            modalLunas.value = false
            formLunas.reset()
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

            <div class="flex gap-2 items-center">
                <!-- Search -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-2 flex items-center text-gray-400">
                        <span class="material-icons text-sm">search</span>
                    </span>
                    <input type="text" v-model="search" placeholder="Cari customer..."
                        class="border rounded-lg pl-8 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Filter tanggal -->
                <input type="date" v-model="start_date"
                    class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <input type="date" v-model="end_date"
                    class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

                <button @click="resetFilter"
                    class="flex items-center gap-1 px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    <span class="material-icons text-base">restart_alt</span>
                    Reset
                </button>

                <!-- Tambah Transaksi -->
                <button v-if="user.role === 'admin'" @click="openModalCreate"
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
                        <th class="px-4 py-3 text-left">Status Pembayaran</th>
                        <!-- <th class="px-4 py-3 text-left">Metode Pembayaran</th> -->
                        <th class="px-4 py-3 text-left">Status Mobil</th>
                        <!-- <th class="px-4 py-3 text-left">Waktu Pengembalian</th> -->
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
                        <td class="px-4 py-3">{{ dayjs(tl.start_date).format('DD-MM-YYYY') }}</td>
                        <td class="px-4 py-3">{{ dayjs(tl.end_date).format('DD-MM-YYYY') }}</td>
                        <td class="px-4 py-3">{{ formatRupiah(tl.total_price) }}</td>
                        <!-- Status -->
                        <td class="px-4 py-3">
                            <span v-if="tl.status === 'paid'"
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                Dibayar
                            </span>
                            <button @click="openModalLunas(tl)" v-else
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                Tertunda
                            </button>
                        </td>
                        <!-- <td class="px-4 py-3">{{ tl.payment.method ?? 'Belum Dibayar' }}</td> -->
                        <td class="px-4 py-3">
                            <button @click="openModalReturned(tl)" v-if="tl.status_car === 'rented'"
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Disewa</button>
                            <button v-else
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Dikembalikan</button>
                        </td>
                        <!-- <td class="px-4 py-3">
                            <span v-if="tl.return_date === null">Belum Dikembalikan</span>
                            <span v-else>{{ dayjs(tl.return_date).format('DD/MM/YYYY HH:mm') }}</span>
                        </td> -->
                        <td v-if="user.role === 'admin'" class="px-4 py-3 text-center">
                            <div class="inline-flex space-x-2">
                                <button @click="openModalDetail(tl)"
                                    class="p-2 rounded-lg bg-green-500 text-white hover:bg-green-600 flex items-center justify-center transition"
                                    title="Lihat Detail Transaksi">
                                    <span class="material-icons text-sm">visibility</span>
                                </button>


                                <button @click="openModalEdit(tl)"
                                    class="p-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 flex items-center justify-center"
                                    title="Edit Mobil">
                                    <span class="material-icons text-sm">edit</span>
                                </button>
                                <button @click="openModalDelete(tl)"
                                    class="p-2 rounded-lg bg-red-500 text-white hover:bg-red-600 flex items-center justify-center"
                                    title="Hapus Mobil">
                                    <span class="material-icons text-sm">delete</span>
                                </button>

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









        <!-- Modal Detail Transaksi -->
        <div v-if="modalDetail"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            @click.self="modalDetail = !modalDetail">

            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full relative animate-fadeIn">
                <!-- Tombol close -->
                <button @click="modalDetail = !modalDetail"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                    ✖
                </button>

                <!-- Isi modal -->
                <div class="p-6">
                    <!-- Header -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        🚗 Detail Transaksi
                    </h2>

                    <!-- Detail transaksi -->
                    <div class="space-y-3 text-gray-600">
                        <p>
                            <span class="font-semibold">👤 Nama Customer:</span>
                            {{ transactionDetail.nameCustomer }}
                        </p>
                        <p>
                            <span class="font-semibold">🚘 Nama Mobil:</span>
                            {{ transactionDetail.nameCar }}
                        </p>
                        <p>
                            <span class="font-semibold">📅 Mulai Sewa:</span>
                            {{ dayjs(transactionDetail.startSewa).format('DD-MM-YYYY') }}
                        </p>
                        <p>
                            <span class="font-semibold">📅 Selesai Sewa:</span>
                            {{ dayjs(transactionDetail.endSewa).format('DD-MM-YYYY') }}
                        </p>
                        <p>
                            <span class="font-semibold">💰 Total Harga:</span>
                            <span class="text-green-600 font-semibold">{{ formatRupiah(transactionDetail.totalPrice)
                                }}</span>
                        </p>
                        <p>
                            <span class="font-semibold">💳 Status Pembayaran:</span>
                            <span v-if="transactionDetail.paymentStatus === 'paid'"
                                class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-600 font-medium">
                                Dibayar
                            </span>
                            <span v-else
                                class="px-2 py-1 text-sm rounded-full bg-yellow-100 text-yellow-600 font-medium">
                                Tertunda
                            </span>
                        </p>
                        <p>
                            <span class="font-semibold">🏦 Metode Pembayaran:</span>
                            <span v-if="transactionDetail.paymentMethod">
                                {{ transactionDetail.paymentMethod }}
                            </span>
                            <span v-else class="italic text-gray-400">Belum dibayar</span>
                        </p>
                        <p>
                            <span class="font-semibold">🚗 Status Mobil:</span>
                            <span v-if="transactionDetail.statusCar === 'rented'"
                                class="px-2 py-1 text-sm rounded-full bg-blue-100 text-blue-600 font-medium">
                                Disewa
                            </span>
                            <span v-else class="px-2 py-1 text-sm rounded-full bg-gray-100 text-gray-600 font-medium">
                                Dikembalikan
                            </span>
                        </p>
                        <p>
                            <span class="font-semibold">⏰ Waktu Pengembalian:</span>
                            <span v-if="transactionDetail.timeReturned">
                                {{ dayjs(transactionDetail.timeReturned).format('DD-MM-YYYY | HH:mm') }}
                            </span>
                            <span v-else class="italic text-gray-400">Belum dikembalikan</span>
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                    <button @click="modalDetail = !modalDetail"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition">
                        Tutup
                    </button>
                </div>
            </div>
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

                <form @submit.prevent="createTransaction">


                    <!-- Grid formStore -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Customer -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <span v-if="formCreate.errors.customer_id" class="text-red-700 italic">{{
                                formCreate.errors.customer_id }}</span>
                            <v-select v-model="formCreate.customer_id" :options="customerList" label="name"
                                :reduce="customer => customer.id" placeholder="pilih customer"
                                class="w-full border border-gray-300 rounded-lg text-sm" />
                        </div>

                        <!-- Mobil -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobil</label>
                            <span v-if="formCreate.errors.customer_id" class="text-red-700 italic">{{
                                formCreate.errors.customer_id }}</span>
                            <v-select v-model="formCreate.car_id" :options="formattedCarList" label="display"
                                :reduce="car => car.id" placeholder="pilih mobil" @option:selected="hitungTotal"
                                class="w-full border border-gray-300 rounded-lg text-sm" />

                        </div>

                        <!-- Mulai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mulai</label>
                            <span v-if="formCreate.errors.start_date" class="text-red-700 italic">{{
                                formCreate.errors.start_date }}</span>
                            <input type="date" v-model="formCreate.start_date" @change="hitungTotal" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 transition" />
                        </div>

                        <!-- Selesai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selesai</label>
                            <span v-if="formCreate.errors.end_date" class="text-red-700 italic">{{
                                formCreate.errors.end_date }}</span>
                            <input type="date" v-model="formCreate.end_date" @change="hitungTotal" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 transition" />
                        </div>


                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Pembayaran</label>
                            <span v-if="formCreate.errors.status" class="text-red-700 italic">{{
                                formCreate.errors.status }}</span>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="radio" v-model="formCreate.status" value="paid" />
                                    <span>Dibayar</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" v-model="formCreate.status" value="pending" />
                                    <span>Tertunda</span>
                                </label>
                            </div>
                        </div>


                        <!-- Total Harga -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga</label>
                            <span v-if="formCreate.errors.total_price" class="text-red-700 italic">{{
                                formCreate.errors.total_price }}</span>
                            <input type="text" :value="formatRupiah(formCreate.total_price)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm 
                        text-gray-700 focus:ring focus:ring-blue-200 focus:border-blue-500 transition" readonly />
                        </div>


                        <!-- Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Metode Pembayaran
                            </label>
                            <span v-if="formCreate.errors.method" class="text-red-700 italic">
                                {{ formCreate.errors.method }}
                            </span>

                            <v-select v-model="formCreate.method" :options="paymentMethods" label="label"
                                :reduce="method => method.value" placeholder="pilih metode pembayaran"
                                class="w-full border border-gray-300 rounded-lg text-sm" />
                        </div>

                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit"
                            class="px-4 py-2 rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            Pesan
                        </button>
                    </div>

                </form>


            </div>
        </div>











        <!-- modal edit -->
        <div v-if="modalEdit"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="closeModalEdit">
            <div class="bg-white/95 backdrop-blur-md rounded-xl shadow-2xl w-full max-w-2xl p-6 relative">
                <!-- Tombol Close -->
                <button @click="closeModalEdit"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    Pesan Mobil
                </h2>

                <form @submit.prevent="editTransaction">


                    <!-- Grid formStore -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Customer -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <span v-if="formEdit.errors.customer_id" class="text-red-700 italic">{{
                                formEdit.errors.customer_id }}</span>
                            <v-select v-model="formEdit.customer_id" :options="customerList" label="name"
                                :reduce="customer => customer.id" placeholder="pilih customer"
                                class="w-full border border-gray-300 rounded-lg text-sm" />
                        </div>

                        <!-- Mobil -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobil</label>
                            <span v-if="formEdit.errors.car_id" class="text-red-700 italic">{{
                                formEdit.errors.car_id }}</span>
                            <v-select v-model="formEdit.car_id" :options="formattedCarList" label="display"
                                :reduce="car => car.id" placeholder="pilih mobil" @option:selected="hitungTotalEdit"
                                class="w-full border border-gray-300 rounded-lg text-sm" />

                        </div>

                        <!-- Mulai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mulai</label>
                            <span v-if="formEdit.errors.start_date" class="text-red-700 italic">{{
                                formEdit.errors.start_date }}</span>
                            <input type="date" v-model="formEdit.start_date" @change="hitungTotalEdit" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 transition" />
                        </div>

                        <!-- Selesai -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selesai</label>
                            <span v-if="formEdit.errors.end_date" class="text-red-700 italic">{{
                                formEdit.errors.end_date }}</span>
                            <input type="date" v-model="formEdit.end_date" @change="hitungTotalEdit" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm 
                       focus:ring focus:ring-blue-200 focus:border-blue-500 transition" />
                        </div>




                        <!-- Total Harga -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga</label>
                            <span v-if="formEdit.errors.total_price" class="text-red-700 italic">{{
                                formEdit.errors.total_price }}</span>
                            <input type="text" :value="formatRupiah(formEdit.total_price)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm 
                        text-gray-700 focus:ring focus:ring-blue-200 focus:border-blue-500 transition" readonly />
                        </div>

                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit"
                            class="px-4 py-2 rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            Pesan
                        </button>
                    </div>

                </form>


            </div>
        </div>





        <!-- modal delete -->
        <div v-if="modalDelete"
            class="fixed inset-0 flex items-start justify-center pt-20 z-50 bg-black/30 backdrop-blur-sm">
            <!-- Modal Box -->
            <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg w-full max-w-md p-6 relative">
                <!-- Tombol X -->
                <button @click="closeModalDelete" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <!-- Judul -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    Konfirmasi Hapus
                </h2>

                <!-- Isi -->
                <p class="text-gray-600 mb-6">
                    Yakin ingin menghapus transaksi customer
                    <span class="font-bold text-red-500">{{
                        customerTrx
                    }}</span>
                    ?
                </p>

                <!-- Tombol aksi -->
                <div class="flex justify-end space-x-3">
                    <button @click="closeModalDelete"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button @click="deleteTrx" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            </div>
        </div>




        <!-- modal returned -->
        <div v-if="modalReturned"
            class="fixed inset-0 flex items-start justify-center pt-20 z-50 bg-black/30 backdrop-blur-sm">
            <!-- Modal Box -->
            <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg w-full max-w-md p-6 relative">
                <!-- Tombol X -->
                <button @click="modalReturned = !modalReturned"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <!-- Judul -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    Konfirmasi Pengembalian
                </h2>

                <!-- Isi -->
                <p class="text-gray-600 mb-6">
                    Yakin ingin mengembalikan mobil ber plat nomor:
                    <span class="font-bold text-green-500">{{
                        plateNumberReturned
                    }}</span>
                    sekarang ?
                </p>

                <!-- Tombol aksi -->
                <div class="flex justify-end space-x-3">
                    <button @click="modalReturned = !modalReturned"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button @click="returnCar" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Kembalikan
                    </button>
                </div>
            </div>
        </div>


        <!-- modal lunas -->
        <div v-if="modalLunas"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="modalLunas = !modalLunas">
            <div class="bg-white/95 backdrop-blur-md rounded-xl shadow-2xl w-full max-w-2xl p-6 relative">
                <!-- Tombol Close -->
                <button @click="modalLunas = !modalLunas"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    Lunasi Pembayaran
                </h2>

                <form @submit.prevent="lunasPayment">


                    <!-- Grid formStore -->
                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga</label>
                            <input type="text" :value="formatRupiah(totalPrice)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm 
                        text-gray-700 focus:ring focus:ring-blue-200 focus:border-blue-500 transition" readonly />
                        </div>


                        <!-- Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Metode Pembayaran
                            </label>
                            <span v-if="formLunas.errors.method" class="text-red-700 italic">
                                {{ formLunas.errors.method }}
                            </span>

                            <v-select v-model="formLunas.method" :options="paymentMethods" label="label"
                                :reduce="method => method.value" placeholder="pilih metode pembayaran"
                                class="w-full border border-gray-300 rounded-lg text-sm" />
                        </div>

                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit"
                            class="px-4 py-2 rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            Pesan
                        </button>
                    </div>

                </form>


            </div>
        </div>
    </LayoutAdmin>

</template>