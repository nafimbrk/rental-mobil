<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '../Components/Layout/Layout.vue';
import { route } from 'ziggy-js';

</script>

<template>
  <Layout>
    <div class="min-h-screen bg-white flex items-center justify-center px-6">
    <div class="bg-white rounded-2xl shadow-2xl p-10 max-w-2xl text-center">
      <!-- Judul -->
      <h1 class="text-4xl font-extrabold text-blue-600 mb-4">
        Selamat Datang di Rental MobilKu 🚗
      </h1>

      <!-- Deskripsi -->
      <p class="text-gray-600 mb-6 leading-relaxed">
        Temukan mobil impianmu dengan mudah dan cepat.
        Kami menyediakan berbagai pilihan mobil yang siap menemani perjalananmu,
        baik untuk liburan, bisnis, atau sekadar jalan santai.
      </p>

      <!-- Tombol -->
      <Link
        :href="route('car.index')"
        class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-300"
      >
        Lihat Daftar Mobil
      </Link>

      <!-- Tambahan ilustrasi -->
      <div class="mt-8">
        <img
          :src="'/storage/asset/car-icon2.jpg'"
          alt="Car Icon"
          class="w-28 mx-auto drop-shadow-lg"
        />
      </div>
    </div>
  </div>
  </Layout>
</template>
