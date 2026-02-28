<script setup lang="ts">
/**
 * Scrolling stats ticker — matches freedomstack-landing-page.html
 */
const items = [
  { label: 'avg monthly expenses', num: '$3,400' },
  { label: 'freedom number at 4%', num: '$1,020,000' },
  { label: 'avg student debt', num: '$37,338' },
  { label: 'median savings (25-34)', num: '$10,500' },
  { label: 'time saved with a plan', num: '12+ years' },
  { label: 'employer match left on table', num: '$1,336/yr' },
]
// Duplicate for seamless loop
const tickerItems = [...items, ...items]
</script>

<template>
  <div class="ticker-section">
    <div class="ticker-track">
      <span
        v-for="(item, index) in tickerItems"
        :key="index"
        class="ticker-item"
      >
        {{ item.label }} <span class="ticker-num">{{ item.num }}</span>
      </span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.ticker-section {
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding: 1.25rem 0;
  overflow: hidden;
}

.ticker-track {
  display: flex;
  gap: 3rem;
  animation: scroll 30s linear infinite;
  width: max-content;
}

.ticker-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  white-space: nowrap;
  color: var(--fs-gray-600, #52525b);
  font-size: 0.85rem;
  font-family: "JetBrains Mono", ui-monospace, monospace;
}

.ticker-num {
  color: var(--fs-green, #22c55e);
  font-weight: 500;
}

@keyframes scroll {
  from { transform: translateX(0); }
  to { transform: translateX(-50%); }
}
</style>
