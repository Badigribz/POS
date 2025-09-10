<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold">Admin Dashboard</h1>

    <!-- Add Product Form -->
    <form @submit.prevent="addProduct" class="mb-6">
      <input v-model="newProduct.name" type="text" placeholder="Product Name" class="border p-2 mr-2" />
      <input v-model="newProduct.price" type="number" placeholder="Price" step="0.01" class="border p-2 mr-2" />
      <input v-model="newProduct.quantity" type="number" placeholder="Quantity" class="border p-2 mr-2" />
      <button type="submit" class="bg-green-500 text-black px-4 py-2">Add</button>
    </form>

    <!-- Product List -->
    <table class="border-collapse border w-full">
      <thead>
        <tr>
          <th class="border p-2">ID</th>
          <th class="border p-2">Name</th>
          <th class="border p-2">Price</th>
          <th class="border p-2">Quantity</th>
          <th class="border p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td class="border p-2">{{ product.id }}</td>
          <td class="border p-2" v-if="editId !== product.id">{{ product.name }}</td>
          <td class="border p-2" v-else>
            <input v-model="editProduct.name" class="border p-1" />
          </td>

          <td class="border p-2" v-if="editId !== product.id">{{ product.price }}</td>
          <td class="border p-2" v-else>
            <input v-model="editProduct.price" type="number" class="border p-1" />
          </td>

          <td class="border p-2" v-if="editId !== product.id">{{ product.quantity }}</td>
          <td class="border p-2" v-else>
            <input v-model="editProduct.quantity" type="number" class="border p-1" />
          </td>

          <td class="border p-2">
            <template v-if="editId === product.id">
              <button @click="updateProduct(product.id)" class="bg-blue-500 text-black px-2 py-1 mr-2">Save</button>
              <button @click="cancelEdit" class="bg-gray-500 text-black px-2 py-1">Cancel</button>
            </template>
            <template v-else>
              <button @click="startEdit(product)" class="bg-yellow-500 text-black px-2 py-1 mr-2">Edit</button>
              <button @click="deleteProduct(product.id)" class="bg-red-500 text-black px-2 py-1">Delete</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>

    <button @click="logout" class="mt-4 px-4 py-2 bg-red-600 text-black rounded">
      Logout
    </button>
  </div>
</template>

<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ref, onMounted } from 'vue'

const router = useRouter()
const products = ref([])
const newProduct = ref({
  name: '',
  price: '',
  quantity: ''
})

const editId = ref(null)
const editProduct = ref({ name: '', price: '', quantity: '' })

// Fetch products
const getProducts = async () => {
  try {
    const res = await axios.get('/api/products')
    products.value = res.data
  } catch (err) {
    console.error('Error fetching products:', err)
  }
}

// Add product
const addProduct = async () => {
  try {
    await axios.post('/api/products', newProduct.value)
    newProduct.value = { name: '', price: '', quantity: '' }
    getProducts() // Refresh list
  } catch (err) {
    console.error('Error adding product:', err)
  }
}

// Delete product
const deleteProduct = async (id) => {
  try {
    await axios.delete(`/api/products/${id}`)
    getProducts() // Refresh list
  } catch (err) {
    console.error('Error deleting product:', err)
  }
}

// Start editing
const startEdit = (product) => {
  editId.value = product.id
  editProduct.value = { ...product }
}

// Cancel edit
const cancelEdit = () => {
  editId.value = null
  editProduct.value = { name: '', price: '', quantity: '' }
}

// Update product
const updateProduct = async (id) => {
  try {
    await axios.put(`/api/products/${id}`, editProduct.value)
    editId.value = null
    getProducts()
  } catch (err) {
    console.error('Error updating product:', err)
  }
}

onMounted(() => {
  getProducts()
})

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
