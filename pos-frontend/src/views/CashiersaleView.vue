<template>
  <div class="p-4 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Cashier POS - Record Sale</h1>

    <!-- Product Selection -->
    <div class="mb-4">
      <label class="block mb-1 font-semibold">Select Product</label>
      <select v-model="selectedProductId" class="border p-2 w-full">
        <option disabled value="">-- Choose Product --</option>
        <option v-for="p in products" :key="p.id" :value="p.id">
          {{ p.name }} (Stock: {{ p.stock }}) - {{ p.price }} KES
        </option>
      </select>
    </div>

    <div class="mb-4">
      <label class="block mb-1 font-semibold">Quantity</label>
      <input type="number" v-model.number="quantity" class="border p-2 w-full" min="1" />
    </div>

    <button @click="addItem"
      class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
      Add Item
    </button>

    <!-- Sale Items Table -->
    <table class="w-full mt-6 border">
      <thead>
        <tr class="bg-gray-200">
          <th class="border p-2">Product</th>
          <th class="border p-2">Qty</th>
          <th class="border p-2">Price</th>
          <th class="border p-2">Total</th>
          <th class="border p-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in saleItems" :key="index">
          <td class="border p-2">{{ item.name }}</td>
          <td class="border p-2">{{ item.quantity }}</td>
          <td class="border p-2">{{ item.price }}</td>
          <td class="border p-2">{{ item.price * item.quantity }}</td>
          <td class="border p-2">
            <button @click="removeItem(index)" class="text-red-500 hover:underline">
              Remove
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Payment Method -->
    <div class="mt-4">
      <label class="block mb-1 font-semibold">Payment Method</label>
      <select v-model="paymentMethod" class="border p-2 w-full">
        <option value="cash">Cash</option>
        <option value="mpesa">M-Pesa</option>
        <option value="card">Card</option>
      </select>
    </div>

    <!-- M-Pesa Code -->
    <div v-if="paymentMethod === 'mpesa'" class="mt-2">
      <label class="block mb-1">M-Pesa Transaction Code</label>
      <input v-model="mpesaCode" class="border p-2 w-full" />
    </div>

    <!-- Complete Sale -->
    <div class="mt-6">
      <button @click="completeSale"
        class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
        Complete Sale
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '../axios'

const products = ref([])
const selectedProductId = ref('')
const quantity = ref(1)
const saleItems = ref([])
const paymentMethod = ref('cash')
const mpesaCode = ref('')

onMounted(async () => {
  const res = await axios.get('/api/products') // You'll need to create a products endpoint
  products.value = res.data
})

const addItem = () => {
  if (!selectedProductId.value) return alert('Please select a product')

  const product = products.value.find(p => p.id === selectedProductId.value)
  if (!product) return

  if (quantity.value > product.stock) {
    return alert(`Not enough stock for ${product.name}`)
  }

  saleItems.value.push({
    product_id: product.id,
    name: product.name,
    quantity: quantity.value,
    price: product.price
  })

  selectedProductId.value = ''
  quantity.value = 1
}

const removeItem = (index) => {
  saleItems.value.splice(index, 1)
}

const completeSale = async () => {
  if (saleItems.value.length === 0) {
    return alert('Add at least one item')
  }

  try {
    await axios.post('/api/sales', {
      items: saleItems.value,
      payment_method: paymentMethod.value,
      mpesa_code: mpesaCode.value
    })

    alert('Sale completed successfully!')
    saleItems.value = []
    paymentMethod.value = 'cash'
    mpesaCode.value = ''
  } catch (error) {
    console.error(error)
    alert('Failed to complete sale')
  }
}
</script>
