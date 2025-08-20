import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import DashboardView from '@/views/DashboardView.vue'
import AdmindashboardView from '@/views/AdmindashboardView.vue'
import CashierdashboardView from '@/views/CashierdashboardView.vue'
import CashiersaleView from '@/views/CashiersaleView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', component: RegisterView },
    { path: '/Login', component: LoginView },
    { path: '/dashboard', component: DashboardView, meta: { requiresAuth: true } },
    { path: '/admindashboard', component: AdmindashboardView, meta: { requiresAuth: true } },
    { path: '/cashierdashboard', component: CashierdashboardView, meta: { requiresAuth: true } },
    { path: '/cashiersale', component: CashiersaleView,}
  ],
});

// 🛡 GLOBAL ROUTE GUARD
router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('token');
  const isLoggedIn = !!token;

  // 🔐 Block unauthenticated users from protected routes
  if (to.meta.requiresAuth && !isLoggedIn) {
    return next('/Login');
  }

  // If authenticated, fetch user role (if needed)
  let userRole = localStorage.getItem('role');
  if (isLoggedIn && !userRole) {
    try {
      const res = await fetch('http://localhost:8000/api/user', {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });
      const data = await res.json();
      userRole = data.role;
      localStorage.setItem('role', userRole); // ✅ Cache for future refreshes
    } catch (err) {
      console.error('Error fetching user:', err);
      localStorage.removeItem('token');
      return next('/Login');
    }
  }

  // ⛔ Prevent logged-in users from accessing login/register
  if ((to.path === '/Login' || to.path === '/') && isLoggedIn) {
    if (userRole === 'admin') return next('/admindashboard');
    if (userRole === 'cashier') return next('/cashierdashboard');
    return next('/dashboard');
  }

  // ✅ Role-based route access control
  const roleRouteMap = {
    '/admindashboard': 'admin',
    '/cashierdashboard': 'cashier',
    '/dashboard': 'user', // general fallback, optional
  };

  const requiredRole = roleRouteMap[to.path];
  if (requiredRole && userRole !== requiredRole) {
    // 🚫 If role mismatch, redirect to their correct dashboard
    if (userRole === 'admin') return next('/admindashboard');
    if (userRole === 'cashier') return next('/cashierdashboard');
    return next('/dashboard');
  }

  // ✅ Proceed
  next();
});

export default router;
