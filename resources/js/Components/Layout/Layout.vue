<script setup>
import Navbar from '../Util/Navbar.vue'
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <Navbar />

    <!-- Konten utama -->
    <main class="flex-1 pt-16"> <!-- kasih padding-top biar gak ketiban navbar -->
      <slot />
    </main>
  </div>
</template>
