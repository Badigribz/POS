<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Sales Report</h1>

    <!-- Loading State -->
    <div v-if="loading" class="text-gray-600">Loading sales...</div>

    <!-- Error State -->
    <div v-if="error" class="text-red-500">{{ error }}</div>

    <!-- Sales Table -->
    <div v-if="sales.length > 0" class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="py-2 px-4 border">#</th>
            <th class="py-2 px-4 border">Cashier</th>
            <th class="py-2 px-4 border">Payment Method</th>
            <th class="py-2 px-4 border">Total Amount</th>
            <th class="py-2 px-4 border">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(sale, index) in sales" :key="sale.id">
            <td class="py-2 px-4 border">{{ index + 1 }}</td>
            <td class="py-2 px-4 border">{{ sale.cashier?.name || 'N/A' }}</td>
            <td class="py-2 px-4 border">{{ sale.payment_method }}</td>
            <td class="py-2 px-4 border">Ksh {{ Number(sale.total_amount).toFixed(2) }}</td>
            <td class="py-2 px-4 border">{{ new Date(sale.created_at).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Totals -->
      <div class="mt-4 text-right font-bold">
        Total Revenue: Ksh {{ totalRevenue.toFixed(2) }} <br>
        Total Sales: {{ totalSales }}
      </div>
    </div>

    <!-- No Sales -->
    <div v-else-if="!loading" class="text-gray-600">No sales recorded yet.</div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "AdminSalesReport",
  data() {
    return {
      sales: [],
      totalRevenue: 0,
      totalSales: 0,
      loading: false,
      error: null,
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
        const res = await axios.get('/api/sales'); // or full http://127.0.0.1:8000/api/sales if needed
        console.log("Sales response:", res.data);

        this.sales = res.data.sales;
        this.totalRevenue = res.data.total_revenue;
        this.totalSales = res.data.total_sales;
      } catch (err) {
        this.error = "Failed to fetch sales.";
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
