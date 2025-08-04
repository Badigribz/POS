import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import DashboardView from '@/views/Dashboardview.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', component: RegisterView, },
    { path: '/Login', component: LoginView, },
    { path: '/dashboard', component: DashboardView , meta: { requiresAuth: true }},
  ],
})

// 🛡 GLOBAL ROUTE GUARD
  router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem('token'); // Check if token exists

  // 🔐 Block unauthenticated access to protected routes
  if (to.meta.requiresAuth && !isLoggedIn) {
    return next('/Login');
  }

  // ⛔ Prevent authenticated users from going to login/register
  if ((to.path === '/Login' || to.path === '/') && isLoggedIn) {
    return next('/dashboard');
  }

  // ✅ Otherwise continue
  next();
});

export default router
