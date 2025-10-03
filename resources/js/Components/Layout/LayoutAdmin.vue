<script setup>
import Sidebar from '../Util/Sidebar.vue'
import { ref, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const isOpenSide = ref(true) // untuk toggle sidebar
const openUserMenu = ref(false);
const openLogoutModal = ref(false);

// Close dropdown when clicking outside
const closeDropdown = (event) => {
  const dropdown = event.target.closest('.relative');
  if (!dropdown) {
    openUserMenu.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});


const formLogout = useForm({})

const logout = () => {
    formLogout.post(route('admin.logout'))
}
</script>

<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar v-model:isOpen="isOpenSide" />

    <!-- Konten utama -->
    <div
      :class="[isOpenSide ? 'ml-64' : 'ml-20', 'flex-1 transition-all duration-300']"
    >
      <!-- Navbar -->
      <nav
        class="bg-white/90 backdrop-blur-md shadow-md fixed top-0 left-0 right-0 w-full z-30 h-16 flex items-center transition-all duration-300"
      >
        <div class="flex justify-between items-center w-full px-6">
          <!-- Logo RentalMobil di kiri -->
          <div class="flex items-center gap-2">
            <span
              class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-lg shadow-md"
            >
              R
            </span>
            <h1 class="text-2xl font-extrabold text-blue-600 tracking-tight hover:scale-105 transition-transform duration-200">
              RentalMobil
            </h1>
          </div>

          <!-- User menu di kanan -->
          <div class="relative">
            <button
              @click.stop="openUserMenu = !openUserMenu"
              class="flex items-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full"
            >
              <img
                src="https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff"
                alt="User Avatar"
                class="w-10 h-10 rounded-full border border-gray-300 hover:border-blue-500 transition-colors"
              />
            </button>

            <!-- Dropdown -->
            <transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="openUserMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-20 border border-gray-200"
              >
                <a
                  href="/profile"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  Profil
                </a>
                <button
                  @click="logout"
                  class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  Logout
                </button>
              </div>
            </transition>
          </div>
        </div>
      </nav>

      <!-- Konten -->
      <main class="p-6 mt-16"> <!-- mt-16 karena navbar fixed -->
        <slot />
      </main>
    </div>
  </div>
</template>
