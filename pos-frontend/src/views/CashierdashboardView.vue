<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold">Cashier POS</h1>

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
              class="bg-blue-500 text-black px-3 py-1 disabled:bg-gray-400"
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
            <button @click="removeFromCart(item.id)" class="bg-red-500 text-black px-2 py-1">Remove</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Payment Method -->
    <div class="mb-4">
      <label class="block mb-1 font-semibold">Payment Method</label>
      <select v-model="paymentMethod" class="border p-2 w-full">
        <option value="cash">Cash</option>
        <option value="mpesa">M-Pesa</option>
        <option value="card">Card</option>
      </select>
    </div>

    <!-- MPESA Code -->
    <div v-if="paymentMethod === 'mpesa'" class="mb-4">
      <label class="block mb-1">M-Pesa Transaction Code</label>
      <input v-model="mpesaCode" class="border p-2 w-full" />
    </div>

    <!-- Total & Process Sale -->
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-bold">Total: Ksh {{ totalPrice }}</h3>
      <button
        @click="completeSale"
        :disabled="cart.length === 0"
        class="bg-green-500 text-black px-4 py-2 disabled:bg-gray-400"
      >
        Complete Sale
      </button>
    </div>

    <button @click="logout" class="mt-4 px-4 py-2 bg-red-600 text-black rounded">
      Logout
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const products = ref([])
const cart = ref([])

const paymentMethod = ref('cash')
const mpesaCode = ref('')

// Fetch products
const getProducts = async () => {
  try {
    const res = await axios.get('/api/products')
    products.value = res.data
  } catch (err) {
    console.error('Error fetching products:', err)
  }
}

// Cart logic
const addToCart = (product) => {
  const existing = cart.value.find((item) => item.id === product.id)
  if (existing) {
    if (existing.qty < product.quantity) existing.qty++
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

const removeFromCart = (id) => {
  cart.value = cart.value.filter((item) => item.id !== id)
}

const updateCart = (item) => {
  if (item.qty > item.stock) item.qty = item.stock
  if (item.qty < 1) item.qty = 1
}

const totalPrice = computed(() =>
  cart.value.reduce((sum, item) => sum + item.price * item.qty, 0)
)

// Complete Sale (From CashierSale.vue)
const completeSale = async () => {
  if (cart.value.length === 0) return alert('Cart is empty')

  try {
    await axios.post('/api/sales', {
      items: cart.value.map((item) => ({
        product_id: item.id,
        name: item.name,
        quantity: item.qty,
        price: item.price
      })),
      payment_method: paymentMethod.value,
      mpesa_code: mpesaCode.value
    })

    alert('Sale completed successfully!')
    cart.value = []
    paymentMethod.value = 'cash'
    mpesaCode.value = ''
    getProducts()
  } catch (error) {
    console.error(error)
    alert('Failed to complete sale')
  }
}

onMounted(() => {
  getProducts()
})

const logout = async () => {
  try {
    await axios.post('/api/logout')
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>
