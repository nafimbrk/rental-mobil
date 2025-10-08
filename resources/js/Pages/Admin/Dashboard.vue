<script setup>
import LayoutAdmin from '../../Components/Layout/LayoutAdmin.vue';

defineProps({
  totalCar: Number,
  totalCustomer: Number,
  totalTransaction: Number
})
</script>

<template>
  <LayoutAdmin>


    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
  <div class="p-6">
      <!-- Card Statistik -->
      <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center justify-between">
        <!-- Kiri (teks) -->
        <div>
          <h2 class="text-lg font-semibold text-gray-600">Total Mobil</h2>
          <p class="text-3xl font-bold text-blue-600 mt-2">{{ totalCar }}</p>
        </div>

        <!-- Kanan (ikon) -->
        <div class="bg-blue-100 p-4 rounded-full">
          <span class="material-icons text-blue-600 text-4xl">directions_car</span>
        </div>
      </div>
    </div>
  <div class="p-6">
      <!-- Card Statistik -->
      <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center justify-between">
        <!-- Kiri (teks) -->
        <div>
          <h2 class="text-lg font-semibold text-gray-600">Total Customer</h2>
          <p class="text-3xl font-bold text-blue-600 mt-2">{{ totalCustomer }}</p>
        </div>

        <!-- Kanan (ikon) -->
        <div class="bg-blue-100 p-4 rounded-full">
          <span class="material-icons text-blue-600 text-4xl">people</span>
        </div>
      </div>
    </div>
  <div class="p-6">
      <!-- Card Statistik -->
      <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center justify-between">
        <!-- Kiri (teks) -->
        <div>
          <h2 class="text-lg font-semibold text-gray-600">Total Transaksi</h2>
          <p class="text-3xl font-bold text-blue-600 mt-2">{{ totalTransaction }}</p>
        </div>

        <!-- Kanan (ikon) -->
        <div class="bg-blue-100 p-4 rounded-full">
          <span class="material-icons text-blue-600 text-4xl">receipt</span>
        </div>
      </div>
    </div>
</div>




  </LayoutAdmin>
</template>

<style>
@import url("https://fonts.googleapis.com/icon?family=Material+Icons");
</style>
