<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold">Cashier Dashboard</h1>


    <!-- Products Table -->
    <h2 class="text-xl font-semibold mb-2">Available Products</h2>
    <table class="border-collapse border w-full mb-6">
      <thead>
        <tr>
          <th class="border p-2">Name</th>
          <th class="border p-2">Price</th>
          <th class="border p-2">Stock</th>
          <th class="border p-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td class="border p-2">{{ product.name }}</td>
          <td class="border p-2">Ksh {{ product.price }}</td>
          <td class="border p-2">{{ product.quantity }}</td>
          <td class="border p-2">
            <button
              @click="addToCart(product)"
              :disabled="product.quantity === 0"
              class="bg-blue-500 text-white px-3 py-1 disabled:bg-gray-400"
            >
              Add to Cart
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Cart Section -->
    <h2 class="text-xl font-semibold mb-2">Cart</h2>
    <table class="border-collapse border w-full mb-4">
      <thead>
        <tr>
          <th class="border p-2">Name</th>
          <th class="border p-2">Price</th>
          <th class="border p-2">Qty</th>
          <th class="border p-2">Total</th>
          <th class="border p-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in cart" :key="item.id">
          <td class="border p-2">{{ item.name }}</td>
          <td class="border p-2">Ksh {{ item.price }}</td>
          <td class="border p-2">
            <input
              type="number"
              v-model.number="item.qty"
              min="1"
              :max="item.stock"
              class="border p-1 w-16"
              @change="updateCart(item)"
            />
          </td>
          <td class="border p-2">Ksh {{ item.price * item.qty }}</td>
          <td class="border p-2">
            <button @click="removeFromCart(item.id)" class="bg-red-500 text-white px-2 py-1">Remove</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Total & Process Sale -->
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-bold">Total: Ksh {{ totalPrice }}</h3>
      <button
        @click="processSale"
        :disabled="cart.length === 0"
        class="bg-green-500 text-white px-4 py-2 disabled:bg-gray-400"
      >
        Process Sale
      </button>
    </div>
  

    <button @click="logout" class="mt-4 px-4 py-2 bg-red-600 text-white rounded">
      Logout
    </button>
  </div>
</template>

<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'

const router = useRouter()
const products = ref([])
const cart = ref([])

// Fetch products from backend
const getProducts = async () => {
  try {
    const res = await axios.get('/api/products')
    products.value = res.data
  } catch (err) {
    console.error('Error fetching products:', err)
  }
}

// Add product to cart
const addToCart = (product) => {
  const existing = cart.value.find((item) => item.id === product.id)
  if (existing) {
    if (existing.qty < product.quantity) {
      existing.qty++
    }
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      price: product.price,
      qty: 1,
      stock: product.quantity
    })
  }
}

// Remove from cart
const removeFromCart = (id) => {
  cart.value = cart.value.filter((item) => item.id !== id)
}

// Update cart qty
const updateCart = (item) => {
  if (item.qty > item.stock) {
    item.qty = item.stock
  } else if (item.qty < 1) {
    item.qty = 1
  }
}

// Total price
const totalPrice = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.price * item.qty, 0)
})

// Process sale (placeholder for now)
const processSale = async () => {
  try {
    // We will replace this with Daraja API call later
    alert(`Sale processed! Total: Ksh ${totalPrice.value}`)
    cart.value = []
    getProducts() // refresh stock
  } catch (err) {
    console.error('Error processing sale:', err)
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
