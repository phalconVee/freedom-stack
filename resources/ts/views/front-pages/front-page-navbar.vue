<script setup lang="ts">
import { useWindowScroll } from '@vueuse/core'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay } from 'vuetify'

import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import { themeConfig } from '@themeConfig'

const props = defineProps({
  activeId: String,
})

const navLinks = [
  { label: 'How it works', to: { path: '/', hash: '#how' } },
  { label: 'Try it', to: { path: '/', hash: '#calculator' } },
  { label: 'Login', to: { path: '/login' } },
]

function isActive(item: { label: string; to: { hash?: string } }) {
  if (!props.activeId || !item.to.hash) return false
  return item.to.hash.slice(1) === props.activeId
}

const display = useDisplay()
const { y } = useWindowScroll()
const sidebar = ref(false)

watch(() => display, () => {
  return display.mdAndUp ? sidebar.value = false : sidebar.value
}, { deep: true })
</script>

<template>
  <!-- 👉 Navigation drawer for mobile devices  -->
  <VNavigationDrawer
    v-model="sidebar"
    width="275"
    data-allow-mismatch
    disable-resize-watcher
  >
    <PerfectScrollbar
      :options="{ wheelPropagation: false }"
      class="h-100"
    >
      <!-- Nav items -->
      <div>
        <div class="d-flex flex-column gap-y-4 pa-4">
          <RouterLink
            v-for="(item, index) in navLinks"
            :key="index"
            :to="item.to"
            class="nav-link font-weight-medium"
            :class="[isActive(item) ? 'active-link' : '']"
          >
            {{ item.label }}
          </RouterLink>
          <VBtn
            :to="{ path: '/calculator' }"
            block
            color="surface"
            variant="flat"
            class="text-high-emphasis mt-2"
          >
            Get your number +
          </VBtn>
        </div>
      </div>

      <!-- Navigation drawer close icon -->
      <VIcon
        id="navigation-drawer-close-btn"
        icon="tabler-x"
        size="20"
        @click="sidebar = !sidebar"
      />
    </PerfectScrollbar>
  </VNavigationDrawer>

  <!-- 👉 Navbar for desktop devices  -->
  <div class="front-page-navbar">
    <div class="front-page-navbar">
      <VAppBar
        color="transparent"
        :class="y > 10 ? 'app-bar-scrolled' : 'app-bar-landing elevation-0'"
        class="navbar-blur landing-header-bar"
      >
        <!-- Mobile: hamburger + logo -->
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n3 me-2 d-inline-block d-md-none"
          @click="sidebar = !sidebar"
        >
          <VIcon
            size="26"
            icon="tabler-menu-2"
            color="rgba(var(--v-theme-on-surface))"
          />
        </IconBtn>
        <RouterLink
          to="/"
          class="d-flex align-center gap-2 d-md-none me-2 landing-logo-link"
        >
          <div class="landing-logo-icon">F</div>
          <span class="landing-logo-text">{{ themeConfig.app.title }}</span>
        </RouterLink>
        <!-- Logo (left, desktop): green F icon + FreedomStack -->
        <VAppBarTitle class="d-none d-md-flex">
          <RouterLink to="/" class="landing-logo-link">
            <div class="landing-logo-icon">F</div>
            <span class="landing-logo-text">{{ themeConfig.app.title }}</span>
          </RouterLink>
        </VAppBarTitle>

        <VSpacer />

        <!-- Nav links + CTA (right-aligned) -->
        <div class="d-none d-md-flex align-center nav-right-group">
          <RouterLink
            v-for="(item, index) in navLinks"
            :key="index"
            :to="item.to"
            class="nav-link py-2 px-3"
            :class="[isActive(item) ? 'active-link' : '']"
          >
            {{ item.label }}
          </RouterLink>
          <VBtn
            :to="{ path: '/calculator' }"
            variant="flat"
            color="surface"
            class="nav-cta-btn ms-4"
          >
            Get your number +
          </VBtn>
        </div>

        <div class="d-flex align-center d-md-none">
          <NavbarThemeSwitcher />
        </div>
      </VAppBar>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.landing-header-bar {
  background: rgba(10, 10, 10, 0.8) !important;
  backdrop-filter: blur(20px);
}

.landing-header-bar.app-bar-scrolled {
  background: rgba(10, 10, 10, 0.95) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.landing-logo-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: inherit;
}

.landing-logo-icon {
  width: 28px;
  height: 28px;
  background: #22c55e;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 700;
  color: #0a0a0a;
  font-family: "DM Sans", system-ui, sans-serif;
}

.landing-logo-text {
  font-weight: 700;
  font-size: 1.15rem;
  letter-spacing: -0.02em;
  color: #fff;
  font-family: "DM Sans", system-ui, sans-serif;
}

.nav-right-group {
  gap: 0.5rem;
}

.nav-link {
  color: #a1a1aa;
  font-size: 0.9rem;
  text-decoration: none;
  transition: color 0.2s;
  font-family: "DM Sans", system-ui, sans-serif;

  &:hover {
    color: #fafaf9;
  }
}

.nav-cta-btn {
  background: #fff !important;
  color: #0a0a0a !important;
  font-weight: 600 !important;
  font-size: 0.85rem !important;
  padding: 0.5rem 1.25rem !important;
  border-radius: 8px !important;
  text-transform: none !important;
  letter-spacing: normal !important;
  font-family: "DM Sans", system-ui, sans-serif;

  &:hover {
    background: rgba(255, 255, 255, 0.95) !important;
    color: #0a0a0a !important;
  }
}

.page-link {
  &:hover {
    color: rgb(var(--v-theme-primary)) !important;
  }
}

@media (min-width: 1920px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(1440px - 32px);
    }
  }
}

@media (min-width: 1280px) and (max-width: 1919px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(1200px - 32px);
    }
  }
}

@media (min-width: 960px) and (max-width: 1279px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(900px - 32px);
    }
  }
}

@media (min-width: 600px) and (max-width: 959px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(100% - 64px);
    }
  }
}

@media (max-width: 600px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(100% - 32px);
    }
  }
}

.nav-item-img {
  border: 10px solid rgb(var(--v-theme-background));
  border-radius: 10px;
}

.active-link {
  color: #fff !important;
}

.front-page-navbar::after {
  position: fixed;
  z-index: 2;
  backdrop-filter: saturate(100%) blur(6px);
  block-size: 5rem;
  content: "";
  inline-size: 100%;
}
</style>

<style lang="scss">
@use "@layouts/styles/mixins" as layoutMixins;

.mega-menu {
  position: fixed !important;
  inset-block-start: 5.4rem;
  inset-inline-start: 50%;
  transform: translateX(-50%);

  @include layoutMixins.rtl {
    transform: translateX(50%);
  }
}

.front-page-navbar {
  .v-toolbar__content {
    padding-inline: 30px !important;
  }

  .v-toolbar {
    inset-inline: 0 !important;
    margin-block-start: 1rem !important;
    margin-inline: auto !important;
  }
}

.mega-menu-item {
  &:hover {
    color: rgb(var(--v-theme-primary)) !important;
  }
}

#navigation-drawer-close-btn {
  position: absolute;
  cursor: pointer;
  inset-block-start: 0.5rem;
  inset-inline-end: 1rem;
}
</style>
