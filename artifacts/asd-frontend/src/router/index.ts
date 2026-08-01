import { createRouter, createWebHistory } from 'vue-router'

declare module 'vue-router' {
  interface RouteMeta {
    layout?: 'default' | 'auth' | 'blank'
    requiresAuth?: boolean
    title?: string
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0, behavior: 'smooth' }
  },
  routes: [
    // ── Placeholder — no business pages yet ──────────────────
    {
      path: '/',
      name: 'home',
      component: () => import('@/App.vue'),
      meta: { layout: 'default', title: 'ASD' },
    },
    // ── Catch-all 404 ─────────────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      redirect: '/',
    },
  ],
})

// Document title guard
router.afterEach((to) => {
  const appName = 'ASD'
  document.title = to.meta.title ? `${to.meta.title} · ${appName}` : appName
})

export default router
