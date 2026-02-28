import type { RouteNamedMap, _RouterTyped } from 'unplugin-vue-router'
import { canNavigate } from '@layouts/plugins/casl'

export const setupGuards = (router: _RouterTyped<RouteNamedMap & { [key: string]: any }>) => {
  // 👉 router.beforeEach
  // Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
  router.beforeEach(to => {
    const isLoggedIn = !!(useCookie('userData').value && useCookie('accessToken').value)

    // FreedomStack: authenticated users at landing (/) go to dashboard
    if (to.path === '/' && isLoggedIn)
      return '/dashboard'

    /*
     * Public routes: no auth required (landing, login, register, etc.)
     */
    if (to.meta.public)
      return

    /*
      If user is logged in and is trying to access login-like page, redirect to dashboard
     */
    if (to.meta.unauthenticatedOnly) {
      if (isLoggedIn)
        return '/dashboard'
      else
        return undefined
    }

    if (!canNavigate(to) && to.matched.length) {
      /* eslint-disable indent */
      return isLoggedIn
        ? { name: 'not-authorized' }
        : {
            name: 'login',
            query: {
              ...to.query,
              to: to.fullPath !== '/' ? to.path : undefined,
            },
          }
      /* eslint-enable indent */
    }
  })
}
