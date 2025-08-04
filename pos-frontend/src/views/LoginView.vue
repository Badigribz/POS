<template>
  <form @submit.prevent="login">
    <input v-model="email" type="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button type="submit">Login</button>
  </form>
</template>

<script setup>
import { ref } from 'vue';
import axios from '../axios';
import { useRouter } from 'vue-router'

const email = ref('');
const password = ref('');
const router = useRouter();

const login = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); // very important
    const res = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    });

    const token = res.data.token;

    // Save token
    localStorage.setItem('token', token);
    // Set axios default header
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    console.log('Login success:', res.data);
    router.push('/dashboard') // ✅ redirect after login
  } catch (err) {
    console.error('Login error:', err.response);
  }
};
</script>

