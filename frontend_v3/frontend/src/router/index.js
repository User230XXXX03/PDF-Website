import { createRouter, createWebHistory } from 'vue-router'
import Layout from '../layout/Index.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue')
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/Register.vue')
  },
  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue')
      },
      {
        path: 'templates',
        name: 'Templates',
        component: () => import('../views/Templates.vue')
      },
      {
        path: 'templates/create',
        name: 'TemplateCreate',
        component: () => import('../views/TemplateEditor.vue')
      },
      {
        path: 'templates/edit/:id',
        name: 'TemplateEdit',
        component: () => import('../views/TemplateEditor.vue')
      },
      {
        path: 'batches',
        name: 'Batches',
        component: () => import('../views/Batches.vue')
      },
      {
        path: 'batches/create',
        name: 'BatchCreate',
        component: () => import('../views/BatchEditor.vue')
      },
      {
        path: 'batches/edit/:id',
        name: 'BatchEdit',
        component: () => import('../views/BatchEditor.vue')
      },
      {
        path: 'batches/detail/:id',
        name: 'BatchDetail',
        component: () => import('../views/BatchDetail.vue')
      },
      {
        path: 'logs',
        name: 'Logs',
        component: () => import('../views/Logs.vue')
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('../views/Settings.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Auth guard
router.beforeEach((to, from, next) => {
  const token = sessionStorage.getItem('user')
  
  if (to.path === '/login' || to.path === '/register') {
    next()
  } else if (!token) {
    next('/login')
  } else {
    next()
  }
})

export default router
