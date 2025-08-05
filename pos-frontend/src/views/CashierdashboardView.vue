<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold">Welcome to the Cashier Dashboard</h1>
    <p>You are a cashier? Get yo money up</p>

    <button @click="logout" class="mt-4 px-4 py-2 bg-red-600 text-white rounded">
      Logout
    </button>
  </div>
</template>

<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = async () => {
  try {
    await axios.post('/api/logout')

    // Clear token from storage and axios
    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];

    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>
