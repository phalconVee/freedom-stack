import type { RouteRecordRaw } from 'vue-router/auto'

/**
 * FreedomStack: route overrides only for user-journey flow.
 * No template demo routes. Root / is handled by pages/index.vue (landing);
 * guards redirect authenticated users from / to /dashboard.
 */

export const redirects: RouteRecordRaw[] = []

// 👉 Route overrides (journey URLs that point at existing components)
export const routes: RouteRecordRaw[] = [
  {
    path: '/action-plan',
    name: 'action-plan',
    component: () => import('@/pages/recommendations/index.vue'),
    meta: { layout: 'dashboard-shell' },
  },
]
