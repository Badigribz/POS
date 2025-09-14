<template>
  <v-app>
    <v-app-bar app color="blue" dense dark>
      <v-toolbar-title>Items Sold</v-toolbar-title>
      <v-btn text @click="$router.back()">Back</v-btn>
    </v-app-bar>

    <v-main>
      <v-container class="mt-6">
        <v-card elevation="6" class="pa-4">
          <v-card-title class="text-h5 font-weight-bold text-center">
            Sale #{{ sale?.id }} Details
          </v-card-title>

          <v-card-text>
            <!-- Loading -->
            <v-alert v-if="loading" type="info">Loading...</v-alert>

            <!-- Error -->
            <v-alert v-if="error" type="error">{{ error }}</v-alert>

            <!-- Sale Details -->
            <div v-if="sale && !loading">
              <p><strong>Cashier:</strong> {{ sale.cashier?.name || 'N/A' }}</p>
              <p><strong>Total Amount:</strong> Ksh {{ Number(sale.total_amount).toFixed(2) }}</p>
              <p><strong>Payment Method:</strong> {{ sale.payment_method }}</p>
              <p><strong>Date:</strong> {{ new Date(sale.created_at).toLocaleString() }}</p>

              <v-data-table
                :headers="headers"
                :items="sale.items"
                class="mt-4"
                dense
                elevation="3"
              >
                <template v-slot:[`item.product.name`]="{ item }">
                  {{ item.product?.name || 'N/A' }}
                </template>
                <template v-slot:[`item.price`]="{ item }">
                  Ksh {{ Number(item.price).toFixed(2) }}
                </template>
                <template v-slot:[`item.total`]="{ item }">
                  Ksh {{ (item.price * item.quantity).toFixed(2) }}
                </template>
              </v-data-table>
            </div>
          </v-card-text>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import axios from "axios";

export default {
  name: "ItemsSoldPage",
  props: ["id"],
  data() {
    return {
      sale: null,
      loading: false,
      error: null,
      headers: [
        { title: "Product", key: "product.name" },
        { title: "Quantity", key: "quantity" },
        { title: "Price", key: "price" },
        { title: "Total", key: "total" },
      ],
    };
  },
  async created() {
    this.fetchSale();
  },
  methods: {
    async fetchSale() {
      this.loading = true;
      this.error = null;
      try {
        const res = await axios.get(`/api/sales/${this.id}/items`);
        this.sale = res.data;
      } catch (err) {
        this.error = "Failed to load sale details.";
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
