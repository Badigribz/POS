<template>
    <v-app>
    <v-app-bar app color="blue" dense>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>GRIBZ SHOP</v-toolbar-title>
      <!-- <v-spacer></v-spacer> -->
      <v-toolbar-items class="hidden-sm-and-down" v-show="!isLoggedIn">
          <v-btn text :to="{ path: '/login'}">Login</v-btn>
          <v-btn text :to="{ path: '/'}">Signup</v-btn>
      </v-toolbar-items>
      <CartIcon v-show="!isLoggedIn"/>
      {{ isLoggedIn }}
      <v-btn text @click="logout" v-show="isLoggedIn">Logout</v-btn>
    </v-app-bar>

   <v-navigation-drawer app v-model="drawer" temporary>
    <v-list>
      <v-list-item link :to="{ path: '/login' }"><v-list-item-title>Login</v-list-item-title></v-list-item>
      <!-- <v-list-item link :to="{ path:'/signup' }"><v-list-item-title>Signup</v-list-item-title></v-list-item> -->
    </v-list>
   </v-navigation-drawer>


  <v-main>
    <v-sheet
       class="mx-auto pa-12 mt-5"
       max-width="500"
       elevation="12">
        <v-form @submit.prevent="login">
        <v-text-field
            v-model="email"
            label="Enter email"
            type="email"
            variant="outlined"></v-text-field>
        <v-text-field
            v-model="password"
            label="Enter password"
            type="password"
            variant="outlined"></v-text-field>

        <v-btn
        color="blue"
        density="comfortable"
        rounded="sm"
        elevation="12"
        type="submit">Login</v-btn>
        </v-form>
    </v-sheet>
    <!-- <RouterView/> -->
  </v-main>
</v-app>

</template>

<script setup>
import { ref } from 'vue';
import axios from '../axios';
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification';

const email = ref('');
const password = ref('');
const router = useRouter();
const toast = useToast();

const login = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); // very important
    const res = await axios.post('/api/login', {
      email: email.value,
      password: password.value,
    });

    const token = res.data.token;
    const role = res.data.user.role;


    // Save token
    localStorage.setItem('token', token);
    // Save role
    localStorage.setItem('role', role);
    // Set axios default header
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    toast.success('Login successful!');

    // Role-based redirect
    console.log('Login success:', res.data);
    if (role === 'admin') {
     router.push('/admindashboard');
     } else if (role === 'cashier') {
     router.push('/cashierdashboard');
     } else {
      router.push('/dashboard'); // fallback
     }
   // router.push('/dashboard') // ✅ redirect after login
  } catch (err) {
    console.error('Login error:', err.response);

        if (err.response?.status === 422) {
      toast.error('The email or password is incorrect. Please try again.');
    } else {
      toast.error('An error occurred. Please try again later.');
    }

  }
};
</script>

