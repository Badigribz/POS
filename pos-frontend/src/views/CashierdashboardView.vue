<template>
  <v-app>
    <!-- App Bar -->
    <v-app-bar app color="blue" dense dark>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>GRIBZ SHOP</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-toolbar-items v-show="!isLoggedIn">
        <v-btn text @click="logout">Logout</v-btn>
      </v-toolbar-items>
      <CartIcon v-show="!isLoggedIn" />
    </v-app-bar>

    <!-- Navigation Drawer -->
    <v-navigation-drawer app v-model="drawer" temporary>
      <v-list>
        <v-list-item link :to="{ path: '/login' }">
          <v-list-item-title>Login</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main>
      <v-container>
        <v-card elevation="6" class="pa-4 mt-4">
          <v-card-title class="text-h5 text-center font-weight-bold">Cashier Dashboard</v-card-title>

          <v-divider class="mb-4"></v-divider>

          <!-- Products Table -->
          <v-card-subtitle class="mb-2 text-center">Available Products</v-card-subtitle>
          <v-data-table
            :headers="productHeaders"
            :items="products"
            class="mb-6"
            dense
            :items-per-page="5"
          >
            <template v-slot:[`item.action`]="{ item }">
              <v-btn
                color="blue"
                density="comfortable"
                :disabled="item.quantity === 0"
                @click="addToCart(item)"
              >
                Add to Cart
              </v-btn>
            </template>
          </v-data-table>

          <!-- Cart Section -->
          <v-card-subtitle class="mb-2 text-center">Cart</v-card-subtitle>
          <v-data-table
            :headers="cartHeaders"
            :items="cart"
            class="mb-4"
            dense
          >
            <template v-slot:[`item.qty`]="{ item }">
              <v-text-field
                v-model.number="item.qty"
                type="number"
                min="1"
                :max="item.stock"
                density="compact"
                style="width: 70px"
                @change="updateCart(item)"
              />
            </template>
            <template v-slot:[`item.total`]="{ item }">
              Ksh {{ (item.price * item.qty).toFixed(2) }}
            </template>
            <template v-slot:[`item.action`]="{ item }">
              <v-btn color="red" density="comfortable" @click="removeFromCart(item.id)">
                Remove
              </v-btn>
            </template>
          </v-data-table>

          <!-- Payment Method -->
          <v-select
            v-model="paymentMethod"
            :items="['cash', 'mpesa', 'card']"
            label="Payment Method"
            outlined
            dense
            class="mb-4"
          />

          <!-- MPESA Code -->
          <v-text-field

            v-if="paymentMethod === 'mpesa'"
            v-model="mpesaPhone"
            label="Customer M-Pesa Phone"
            outlined
            dense
            class="mb-4"
          />


          <!-- Total & Complete Sale -->
          <div class="d-flex justify-space-between align-center">
            <h3 class="text-lg font-weight-bold">Total: Ksh {{ totalPrice.toFixed(2) }}</h3>
            <v-btn
              color="green"
              :disabled="cart.length === 0"
              @click="completeSale"
            >
              Complete Sale
            </v-btn>
          </div>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const router = useRouter()
const products = ref([])
const cart = ref([])
const toast = useToast()

const paymentMethod = ref('cash')
const mpesaPhone = ref('')
const drawer = ref(false)
const isLoggedIn = false // Adjust this as needed

const productHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Price', key: 'price' },
  { title: 'Stock', key: 'quantity' },
  { title: 'Action', key: 'action', sortable: false }
]

const cartHeaders = [
  { title: 'Name', key: 'name' },
  { title: 'Price', key: 'price' },
  { title: 'Qty', key: 'qty' },
  { title: 'Total', key: 'total' },
  { title: 'Action', key: 'action', sortable: false }
]

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
    toast.success('Product added to cart')
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

const completeSale = async () => {
  if (cart.value.length === 0) return alert('Cart is empty')

  try {
    if (paymentMethod.value === 'mpesa') {
      const stkRes = await axios.post('/api/mpesa/stkpush', {
        phone: mpesaPhone.value,
        amount: totalPrice.value,
      })

      console.log(stkRes.data)
      toast.success('STK push sent to customer. Waiting for confirmation...')
    }

    await axios.post('/api/sales', {
      items: cart.value.map((item) => ({
        product_id: item.id,
        name: item.name,
        quantity: item.qty,
        price: item.price
      })),
      payment_method: paymentMethod.value
    })
    toast.success('Sale completed')
    cart.value = []
    paymentMethod.value = 'cash'
    mpesaPhone.value = ''
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
