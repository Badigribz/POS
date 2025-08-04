<template>
  <div class="max-w-md mx-auto p-4 border rounded mt-10">
    <h2 class="text-xl font-bold mb-4">Register</h2>

    <form @submit.prevent="register">
      <div class="mb-4">
        <label class="block mb-1">Name</label>
        <input v-model="form.name" type="text" class="w-full p-2 border rounded" required />
      </div>

      <div class="mb-4">
        <label class="block mb-1">Email</label>
        <input v-model="form.email" type="email" class="w-full p-2 border rounded" required />
      </div>

      <div class="mb-4">
        <label class="block mb-1">Password</label>
        <input v-model="form.password" type="password" class="w-full p-2 border rounded" required />
      </div>

      <div class="mb-4">
        <label class="block mb-1">Confirm Password</label>
        <input v-model="form.password_confirmation" type="password" class="w-full p-2 border rounded" required />
      </div>

      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Register</button>

      <p class="text-red-600 mt-2" v-if="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const error = ref(null)

const register = async () => {
  error.value = null

  try {
    // Get CSRF cookie first
    await axios.get('/sanctum/csrf-cookie')

    // Then send registration request
    const res = await axios.post('/api/register', form.value)

    console.log('Registration successful:', res.data)
    // You can redirect or set user state here
  } catch (err) {
    console.error(err)
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Registration failed'
    }
  }
}
</script>
