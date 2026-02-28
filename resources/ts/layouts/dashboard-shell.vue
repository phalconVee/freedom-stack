<script setup lang="ts">
/**
 * FreedomStack app shell — matches freedomstack-dashboard-v2.html.
 * Standalone layout: sidebar + topbar + main. No Vuexy wrapper.
 */
import navItemsRaw from '@/navigation/vertical/dashboard'
import type { VerticalNavItems } from '@layouts/types'

const navItems = navItemsRaw as VerticalNavItems
const route = useRoute()
const router = useRouter()

function navKey(item: (typeof navItems)[number], idx: number): string {
  if ('heading' in item && item.heading) return `h-${item.heading}`
  if ('to' in item && item.to) return item.to
  if ('title' in item && item.title) return `t-${item.title}-${idx}`
  return `n-${idx}`
}

const topbarTitle = computed(() => {
  const name = route.name?.toString() ?? ''
  if (name.includes('dashboard')) return 'Dashboard'
  if (name.includes('calculator')) return 'Calculator'
  if (name.includes('profile')) return 'My Finances — Profile'
  if (name.includes('expenses')) return 'My Finances — Expenses'
  if (name.includes('debts')) return 'My Finances — Debts'
  if (name.includes('investments')) return 'My Finances — Investments'
  if (name.includes('action-plan')) return 'Action Plan'
  if (name.includes('scenarios')) return 'Scenarios'
  if (name.includes('progress')) return 'Progress'
  if (name.includes('resources')) return 'Resources'
  if (name.includes('settings')) return 'Settings'
  return 'Dashboard'
})

const financesMenuOpen = ref(route.path.startsWith('/finances'))

watch(() => route.path, (path: string) => {
  if (path.startsWith('/finances'))
    financesMenuOpen.value = true
  else
    financesMenuOpen.value = false
}, { immediate: false })

function toggleFinancesMenu() {
  financesMenuOpen.value = !financesMenuOpen.value
}

const isActive = (item: { to?: string; children?: { to: string }[] }) => {
  if (item.to) return route.path === item.to || route.path.startsWith(item.to + '/')
  if (item.children) return item.children.some(c => route.path === c.to || route.path.startsWith(c.to + '/'))
  return false
}

const userInitials = ref('')
const userEmail = ref('')
const userName = ref('Account')
const profileMenuOpen = ref(false)

async function logout() {
  try {
    await $api('/auth/logout', { method: 'POST' })
  } catch {
    // continue to clear client state
  }
  useCookie('accessToken').value = null
  useCookie('userData').value = null
  useCookie('userAbilityRules').value = null
  profileMenuOpen.value = false
  await router.push('/login')
}

onMounted(async () => {
  try {
    const res = await $api<{ userData?: { fullName?: string; email?: string } }>('/auth/me')
    const u = res?.userData
    userName.value = u?.fullName ?? 'Account'
    userEmail.value = u?.email ?? ''
    if (u?.fullName) {
      const parts = u.fullName.trim().split(/\s+/)
      userInitials.value = parts.length >= 2
        ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase().slice(0, 2)
        : u.fullName.slice(0, 2).toUpperCase()
    } else if (u?.email)
      userInitials.value = u.email.slice(0, 2).toUpperCase()
    else
      userInitials.value = 'U'
  } catch {
    userInitials.value = 'U'
    userEmail.value = ''
  }
})
</script>

