<script setup lang="ts">
const shortcuts = [
  { icon: 'tabler-smart-home', title: 'Dashboard', subtitle: 'Home', to: '/dashboard' },
  { icon: 'tabler-calculator', title: 'Calculator', subtitle: 'Freedom Number', to: '/calculator' },
  { icon: 'tabler-robot', title: 'Action Plan', subtitle: 'Recommendations', to: '/action-plan' },
  { icon: 'tabler-chart-line', title: 'Progress', subtitle: 'Monthly tracking', to: '/progress' },
  { icon: 'tabler-settings', title: 'Settings', subtitle: 'Account', to: '/settings' },
]
</script>

<template>
  <Shortcuts :shortcuts="shortcuts" />
</template>
