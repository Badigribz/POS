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

const email = ref('');
const password = ref('');

const login = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); // very important
    const res = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    });
    console.log('Login success:', res.data);
  } catch (err) {
    console.error('Login error:', err.response);
  }
};
</script>