<template>
  <div class="fs-app">
    <aside class="fs-sidebar">
      <RouterLink
        to="/"
        class="fs-sidebar-logo"
      >
        <div class="fs-logo-icon">F</div>
        FreedomStack
      </RouterLink>
      <nav class="fs-sidebar-nav">
        <template v-for="(item, idx) in navItems" :key="navKey(item, idx)">
          <div
            v-if="'heading' in item && item.heading"
            class="fs-nav-section-label"
          >
            {{ item.heading }}
          </div>
          <RouterLink
            v-else-if="'to' in item && item.to && !('children' in item && item.children?.length)"
            :to="item.to"
            class="fs-nav-item"
            :class="{ active: isActive(item) }"
          >
            <VIcon v-if="item.icon" :icon="item.icon.icon" size="18" />
            <span class="fs-nav-label">{{ item.title }}</span>
            <span
              v-if="'badgeContent' in item && item.badgeContent"
              class="fs-nav-badge"
              :class="('badgeClass' in item && item.badgeClass === 'bg-secondary') ? 'coming' : 'green'"
            >
              {{ item.badgeContent }}
            </span>
          </RouterLink>
          <template v-else-if="'children' in item && item.children?.length">
            <button
              type="button"
              class="fs-nav-item fs-nav-parent"
              :class="{ open: financesMenuOpen }"
              @click="toggleFinancesMenu"
            >
              <VIcon v-if="item.icon" :icon="item.icon.icon" size="18" />
              <span class="fs-nav-label">{{ item.title }}</span>
              <VIcon class="fs-nav-chevron" icon="tabler-chevron-right" size="14" />
            </button>
            <div class="fs-nav-submenu" :class="{ open: financesMenuOpen }">
              <RouterLink
                v-for="child in item.children"
                :key="child.to"
                :to="child.to"
                class="fs-nav-item"
                :class="{ active: route.path === child.to }"
              >
                {{ child.title }}
              </RouterLink>
            </div>
          </template>
        </template>
      </nav>
      <div class="fs-sidebar-footer">
        <div class="fs-sidebar-user">
          <div class="fs-user-avatar">{{ userInitials || 'U' }}</div>
          <div class="fs-user-info">
            <div class="fs-user-name">{{ userName }}</div>
            <div class="fs-user-email">{{ userEmail || '—' }}</div>
          </div>
        </div>
      </div>
    </aside>
    <header class="fs-topbar">
      <span class="fs-topbar-title">{{ topbarTitle }}</span>
      <div class="fs-topbar-right">
        <button
          type="button"
          class="fs-topbar-btn"
          aria-label="Notifications"
        >
          <VIcon icon="tabler-bell" size="20" />
          <span class="fs-notif-dot" />
        </button>
        <VMenu
        v-model="profileMenuOpen"
        location="bottom end"
        offset="8"
        :close-on-content-click="true"
      >
        <template #activator="{ props: menuProps }">
          <button
            v-bind="menuProps"
            type="button"
            class="fs-topbar-btn"
            aria-label="Profile menu"
          >
            <VAvatar
              size="32"
              color="success"
              variant="tonal"
            >
              {{ userInitials || 'U' }}
            </VAvatar>
          </button>
        </template>
        <VList
          min-width="200"
          class="fs-profile-menu"
        >
          <VListItem
            :to="'/settings'"
            class="fs-profile-menu-item"
            @click="profileMenuOpen = false"
          >
            <template #prepend>
              <VIcon icon="tabler-settings" size="20" />
            </template>
            Settings
          </VListItem>
          <VListItem
            class="fs-profile-menu-item fs-profile-menu-logout"
            @click="logout"
          >
            <template #prepend>
              <VIcon icon="tabler-logout" size="20" />
            </template>
            Log out
          </VListItem>
        </VList>
      </VMenu>
      </div>
    </header>
    <main class="fs-main">
      <RouterView v-slot="{ Component }">
        <Suspense :timeout="0">
          <Component :is="Component" />
        </Suspense>
      </RouterView>
    </main>
  </div>
</template>

<style lang="scss" scoped>
/* freedomstack-dashboard-v2.html layout + shell only */
.fs-app {
  --fs-black: #0a0a0a;
  --fs-black-card: #111111;
  --fs-white: #fafaf9;
  --fs-green: #22c55e;
  --fs-green-dim: #166534;
  --fs-green-glow: rgba(34, 197, 94, 0.12);
  --fs-gray-500: #71717a;
  --fs-gray-600: #52525b;
  --fs-sidebar-width: 260px;
  --fs-topbar-height: 60px;
  min-height: 100vh;
  background: var(--fs-black);
  color: var(--fs-white);
  font-family: 'DM Sans', system-ui, sans-serif;
}

.fs-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: var(--fs-sidebar-width);
  background: var(--fs-black-card);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  z-index: 50;
}

.fs-sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 1.25rem 1.5rem;
  font-weight: 700;
  font-size: 1.05rem;
  color: var(--fs-white);
  text-decoration: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  flex-shrink: 0;
}

