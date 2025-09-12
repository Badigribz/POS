<template>
  <div class="p-4">
  <v-app>
    <v-app-bar app color="blue" dense>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>GRIBZ SHOP</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-toolbar-items class="hidden-sm-and-down" v-show="!isLoggedIn">
          <v-btn text :to="{ path: '/adminsalesreport' }">Sales Report</v-btn>
          <v-btn text color="white" @click="logout">Logout </v-btn>
      </v-toolbar-items>
      <CartIcon v-show="!isLoggedIn"/>
    </v-app-bar>

   <v-navigation-drawer app v-model="drawer" temporary>
    <v-list>
      <v-list-item link :to="{ path: '/login' }"><v-list-item-title>Login</v-list-item-title></v-list-item>
      <!-- <v-list-item link :to="{ path:'/signup' }"><v-list-item-title>Signup</v-list-item-title></v-list-item> -->
    </v-list>
   </v-navigation-drawer>

  <v-main>
  <v-container class="mt-5">
    <v-sheet class="mx-auto pa-6" max-width="900" elevation="12">
      <h1 class="text-2xl text-center font-bold mb-4">Admin Dashboard</h1>

      <!-- Add Product Form -->
      <v-form @submit.prevent="addProduct" class="mb-6">
        <v-row dense>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="newProduct.name"
              label="Product Name"
              variant="outlined"
              dense
            />
          </v-col>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="newProduct.price"
              label="Price"
              type="number"
              variant="outlined"
              dense
            />
          </v-col>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="newProduct.quantity"
              label="Quantity"
              type="number"
              variant="outlined"
              dense
            />
          </v-col>
        </v-row>
        <v-btn type="submit" color="green" elevation="8" class="mt-2">
          Add Product
        </v-btn>
      </v-form>

      <!-- Product Table -->
     <v-data-table
  :headers="headers"
  :items="products"
  class="elevation-3"
  item-value="id"
>
  <template v-slot:[`item.name`]="{ item }">
    <v-text-field
      v-if="editId === item.id"
      v-model="editProduct.name"
      dense
      variant="outlined"
    />
    <span v-else>{{ item.name }}</span>
  </template>

  <template v-slot:[`item.price`]="{ item }">
    <v-text-field
      v-if="editId === item.id"
      v-model="editProduct.price"
      type="number"
      dense
      variant="outlined"
    />
    <span v-else>{{ item.price }}</span>
  </template>

  <template v-slot:[`item.quantity`]="{ item }">
    <v-text-field
      v-if="editId === item.id"
      v-model="editProduct.quantity"
      type="number"
      dense
      variant="outlined"
    />
    <span v-else>{{ item.quantity }}</span>
  </template>

  <template v-slot:[`item.actions`]="{ item }">
  <template v-if="editId === item.id">
    <v-btn
      color="green"
      size="small"
      class="mr-2"
      @click="updateProduct(item.id)"
    >
      Save
    </v-btn>
    <v-btn
      color="grey"
      size="small"
      @click="cancelEdit"
    >
      Cancel
    </v-btn>
  </template>
  <template v-else>
    <v-btn
      color="blue"
      size="small"
      class="mr-2"
      @click="startEdit(item)"
    >
      Edit
    </v-btn>
    <v-btn
      color="red"
      size="small"
      @click="deleteProduct(item.id)"
    >
      Delete
    </v-btn>
  </template>
</template>


</v-data-table>
    </v-sheet>
  </v-container>
  </v-main>

</v-app>
  </div>
</template>

<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'

const toast = useToast()
const router = useRouter()
const products = ref([])
const newProduct = ref({
  name: '',
  price: '',
  quantity: ''
})
const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Name', key: 'name' },
  { title: 'Price', key: 'price' },
  { title: 'Quantity', key: 'quantity' },
  { title: 'Actions', key: 'actions', sortable: false },
]
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
    toast.success('Product added')
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
