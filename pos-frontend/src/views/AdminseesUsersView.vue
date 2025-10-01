<template>
  <div class="p-4">
    <v-app>
      <v-app-bar app color="blue" dense>
        <v-toolbar-title>ALL USERS</v-toolbar-title>
        <v-btn text :to="{ path: '/' }">Dashboard</v-btn>
      </v-app-bar>

      <v-main>
        <v-container class="mt-5">
          <v-row>
            <v-col
              v-for="u in users"
              :key="u.id"
              cols="12"
              sm="6"
              md="4"
            >
              <v-card class="pa-4" elevation="4">
                <v-avatar size="80" class="mx-auto mb-3">
                  <v-img
                    v-if="u.profile_picture_url"
                    :src="u.profile_picture_url"
                  />
                  <v-icon v-else size="48">mdi-account</v-icon>
                </v-avatar>

                <h3 class="text-center">{{ u.name }}</h3>
                <p class="text-center text-gray-500">{{ u.email }}</p>
                <p class="text-center font-bold">{{ u.role ?? 'User' }}</p>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
      </v-main>
    </v-app>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from "vue"

const users = ref([])

const getUsers = async () => {
  try {
    const res = await axios.get("/api/users")
    users.value = res.data
  } catch (err) {
    console.error("Error fetching users:", err)
  }
}

onMounted(() => {
  getUsers()
})
</script>
