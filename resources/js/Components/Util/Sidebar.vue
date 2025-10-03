<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

// Dummy untuk cek menu aktif (biasanya dari Inertia props)
const currentRoute = route().current();
</script>

<template>
  <!-- Sidebar -->
  <aside
    class="bg-gradient-to-b from-blue-600 to-blue-700 text-white shadow-2xl h-[calc(100vh-4rem)] w-64 p-5 flex flex-col fixed top-16 left-0 z-10"
  >
    <!-- Menu -->

    <!-- Menu -->
    <nav class="flex flex-col gap-2">
      <Link
        :href="route('admin.dashboard')"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200"
        :class="currentRoute === 'admin.dashboard'
          ? 'bg-white text-blue-600 shadow-md'
          : 'hover:bg-blue-500 hover:shadow-lg'"
      >
        <span class="material-icons">home</span>
        <span>Dashboard</span>
      </Link>

      <Link
        :href="route('admin.car.index')"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200"
        :class="currentRoute === 'admin.car.index'
          ? 'bg-white text-blue-600 shadow-md'
          : 'hover:bg-blue-500 hover:shadow-lg'"
      >
        <span class="material-icons">directions_car</span>
        <span>Daftar Mobil</span>
      </Link>

      <Link
        :href="route('admin.customer.index')"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200"
        :class="currentRoute === 'admin.customer.index'
          ? 'bg-white text-blue-600 shadow-md'
          : 'hover:bg-blue-500 hover:shadow-lg'"
      >
        <span class="material-icons">people</span>
        <span>User</span>
      </Link>

      <Link
        :href="route('admin.transaction.index')"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-blue-500 hover:shadow-lg"
        :class="currentRoute === 'admin.transaction.index'
          ? 'bg-white text-blue-600 shadow-md'
          : 'hover:bg-blue-500 hover:shadow-lg'"
      >
        <span class="material-icons">receipt</span>
        <span>Transaksi</span>
    </Link>

      <a
        href="#"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-blue-500 hover:shadow-lg"
      >
        <span class="material-icons">settings</span>
        <span>Pengaturan</span>
      </a>
    </nav>

  </aside>
</template>

<style>
@import url("https://fonts.googleapis.com/icon?family=Material+Icons");
</style>
