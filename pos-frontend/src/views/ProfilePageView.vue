<template>
  <div class="p-4">
    <v-app>
      <v-app-bar app color="blue" dense>
        <v-toolbar-title>USER PROFILE</v-toolbar-title>
        <v-btn text :to="{ path: '/' }">Dashboard</v-btn>
      </v-app-bar>

      <v-main>
        <v-container class="mt-5">
          <v-sheet class="mx-auto pa-6" max-width="600" elevation="12">
            <h1 class="text-2xl text-center font-bold mb-4">Update Profile</h1>

            <v-avatar size="120" class="mx-auto mb-4">
              <v-img v-if="user?.profile_picture_url" :src="user.profile_picture_url" />
              <v-icon v-else size="64">mdi-account</v-icon>
            </v-avatar>


            <v-form @submit.prevent="updateProfile">

              <v-file-input
                v-model="form.profile_picture"
                label="Profile Picture"
                accept="image/*"
                prepend-icon="mdi-camera"
              />

              <!-- Name -->
              <v-text-field
                v-model="form.name"
                label="Name"
                variant="outlined"
                dense
                required
              />

              <!-- Email -->
              <v-text-field
                v-model="form.email"
                label="Email"
                type="email"
                variant="outlined"
                dense
                required
              />

                            <v-text-field
                v-model="form.phone"
                label="Phone Number"
                type="tel"
                variant="outlined"
                dense
              />

              <!-- Password -->
              <v-text-field
                v-model="form.password"
                label="New Password"
                type="password"
                variant="outlined"
                dense
              />

              <!-- Confirm Password -->
              <v-text-field
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
                variant="outlined"
                dense
              />

              <v-btn type="submit" color="green" elevation="8" class="mt-4">
                Save Changes
              </v-btn>
            </v-form>
          </v-sheet>
        </v-container>
      </v-main>
    </v-app>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, onMounted } from "vue"
import { useToast } from "vue-toastification"

const toast = useToast()
const form = ref({
name: "",
email: "",
phone: "",
password: "",
password_confirmation: "",
 })
const user = ref(null)

// Fetch user profile
const getUser = async () => {
  try {
    const res = await axios.get("/api/user")
    user.value = res.data
    form.value.name = res.data.name
    form.value.email = res.data.email
    form.value.phone = res.data.phone
  } catch (err) {
    console.error("Error fetching user:", err)
  }
}

// Update profile
// const updateProfile = async () => {
//   try {
//     const res = await axios.put("/api/user", form.value)
//     toast.success("Profile updated successfully")
//     user.value = res.data.user // update local state
//     // clear password fields after update
//     form.value.password = ""
//     form.value.password_confirmation = ""
//   } catch (err) {
//     console.error("Error updating profile:", err)
//     toast.error("Failed to update profile")
//   }
// }

const updateProfile = async () => {
  try {
    const formData = new FormData()
    formData.append("name", form.value.name)
    formData.append("email", form.value.email)
    formData.append("phone", form.value.phone ?? "")

    if (form.value.profile_picture instanceof File) {
      formData.append("profile_picture", form.value.profile_picture)
    }

    if (form.value.password) {
      formData.append("password", form.value.password)
      formData.append("password_confirmation", form.value.password_confirmation)
    }

    // Laravel requires this for file upload + PUT
    formData.append("_method", "PUT")

    const res = await axios.post("/api/user", formData, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    })

    toast.success("Profile updated successfully")
    user.value = res.data.user
  } catch (err) {
    console.error("Error updating profile:", err.response?.data || err)
    toast.error("Failed to update profile")
  }
}

onMounted(() => {
  getUser()
})
</script>
