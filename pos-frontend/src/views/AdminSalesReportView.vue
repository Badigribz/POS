<template>
  <div>
    <v-app>
      <v-app-bar app color="blue" dense dark>
        <v-toolbar-title>GRIBZ SHOP</v-toolbar-title>
          <v-btn text @click="logout">Logout</v-btn>
      </v-app-bar>
      <v-main>
        <v-container class="mt-6">
          <v-card elevation="6" class="pa-4">
            <v-card-title class="text-h5 font-weight-bold text-center justify-center">
              Sales Report
            </v-card-title>

            <v-card-text>

              <!-- Loading State -->
              <v-alert v-if="loading" type="info" border="start" elevation="2" class="mb-4">
                Loading sales...
              </v-alert>

              <!-- Error State -->
              <v-alert v-if="error" type="error" border="start" elevation="2" class="mb-4">
                {{ error }}
              </v-alert>

              <!-- Sales Table -->
              <v-data-table
                v-if="sales.length > 0"
                :headers="headers"
                :items="sales"
                class="elevation-3"
                dense
              >
              <template v-slot:[`item.total_amount`]="{ item }">
               Ksh {{ Number(item.total_amount).toFixed(2) }}
              </template>

              <template v-slot:[`item.created_at`]="{ item }">
                {{ new Date(item.created_at).toLocaleString() }}
              </template>

              <template v-slot:[`item.cashier`]="{ item }">
                {{ item.cashier?.name || 'N/A' }}
              </template>

              </v-data-table>

              <!-- Totals -->
              <div v-if="sales.length > 0" class="mt-4 text-center font-weight-bold">
                Total Revenue: Ksh {{ totalRevenue.toFixed(2) }} <br />
                Total Sales: {{ totalSales }}
              </div>

              <!-- No Sales -->
              <v-alert v-else-if="!loading" type="info" border="start" elevation="2" class="mt-4">
                No sales recorded yet.
              </v-alert>

            </v-card-text>
          </v-card>
        </v-container>
      </v-main>
    </v-app>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "AdminSalesReport",
  data() {
    return {
      drawer: false,
      sales: [],
      totalRevenue: 0,
      totalSales: 0,
      loading: false,
      error: null,
      headers: [
        { title: '#', key: 'index', value: 'index' },
        { title: 'Cashier', key: 'cashier' },
        { title: 'Payment Method', key: 'payment_method' },
        { title: 'Total Amount', key: 'total_amount' },
        { title: 'Date', key: 'created_at' },
      ],
    };
  },
  async created() {
    this.fetchSales();
  },
  methods: {
    async fetchSales() {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.get('/api/sales');
        console.log("Sales response:", res.data);

        this.sales = res.data.sales.map((sale, index) => ({
          ...sale,
          index: index + 1,
        }));
        this.totalRevenue = res.data.total_revenue;
        this.totalSales = res.data.total_sales;
      } catch (err) {
        this.error = "Failed to fetch sales.";
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    async logout() {
      try {
        await axios.post('/api/logout');
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
        this.$router.push('/login');
      } catch (error) {
        console.error('Logout failed:', error);
      }
    },
  },
};
</script>
