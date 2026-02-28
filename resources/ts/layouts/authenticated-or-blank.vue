<script setup lang="ts">
/**
 * Use dashboard shell when authenticated, otherwise render no shell (blank).
 * Used by Calculator so unauthenticated users get the public flow without the app chrome.
 */
const DashboardShell = defineAsyncComponent(() => import('./dashboard-shell.vue'))

const accessToken = useCookie('accessToken')
const userData = useCookie('userData')

const isAuthenticated = computed(() => !!(userData.value && accessToken.value))
</script>

<template>
  <component :is="DashboardShell" v-if="isAuthenticated" />
  <RouterView v-else v-slot="{ Component }">
    <Suspense :timeout="0">
      <Component :is="Component" />
    </Suspense>
  </RouterView>
</template>
