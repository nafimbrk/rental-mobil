<script setup>
import { router, useForm } from "@inertiajs/vue3";
import LayoutAdmin from "../../../Components/Layout/LayoutAdmin.vue";
import { computed, ref, watch } from "vue";
import { route } from "ziggy-js";
import dayjs from "dayjs";
import vSelect from "vue3-select";
import "vue3-select/dist/vue3-select.css";

const props = defineProps({
    maintenances: Object,
    carList: Array,
    filters: Object
});

let modalCreate = ref(false);

const openModalCreate = () => {
    modalCreate.value = !modalCreate.value;
};

const formStore = useForm({
    car_id: "",
    description: "",
    maintenance_date: "",
    cost: ""
});

const formattedCarList = computed(() =>
    props.carList.map(car => ({
        ...car,
        display: `${car.model} - ${car.plate_number}`,
    }))
);

const storeMaintenance = () => {
    formStore.post(route("admin.maintenance.store"), {
        onSuccess: () => {
            formStore.reset();
            modalCreate.value = false;
        },
    });
};

let modalUpdate = ref(false);

const formUpdate = useForm({
    car_id: "",
    description: "",
    maintenance_date: "",
    cost: ""
});

let idMaintenanceU = ref("");

const openModalUpdate = (maintenance) => {
    modalUpdate.value = true;

    formUpdate.car_id = maintenance.car_id;
    formUpdate.description = maintenance.description;
    formUpdate.maintenance_date = maintenance.maintenance_date;
    formUpdate.cost = maintenance.cost;

    idMaintenanceU.value = maintenance.id;
};

const updateMaintenance = () => {
    formUpdate.put(route('admin.maintenance.update', idMaintenanceU.value), {
        onSuccess: () => {
            formUpdate.reset();
            modalUpdate.value = false;  
        },
    });
};

const formDelete = useForm({});

let deleteModal = ref(false);
let idMaintenanceD = ref("");
let plateNumber = ref("");

const openDeleteModal = (maintenance) => {
    deleteModal.value = !deleteModal.value;
    idMaintenanceD.value = maintenance.id;
    plateNumber.value = maintenance.car.plate_number;
};

const deleteMaintenance = () => {
    formDelete.delete(route("admin.maintenance.destroy", idMaintenanceD.value), {
        onSuccess: (deleteModal.value = !deleteModal.value),
    });
};

let search = ref(props.filters?.search ?? "");

// 🔎 search realtime + debounce langsung di watch
let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route("admin.maintenance.index"),
            { search: value },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300); // delay 300ms
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0, // hilangin nol dibelakang
        maximumFractionDigits: 0,
    }).format(angka);
};

const resetFilter = () => {
    search.value = ""
}

let modalEndMaintenance = ref(false)
let carMaintenance = ref("")
let idMaintenance = ref("")

const openmodalEndMaintenance = (maintenance) => {
    modalEndMaintenance.value = true
    carMaintenance.value = maintenance.car.plate_number
    idMaintenance.value = maintenance.id
}

