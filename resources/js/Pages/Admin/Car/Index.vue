<script setup>
import { computed, ref, watch } from "vue";
import LayoutAdmin from "../../../Components/Layout/LayoutAdmin.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    cars: Object,
    filters: Object
});

let modalCreate = ref(false);
let modalUpdate = ref(false);

const openModalCreate = () => {
    modalCreate.value = !modalCreate.value;
};

const formStore = useForm({
    image: null,
    brand: "",
    model: "",
    plate_number: "",
    color: "",
    year: "",
    price_per_day: ""
});

const imagePreview = ref(null);

function handleImageUpload(e) {
    const file = e.target.files[0];
    if (file) {
        formStore.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

const storeCar = () => {
    formStore.post(route("admin.car.store"), {
        onSuccess: () => {
            formStore.reset();
            modalCreate.value = false; // lebih aman daripada toggle (!), biar pasti ketutup
        },
    });
};

const imagePreviewU = ref(null);

function handleImageUploadU(e) {
    const file = e.target.files[0];
    if (file) {
        formUpdate.image = file; // File object
        imagePreviewU.value = URL.createObjectURL(file);
    }
}

const formUpdate = useForm({
    image: null,
    old_image: null, // tambahkan ini
    brand: "",
    model: "",
    plate_number: "",
    color: "",
    year: "",
    price_per_day: "",
});

let carId = ref(null);
let selectedCar = ref(null);

const openModalUpdate = (car) => {
    selectedCar.value = car;
    modalUpdate.value = true;
    imagePreviewU.value = null;

    formUpdate.brand = car.brand;
    formUpdate.model = car.model;
    formUpdate.plate_number = car.plate_number;
    formUpdate.color = car.color;
    formUpdate.year = car.year;
    formUpdate.price_per_day = car.price_per_day;

    // simpan nama lama di old_image, bukan di image
    formUpdate.old_image = car.image;

    carId.value = car.id;
};

const updateCar = () => {
    formUpdate.post(route("admin.car.update", carId.value), {
        _method: "put", // Laravel method spoofing
        forceFormData: true,
        onSuccess: () => {
            modalUpdate.value = false;
            formUpdate.reset()
        },
    });
};

const formDelete = useForm({});

let deleteModal = ref(false);
let carIdDelete = ref("");
let carPlateNumberDelete = ref("");

const openDeleteModal = (car) => {
    deleteModal.value = !deleteModal.value;
    carIdDelete.value = car.id;
    carPlateNumberDelete.value = car.plate_number;
};

const deleteCar = () => {
    formDelete.delete(route("admin.car.destroy", carIdDelete.value), {
        onSuccess: (deleteModal.value = !deleteModal.value),
    });
};

let search = ref(props.filters?.search ?? "");
let statusFilter = ref(props.filters?.status ?? "");

// 🔎 search realtime + debounce langsung di watch
let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route("admin.car.index"),
            { search: value, status: statusFilter.value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300); // delay 300ms
});

// ⛔ filter status (langsung tanpa debounce)
watch(statusFilter, (value) => {
    router.get(route("admin.car.index"),
        { search: search.value, status: value },
        { preserveState: true, preserveScroll: true, replace: true }
    );
});



const formatRupiah = (angka) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0, // hilangin nol dibelakang
        maximumFractionDigits: 0,
    }).format(angka);
};


const page = usePage()
const user = computed(() => page.props.auth.user)

const resetFilter = () => {
    search.value = ""
    statusFilter.value = ""
}
</script>