.fs-logo-icon {
  width: 28px;
  height: 28px;
  background: var(--fs-green);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  color: var(--fs-black);
  font-weight: 700;
}

.fs-sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.fs-nav-section-label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.62rem;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  color: var(--fs-gray-600);
  padding: 1.25rem 1.5rem 0.5rem;
}

.fs-nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 1.5rem;
  font-size: 0.88rem;
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  cursor: pointer;
  transition: all 0.15s;
  position: relative;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  font-family: inherit;
}

.fs-nav-item:hover {
  color: var(--fs-white);
  background: rgba(255, 255, 255, 0.03);
}

.fs-nav-item.active {
  color: var(--fs-white);
  background: var(--fs-green-glow);
}

.fs-nav-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 25%;
  bottom: 25%;
  width: 3px;
  background: var(--fs-green);
  border-radius: 0 3px 3px 0;
}

.fs-nav-label { flex: 1; }

.fs-nav-badge {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.6rem;
  padding: 0.1rem 0.45rem;
  border-radius: 4px;
}

.fs-nav-badge.green { background: var(--fs-green-glow); color: var(--fs-green); }
.fs-nav-badge.coming { background: rgba(255, 255, 255, 0.05); color: var(--fs-gray-600); font-size: 0.55rem; }

.fs-nav-parent .fs-nav-chevron { transition: transform 0.2s; margin-left: auto; }
.fs-nav-parent.open .fs-nav-chevron { transform: rotate(90deg); }

.fs-nav-submenu {
  padding-left: 2.85rem;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transition: max-height 0.25s ease, opacity 0.2s ease;
}

.fs-nav-submenu.open {
  max-height: 220px;
  opacity: 1;
}
.fs-nav-submenu .fs-nav-item { padding: 0.4rem 1rem; font-size: 0.82rem; }
.fs-nav-submenu .fs-nav-item.active::before { display: none; }

.fs-sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  flex-shrink: 0;
}

.fs-sidebar-user {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.fs-user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--fs-green-dim), var(--fs-green));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--fs-white);
  flex-shrink: 0;
}

.fs-user-info { flex: 1; min-width: 0; }
.fs-user-name { font-size: 0.82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fs-user-email { font-size: 0.7rem; color: var(--fs-gray-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.fs-topbar {
  background: rgba(10, 10, 10, 0.8);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  height: var(--fs-topbar-height);
  position: sticky;
  top: 0;
  z-index: 40;
  margin-left: var(--fs-sidebar-width);
}

.fs-topbar-title { font-size: 0.95rem; font-weight: 600; }

.fs-topbar-right { display: flex; align-items: center; gap: 1rem; }

.fs-topbar-btn {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.65);
  cursor: pointer;
  padding: 0.4rem;
  border-radius: 8px;
  transition: all 0.2s;
  position: relative;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fs-topbar-btn:hover { color: var(--fs-white); background: rgba(255, 255, 255, 0.05); }

.fs-notif-dot {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 7px;
  height: 7px;
  background: var(--fs-green);
  border-radius: 50%;
  border: 2px solid var(--fs-black);
}

.fs-main {
  margin-left: var(--fs-sidebar-width);
  padding: 2rem;
  min-height: calc(100vh - var(--fs-topbar-height));
}

/* VCard and other surface-based components in main: blend with shell (no default surface background) */
.fs-main :deep(.v-card) {
  background: var(--fs-black-card);
  border: 1px solid rgba(255, 255, 255, 0.06);
  color: var(--fs-white);
}

.fs-main :deep(.v-card-title),
.fs-main :deep(.v-card-text) {
  color: inherit;
}

/* Profile dropdown — dark theme to match shell */
:deep(.fs-profile-menu) {
  background: var(--fs-black-card);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
}

:deep(.fs-profile-menu-item) {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.875rem;
}

:deep(.fs-profile-menu-item:hover),
:deep(.fs-profile-menu-item:focus-visible) {
  background: rgba(255, 255, 255, 0.05);
}

:deep(.fs-profile-menu-logout) {
  color: rgba(255, 255, 255, 0.75);
}
</style>
