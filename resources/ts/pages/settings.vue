<script setup lang="ts">
/**
 * Settings. FREEDOMSTACK_USER_JOURNEY.md §6.11
 * URL: /settings. Account, preferences, subscription (Phase 2), data & privacy.
 */
import AccountSettingsAccount from '@/views/pages/account-settings/AccountSettingsAccount.vue'

definePage({
  meta: {
    layout: 'dashboard-shell',
  },
})

const activeTab = ref('account')
const tabs = [
  { title: 'Account', icon: 'tabler-users', value: 'account' },
  { title: 'Preferences', icon: 'tabler-adjustments', value: 'preferences' },
  { title: 'Data & Privacy', icon: 'tabler-shield-lock', value: 'privacy' },
]
</script>

<template>
  <div>
    <h1 class="text-h3 mb-4">
      Settings
    </h1>
    <p class="text-body-1 text-medium-emphasis mb-6">
      Account, preferences, and data. Notifications and subscription (Phase 2) coming later.
    </p>
    <VTabs v-model="activeTab" class="v-tabs-pill">
      <VTab
        v-for="item in tabs"
        :key="item.value"
        :value="item.value"
      >
        <VIcon size="20" start :icon="item.icon" />
        {{ item.title }}
      </VTab>
    </VTabs>
    <VWindow v-model="activeTab" class="mt-6 disable-tab-transition" :touch="false">
      <VWindowItem value="account">
        <AccountSettingsAccount />
      </VWindowItem>
      <VWindowItem value="preferences">
        <VCard>
          <VCardText>
            <p class="text-body-2 text-medium-emphasis">
              Default withdrawal rate (4%, 5%, 6%), inflation assumption, currency, dark mode. (To be wired.)
            </p>
          </VCardText>
        </VCard>
      </VWindowItem>
      <VWindowItem value="privacy">
        <VCard>
          <VCardText>
            <p class="text-body-2 text-medium-emphasis mb-2">
              Export all data (JSON/CSV). We never sell your data.
              <RouterLink to="/privacy" class="text-primary text-decoration-none">Privacy policy</RouterLink>
            </p>
          </VCardText>
        </VCard>
      </VWindowItem>
    </VWindow>
  </div>
</template>