<template>
    <LayoutAdmin>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                🚗 List Mobil
            </h2>

            <div class="flex items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400 material-icons text-sm">search</span>
                    <input type="text" v-model="search" placeholder="Cari mobil..."
                        class="pl-10 border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Dropdown Filter Status -->
                <select v-model="statusFilter"
                    class="border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="available">Tersedia</option>
                    <option value="rented">Disewa</option>
                    <option value="maintenance">Perawatan</option>
                </select>

                <button @click="resetFilter"
                    class="flex items-center gap-1 px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    <span class="material-icons text-base">restart_alt</span>
                    Reset
                </button>


                <!-- Tambah Mobil -->
                <button v-if="user.role === 'admin'" @click="openModalCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-md transition">
                    <span class="material-icons text-sm">add</span>
                    Tambah Mobil
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-lg shadow-md border border-gray-200">
            <table class="min-w-full bg-white text-sm">
                <thead class="bg-blue-50 text-gray-700 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Gambar</th>
                        <th class="px-4 py-3 text-left">Merek</th>
                        <th class="px-4 py-3 text-left">Model</th>
                        <th class="px-4 py-3 text-left">Nomor Plat</th>
                        <th class="px-4 py-3 text-left">Warna</th>
                        <th class="px-4 py-3 text-left">Tahun</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-center">Harga / Hari</th>
                        <th v-if="user.role === 'admin'" class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="car in cars.data" :key="car.id" class="hover:bg-gray-50 transition">
                        <!-- Gambar -->
                        <td class="px-4 py-3">
                            <img :src="car.image ? `/storage/car/${car.image}` : '/storage/car/default-car.jpg'"
                                alt="Car" class="w-16 h-12 object-cover rounded-lg border" />
                        </td>

                        <!-- Merek -->
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ car.brand }}</td>
                        <td class="px-4 py-3">{{ car.model }}</td>
                        <td class="px-4 py-3">{{ car.plate_number }}</td>
                        <td class="px-4 py-3">{{ car.color }}</td>
                        <td class="px-4 py-3">{{ car.year }}</td>

                        <!-- Status -->
                        <td class="px-4 py-3">
                            <span v-if="car.status == 'available'"
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                Tersedia
                            </span>
                            <span v-else-if="car.status == 'rented'"
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                Disewa
                            </span>
                            <span v-else
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                Perawatan
                            </span>
                        </td>

                        <!-- Harga -->
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            {{ formatRupiah(car.price_per_day) }}
                        </td>

                        <!-- Actions -->
                        <td v-if="user.role === 'admin'" class="px-4 py-3 text-center">
                            <div class="inline-flex space-x-2">
                                <button @click="openModalUpdate(car)"
                                    class="p-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 flex items-center justify-center"
                                    title="Edit Mobil">
                                    <span class="material-icons text-sm">edit</span>
                                </button>
                                <button @click="openDeleteModal(car)"
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
                cars.prev_page_url &&
                router.get(
                    cars.prev_page_url,
                    {},
                    { preserveState: true, preserveScroll: true }
                )
                " class="px-3 py-1.5 rounded-lg border text-sm transition-all duration-200" :class="cars.prev_page_url
                    ? 'bg-white text-gray-600 border-gray-300 hover:bg-blue-50 hover:text-blue-600'
                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    " :disabled="!cars.prev_page_url">
                «
            </button>

            <!-- Page Numbers -->
            <button v-for="link in cars.links.filter((l) => !isNaN(l.label))" :key="link.url ?? link.label" @click="
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
                cars.next_page_url &&
                router.get(
                    cars.next_page_url,
                    {},
                    { preserveState: true, preserveScroll: true }
                )
                " class="px-3 py-1.5 rounded-lg border text-sm transition-all duration-200" :class="cars.next_page_url
                    ? 'bg-white text-gray-600 border-gray-300 hover:bg-blue-50 hover:text-blue-600'
                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    " :disabled="!cars.next_page_url">
                »
            </button>
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
                    Tambah Mobil Baru
                </h2>

                <form @submit.prevent="storeCar" class="space-y-4">
                    <!-- Upload gambar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                        <span v-if="formStore.errors.image">{{
                            formStore.errors.image
                        }}</span>
                        <input type="file" accept="image/*" @change="handleImageUpload"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />

                        <!-- Progress upload -->
                        <progress v-if="formStore.progress" :value="formStore.progress.percentage" max="100"
                            class="w-full mt-2">
                            {{ formStore.progress.percentage }}%
                        </progress>

                        <!-- Preview -->
                        <div v-if="imagePreview" class="mt-3">
                            <img :src="imagePreview" alt="Preview" class="w-32 h-24 object-cover rounded border" />
                        </div>
                    </div>

                    <!-- Grid formStore -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
                            <span v-if="formStore.errors.brand" class="text-red-700 italic">{{ formStore.errors.brand
                            }}</span>
                            <input type="text" v-model="formStore.brand"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                            <span v-if="formStore.errors.model" class="text-red-700 italic">{{ formStore.errors.model
                            }}</span>
                            <input type="text" v-model="formStore.model"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat</label>
                            <span v-if="formStore.errors.plate_number" class="text-red-700 italic">{{
                                formStore.errors.plate_number }}</span>
                            <input type="text" v-model="formStore.plate_number"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Warna</label>
                            <span v-if="formStore.errors.color" class="text-red-700 italic">{{ formStore.errors.color
                            }}</span>
                            <input type="text" v-model="formStore.color"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <span v-if="formStore.errors.year" class="text-red-700 italic">{{ formStore.errors.year
                            }}</span>
                            <input type="number" v-model="formStore.year"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga / Hari</label>
                            <span v-if="formStore.errors.price_per_day" class="text-red-700 italic">{{
                                formStore.errors.price_per_day }}</span>
                            <input type="number" v-model="formStore.price_per_day"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- modal update -->
        <div v-if="modalUpdate"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="modalUpdate = !modalUpdate">
            <div class="bg-white/95 backdrop-blur-md rounded-xl shadow-2xl w-full max-w-2xl p-6 relative">
                <!-- Tombol Close -->
                <button @click="modalUpdate = !modalUpdate"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Mobil</h2>

                <form @submit.prevent="updateCar" class="space-y-4">
                    <!-- Upload gambar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                        <span v-if="formUpdate.errors.image">{{
                            formUpdate.errors.image
                        }}</span>
                        <input type="file" accept="image/*" @change="handleImageUploadU"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
                        <input type="hidden" v-model="formUpdate.old_image" />
                        <!-- Progress upload -->
                        <progress v-if="formUpdate.progress" :value="formUpdate.progress.percentage" max="100"
                            class="w-full mt-2">
                            {{ formUpdate.progress.percentage }}%
                        </progress>
                        <!-- Preview -->
                        <div class="mt-3">
                            <!-- Preview gambar baru -->
                            <img v-if="imagePreviewU" :src="imagePreviewU" alt="Preview Baru"
                                class="w-32 h-24 object-cover rounded border" />

                            <!-- Kalau belum pilih gambar baru, tampilkan gambar lama -->
                            <img v-else-if="selectedCar && selectedCar.image" :src="`/storage/car/${selectedCar.image}`"
                                alt="Gambar Lama" class="w-32 h-24 object-cover rounded border" />

                            <img v-else :src="'/storage/car/default-car.jpg'" alt="Gambar Lama"
                                class="w-32 h-24 object-cover rounded border" />
                        </div>
                    </div>

                    <!-- Grid formUpdate -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
                            <span v-if="formUpdate.errors.brand" class="text-red-700 italic">{{ formUpdate.errors.brand
                            }}</span>
                            <input type="text" v-model="formUpdate.brand"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                            <span v-if="formUpdate.errors.model" class="text-red-700 italic">{{ formUpdate.errors.model
                            }}</span>
                            <input type="text" v-model="formUpdate.model"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat</label>
                            <span v-if="formUpdate.errors.plate_number" class="text-red-700 italic">{{
                                formUpdate.errors.plate_number }}</span>
                            <input type="text" v-model="formUpdate.plate_number"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Warna</label>
                            <span v-if="formUpdate.errors.color" class="text-red-700 italic">{{ formUpdate.errors.color
                            }}</span>
                            <input type="text" v-model="formUpdate.color"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <span v-if="formUpdate.errors.year" class="text-red-700 italic">{{ formUpdate.errors.year
                            }}</span>
                            <input type="number" v-model="formUpdate.year"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga / Hari</label>
                            <span v-if="formUpdate.errors.price_per_day" class="text-red-700 italic">{{
                                formUpdate.errors.price_per_day }}</span>
                            <input type="number" v-model="formUpdate.price_per_day"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Delete -->
        <!-- Backdrop -->
        <div v-if="deleteModal"
            class="fixed inset-0 flex items-start justify-center pt-20 z-50 bg-black/30 backdrop-blur-sm">
            <!-- Modal Box -->
            <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg w-full max-w-md p-6 relative">
                <!-- Tombol X -->
                <button @click="deleteModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <!-- Judul -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    Konfirmasi Hapus
                </h2>

                <!-- Isi -->
                <p class="text-gray-600 mb-6">
                    Yakin ingin menghapus mobil dengan plat nomor:
                    <span class="font-bold text-red-500">{{
                        carPlateNumberDelete
                    }}</span>
                    ?
                </p>

                <!-- Tombol aksi -->
                <div class="flex justify-end space-x-3">
                    <button @click="deleteModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button @click="deleteCar" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
