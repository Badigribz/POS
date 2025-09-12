<template>
      <v-app>
    <v-app-bar app color="blue" dense>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>GRIBZ SHOP</v-toolbar-title>
      <v-spacer></v-spacer>
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
       <v-form @submit.prevent="register">
        <v-text-field
          v-model="username"
          label="Enter username"
          type="text"
          variant="outlined"></v-text-field>
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
        <v-text-field
          v-model="password_confirmation"
          label="Confirm Password"
          type="password"
          variant="outlined"></v-text-field>

        <!-- Error Message -->
        <v-alert
          v-if="error"
          type="error"
          dense
          class="mb-3"
        >
          {{ error }}
        </v-alert>

        <!-- Register Button -->
        <v-btn
          color="blue"
          density="comfortable"
          rounded="sm"
          elevation="12"
          type="submit">
          Register</v-btn>
        </v-form>
 </v-sheet>
    <!-- <RouterView/> -->
  </v-main>
</v-app>

</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const username= ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')

const router = useRouter()
const error = ref(null)
const toast = useToast()

const register = async () => {
  error.value = null

  try {
    // Get CSRF cookie first
    await axios.get('/sanctum/csrf-cookie')

    // Then send registration request
    const res = await axios.post('/api/register', {
    name: username.value,
    email: email.value,
    password: password.value,
    password_confirmation: password_confirmation.value
    })

    console.log('Registration successful:', res.data)
    toast.success('Registration successful! Please log in.')
     router.push({ path: 'Login' })
  } catch (err) {
    console.error(err)

    if (err.response?.data?.message) {
      // Validation errors from Laravel
      const messages = Object.values(err.response.data.errors).flat().join(' ')
      toast.error(messages)
    // eslint-disable-next-line no-dupe-else-if
    } else if (err.response?.data?.message) {
      toast.error(err.response.data.message)
    } else {
      toast.error('Registration failed. Please try again.')
    }
  }
}
</script>
