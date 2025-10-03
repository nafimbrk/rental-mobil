<script setup>
import { ref } from 'vue'
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const isOpen = ref(false)
</script>

<template>
  <nav class="bg-blue-600 shadow-md fixed w-full z-20 top-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">


        <div class="flex items-center gap-2">
          <span
            class="flex items-center justify-center w-10 h-10 rounded-xl bg-white text-blue-600 font-bold text-lg shadow-md">
            R
          </span>
          <h1
            class="text-2xl font-extrabold text-white tracking-tight hover:scale-105 transition-transform duration-200">
            RentalMobil
          </h1>
        </div>

        <!-- Menu desktop -->
        <div class="hidden md:flex items-center space-x-8 font-medium">
          <Link href="/" class="relative text-white hover:text-gray-200 group">
          Home
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-white transition-all group-hover:w-full"></span>
          </Link>
          <Link :href="route('car.index')" class="relative text-white hover:text-gray-200 group">
          Daftar Mobil
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-white transition-all group-hover:w-full"></span>
          </Link>
          <Link href="" class="relative text-white hover:text-gray-200 group">
          Tentang
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-white transition-all group-hover:w-full"></span>
          </Link>
          <Link href="#" class="relative text-white hover:text-gray-200 group">
          Kontak
          <span class="absolute left-0 -bottom-1 w-0 h-0.5 bg-white transition-all group-hover:w-full"></span>
          </Link>
        </div>

        <!-- Tombol mobile -->
        <div class="md:hidden flex items-center">
          <button @click="isOpen = !isOpen" class="text-white hover:text-gray-200 focus:outline-none">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path v-if="!isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"></path>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
              </path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Menu mobile -->
    <transition enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform -translate-y-4 opacity-0" enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in" leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-4 opacity-0">
      <div v-if="isOpen" class="md:hidden bg-blue-600 shadow-lg">
        <div class="px-4 pt-3 pb-4 space-y-2">
          <a href="#" class="block px-3 py-2 rounded-md text-white hover:bg-blue-700">
            Home
          </a>
          <a href="#" class="block px-3 py-2 rounded-md text-white hover:bg-blue-700">
            Daftar Mobil
          </a>
          <a href="#" class="block px-3 py-2 rounded-md text-white hover:bg-blue-700">
            Tentang
          </a>
          <a href="#" class="block px-3 py-2 rounded-md text-white hover:bg-blue-700">
            Kontak
          </a>
        </div>
      </div>
    </transition>
  </nav>

</template>
