<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { EyeIcon, EyeSlashIcon } from "@heroicons/vue/24/outline";

const formLogin = useForm({
    email: "",
    password: ""
})

const login = () => {
    formLogin.post(route('admin.loginStore'))
}

const showPassword = ref(false);


</script>



<template>

<div class="min-h-screen flex items-center justify-center bg-blue-600 px-4">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
     <!-- Judul -->
<h1 class="text-2xl font-bold text-center text-blue-600 mb-4">
  Login Admin
</h1>

<!-- Alert Error -->
<div
  v-if="$page.props.errors?.email"
  class="mb-6 flex items-center gap-2 rounded-lg border border-red-400 bg-red-50 px-4 py-3 text-sm text-red-600"
>
  <!-- Icon warning -->
  <svg
    xmlns="http://www.w3.org/2000/svg"
    class="h-5 w-5 text-red-500"
    viewBox="0 0 20 20"
    fill="currentColor"
  >
    <path
      fill-rule="evenodd"
      d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-9-4a1 1 0 012 0v4a1 1 0 01-2 0V6zm1 8a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
      clip-rule="evenodd"
    />
  </svg>
  <span>{{ $page.props.errors.email }}</span>
</div>


      <!-- Form -->
      <form @submit.prevent="login" class="space-y-5">
        <!-- Email -->
        <div>
          <label for="email" class="block text-gray-700 font-medium mb-1">
            Email
          </label>
          <input
            type="email"
            id="email"
            v-model="formLogin.email"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
            required
          />
        </div>

        <!-- Password -->
         <div class="mb-4">
    <label for="password" class="block text-gray-700 font-medium mb-1">
      Password
    </label>
    <div class="relative">
      <input
        :type="showPassword ? 'text' : 'password'"
        id="password"
        v-model="formLogin.password"
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none pr-10"
        required
      />
      <!-- Toggle Icon -->
      <button
        type="button"
        @click="showPassword = !showPassword"
        class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none"
      >
        <EyeIcon v-if="!showPassword" class="h-5 w-5" />
        <EyeSlashIcon v-else class="h-5 w-5" />
      </button>
    </div>
  </div>


        <!-- Tombol -->
        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow-md transition"
        >
          Login
        </button>
      </form>
    </div>
  </div>


</template>
