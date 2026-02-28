<script setup lang="ts">
/**
 * Landing page (/) — matches freedomstack-landing-page.html
 * Hero, Ticker, Problem, How It Works, Demo Calculator, Features, Comparison, Final CTA, Footer.
 * Public, blank layout, dark theme.
 */
import Footer from '@/views/front-pages/front-page-footer.vue'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import ComparisonSection from '@/views/front-pages/landing-page/ComparisonSection.vue'
import DemoCalculator from '@/views/front-pages/landing-page/DemoCalculator.vue'
import FeaturesSection from '@/views/front-pages/landing-page/FeaturesSection.vue'
import FinalCtaSection from '@/views/front-pages/landing-page/FinalCtaSection.vue'
import HeroSection from '@/views/front-pages/landing-page/hero-section.vue'
import HowItWorks from '@/views/front-pages/landing-page/HowItWorks.vue'
import ProblemSection from '@/views/front-pages/landing-page/ProblemSection.vue'
import TickerSection from '@/views/front-pages/landing-page/TickerSection.vue'
import { useConfigStore } from '@core/stores/config'

const store = useConfigStore()
store.skin = 'default'
store.theme = 'dark'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const activeSectionId = ref<string | undefined>()
const landingRef = ref<HTMLElement | null>(null)

// Section refs for nav active state
const refHome = ref()
const refHow = ref()
const refCalculator = ref()

useIntersectionObserver(
  [refHome, refHow, refCalculator],
  (entries: IntersectionObserverEntry[]) => {
    const visible = entries.find(e => e.isIntersecting && (e.target as HTMLElement)?.id)
    if (visible) activeSectionId.value = (visible.target as HTMLElement).id
  },
  { threshold: 0.25 },
)

// Scroll reveal: add .visible to .reveal elements when they enter view
onMounted(() => {
  const el = landingRef.value
  if (!el) return
  const reveals = el.querySelectorAll('.reveal')
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) entry.target.classList.add('visible')
      })
    },
    { threshold: 0.15 },
  )
  reveals.forEach((node: Element) => observer.observe(node))
})
</script>

<template>
  <div
    ref="landingRef"
    class="landing-page-wrapper"
  >
    <Navbar :active-id="activeSectionId" />
    <HeroSection ref="refHome" />
    <TickerSection />
    <ProblemSection />
    <HowItWorks ref="refHow" />
    <DemoCalculator ref="refCalculator" />
    <FeaturesSection />
    <ComparisonSection />
    <FinalCtaSection />
    <Footer />
  </div>
</template>

<style lang="scss" scoped>
/* Base palette — matches freedomstack-landing-page.html */
.landing-page-wrapper {
  --fs-black: #0a0a0a;
  --fs-white: #fafaf9;
  --fs-cream: #f5f0e8;
  --fs-green: #22c55e;
  --fs-green-glow: rgba(34, 197, 94, 0.12);
  --fs-gray-400: #a1a1aa;
  --fs-gray-600: #52525b;
  --fs-gray-800: #27272a;

  background: var(--fs-black);
  color: var(--fs-white);
  min-height: 100vh;
}
</style>

<style lang="scss">
/* Atmospheric effects — cards and hierarchy (unscoped so they apply to Vuetify components) */
.landing-page-wrapper {
  .v-card {
    background: rgba(255, 255, 255, 0.02) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    transition: border-color 0.3s, background 0.3s, transform 0.3s;

    &:hover {
      border-color: rgba(34, 197, 94, 0.2) !important;
      background: rgba(255, 255, 255, 0.03) !important;
    }
  }

  /* Secondary text */
  .text-medium-emphasis,
  .text-disabled {
    color: var(--fs-gray-400) !important;
  }

  /* Tertiary / fine print */
  .landing-tertiary {
    color: var(--fs-gray-600);
  }
}
</style>
