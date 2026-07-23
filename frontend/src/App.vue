<template>
  <router-view />
</template>

<script setup>
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
// Load user from localStorage on app start
authStore.loadUserFromStorage()
</script>