const endMaintenance = () => {
    router.post(route('admin.maintenance.end', idMaintenance.value), {
        onSuccess: () => {
            modalEndMaintenance.value = false
            router.reload({ only: ['maintenances'] })
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
                List operator
            </h2>

            <div class="flex gap-2">
                <!-- Search -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-2 flex items-center text-gray-400">
                        <span class="material-icons text-sm">search</span>
                    </span>
                    <input type="text" v-model="search" placeholder="Cari operator..."
                        class="border rounded-lg pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>


                <button @click="resetFilter"
                    class="flex items-center gap-1 px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    <span class="material-icons text-base">restart_alt</span>
                    Reset
                </button>

                <!-- Tambah operator -->
                <button @click="openModalCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition">
                    <span class="material-icons text-sm">person_add</span>
                    Tambah operator
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full text-sm text-gray-600">
                <thead class="bg-gray-100 text-gray-700 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Mobil</th>
                        <th class="px-4 py-3 text-left">Plat Nomor</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">Tanggal Perbaikan</th>
                        <th class="px-4 py-3 text-left">Biaya</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Tanggal Selesai</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="maintenance in maintenances.data" :key="maintenance.id"
                        class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ maintenance.car.model }}</td>
                        <td class="px-4 py-3">{{ maintenance.car.plate_number }}</td>
                        <td class="px-4 py-3">{{ maintenance.description }}</td>
                        <td class="px-4 py-3">{{ dayjs(maintenance.maintenance_date).format('DD-MM-YYYY') }}</td>
                        <td class="px-4 py-3">{{ formatRupiah(maintenance.cost) }}</td>
                        <td class="px-4 py-3">
                            <button @click="openmodalEndMaintenance(maintenance)"
                                v-if="maintenance.status === 'in_progress'"
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                Progress
                            </button>
                            <span v-else
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                Selesai
                            </span>
                        </td>
                        <td class="px-4 py-3" v-if="maintenance.end_date">
                            {{ dayjs(maintenance.end_date).format('DD-MM-YYYY') }}
                        </td>
                        <td v-else class="px-4 py-3">
                            Belum Selesai
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex space-x-2">
                                <button @click="openModalUpdate(maintenance)"
                                    class="p-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 flex items-center justify-center"
                                    title="Edit Mobil">
                                    <span class="material-icons text-sm">edit</span>
                                </button>
                                <button @click="openDeleteModal(maintenance)"
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
                maintenances.prev_page_url &&
                router.get(
                    maintenances.prev_page_url,
                    {},
                    { preserveState: true, preserveScroll: true }
                )
                " class="px-3 py-1.5 rounded-lg border text-sm transition-all duration-200" :class="maintenances.prev_page_url
                    ? 'bg-white text-gray-600 border-gray-300 hover:bg-blue-50 hover:text-blue-600'
                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    " :disabled="!maintenances.prev_page_url">
                «
            </button>

            <!-- Numbered Pages -->
            <button v-for="link in maintenances.links.filter((l) => !isNaN(l.label))" :key="link.url ?? link.label"
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
                maintenances.next_page_url &&
                router.get(
                    maintenances.next_page_url,
                    {},
                    { preserveState: true, preserveScroll: true }
                )
                " class="px-3 py-1.5 rounded-lg border text-sm transition-all duration-200" :class="maintenances.next_page_url
                    ? 'bg-white text-gray-600 border-gray-300 hover:bg-blue-50 hover:text-blue-600'
                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                    " :disabled="!maintenances.next_page_url">
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
                    Tambah operator Baru
                </h2>

                <form @submit.prevent="storeMaintenance" class="space-y-4">
                    <!-- Grid formStore -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobil</label>
                            <span v-if="formStore.errors.car_id" class="text-red-700 italic">{{
                                formStore.errors.car_id }}</span>
                            <v-select v-model="formStore.car_id" :options="formattedCarList" label="display"
                                :reduce="car => car.id" placeholder="pilih mobil" @option:selected="hitungTotal"
                                class="w-full border border-gray-300 rounded-lg text-sm" />

                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <span v-if="formStore.errors.description" class="text-red-700 italic">
                                {{ formStore.errors.description }}
                            </span>
                            <textarea v-model="formStore.description" rows="4"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200 resize-none"
                                placeholder="Tulis deskripsi di sini..."></textarea>
                        </div>



                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perbaikan</label>
                            <span v-if="formStore.errors.maintenance_date" class="text-red-700 italic">{{
                                formStore.errors.maintenance_date }}</span>
                            <input type="date" v-model="formStore.maintenance_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Perbaikan</label>
                            <span v-if="formStore.errors.cost" class="text-red-700 italic">{{ formStore.errors.cost
                            }}</span>
                            <input type="number" v-model="formStore.cost"
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

                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    Edit operator
                </h2>

                <form @submit.prevent="updateMaintenance" class="space-y-4">
                    <!-- Grid formStore -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobil</label>
                            <span v-if="formUpdate.errors.car_id" class="text-red-700 italic">{{
                                formUpdate.errors.car_id }}</span>
                            <v-select v-model="formUpdate.car_id" :options="formattedCarList" label="display"
                                :reduce="car => car.id" placeholder="pilih mobil"
                                class="w-full border border-gray-300 rounded-lg text-sm" />

                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <span v-if="formUpdate.errors.description" class="text-red-700 italic">
                                {{ formUpdate.errors.description }}
                            </span>
                            <textarea v-model="formUpdate.description" rows="4"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200 resize-none"
                                placeholder="Tulis deskripsi di sini..."></textarea>
                        </div>



                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perbaikan</label>
                            <span v-if="formUpdate.errors.maintenance_date" class="text-red-700 italic">{{
                                formUpdate.errors.maintenance_date }}</span>
                            <input type="date" v-model="formUpdate.maintenance_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring focus:ring-blue-200" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Perbaikan</label>
                            <span v-if="formUpdate.errors.cost" class="text-red-700 italic">{{ formUpdate.errors.cost
                            }}</span>
                            <input type="number" v-model="formUpdate.cost"
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
                    Yakin ingin menghapus maintenance untuk mobil:
                    <span class="font-bold text-red-500">{{
                        plateNumber
                    }}</span>
                    ?
                </p>

                <!-- Tombol aksi -->
                <div class="flex justify-end space-x-3">
                    <button @click="deleteModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button @click="deleteMaintenance" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            </div>
        </div>


        <!-- Modal end aintenance -->
        <div v-if="modalEndMaintenance"
            class="fixed inset-0 flex items-start justify-center pt-20 z-50 bg-black/30 backdrop-blur-sm">
            <!-- Modal Box -->
            <div class="bg-white/80 backdrop-blur-md rounded-xl shadow-lg w-full max-w-md p-6 relative">
                <!-- Tombol X -->
                <button @click="modalEndMaintenance = false"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                    ✕
                </button>

                <!-- Judul -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    Konfirmasi Hapus
                </h2>

                <!-- Isi -->
                <p class="text-gray-600 mb-6">
                    Yakin ingin menyelesaikan maintenance mobil:
                    <span class="font-bold text-red-500">{{
                        carMaintenance
                    }}</span>
                    ?
                </p>

                <!-- Tombol aksi -->
                <div class="flex justify-end space-x-3">
                    <button @click="modalEndMaintenance = false"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button @click="endMaintenance" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </LayoutAdmin>
</template>
