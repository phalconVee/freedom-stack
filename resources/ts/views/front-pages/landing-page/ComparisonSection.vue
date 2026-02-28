<script setup lang="ts">
/**
 * Not another budgeting app — comparison table. Matches freedomstack-landing-page.html
 */
const rows = [
  { feature: 'Shows your Freedom Number', budgeting: false, fire: true, us: true },
  { feature: 'Personalized AI action plan', budgeting: false, fire: false, us: true },
  { feature: 'Debt payoff optimization', budgeting: false, fire: 'Some', us: true },
  { feature: "Each action's timeline impact", budgeting: false, fire: false, us: true },
  { feature: 'No account linking needed', budgeting: false, fire: true, us: true },
  { feature: 'Works in 60 seconds', budgeting: false, fire: true, us: true },
  { feature: 'Tracks progress over time', budgeting: true, fire: false, us: true },
  { feature: 'No ads, no data selling', budgeting: 'Some', fire: true, us: true },
]

function cellVal(val: boolean | string): string {
  if (val === true) return '✓'
  if (val === false) return '✗'
  return val
}

function cellClass(val: boolean | string, isUs: boolean): string {
  if (val === true) return 'cell-check'
  if (val === false) return 'cell-x'
  return 'cell-some'
}
</script>

<template>
  <section class="comparison-section reveal">
    <VContainer class="comparison-container">
      <h2 class="comparison-heading">
        Not another budgeting app
      </h2>
      <VTable class="comparison-table">
        <thead>
          <tr>
            <th class="th-empty">
              &nbsp;
            </th>
            <th class="th-col">
              Budgeting Apps
            </th>
            <th class="th-col">
              FIRE Calculators
            </th>
            <th class="th-col th-freedom">
              FreedomStack
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, i) in rows"
            :key="i"
          >
            <td class="td-feature">
              {{ row.feature }}
            </td>
            <td :class="cellClass(row.budgeting, false)">
              {{ cellVal(row.budgeting) }}
            </td>
            <td :class="cellClass(row.fire, false)">
              {{ cellVal(row.fire) }}
            </td>
            <td :class="cellClass(row.us, true)">
              {{ cellVal(row.us) }}
            </td>
          </tr>
        </tbody>
      </VTable>
    </VContainer>
  </section>
</template>

<style lang="scss" scoped>
.comparison-section {
  padding-block: 6rem 4rem;
  background: var(--fs-black, #0a0a0a);
  text-align: center;
}

.comparison-container {
  max-width: 780px;
  margin-inline: auto;
}

/* Heading: serif, large, off-white — matches .not-another h2 */
.comparison-heading {
  font-family: "Instrument Serif", Georgia, serif;
  font-size: clamp(2rem, 4.5vw, 3rem);
  font-weight: normal;
  letter-spacing: -0.02em;
  color: var(--fs-white, #fafaf9);
  text-align: center;
  margin-bottom: 2.5rem;
}

.comparison-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  border-radius: 8px;
  overflow: hidden;
  background: transparent !important;
}

.comparison-table thead,
.comparison-table tbody,
.comparison-table tr,
.comparison-table th,
.comparison-table td {
  background: transparent !important;
}

.comparison-table th {
  font-family: "JetBrains Mono", ui-monospace, monospace;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: var(--fs-gray-600, #52525b);
}

.comparison-table th.th-freedom {
  color: var(--fs-green, #22c55e);
}

.comparison-table td {
  padding: 0.85rem 1.25rem;
  font-size: 0.9rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.td-feature {
  color: var(--fs-white, #fafaf9);
}

.cell-check {
  color: var(--fs-green, #22c55e);
  font-weight: bold;
  text-align: center;
}

.cell-x {
  color: var(--fs-white, #fafaf9);
  text-align: center;
}

.cell-some {
  color: var(--fs-white, #fafaf9);
  text-align: center;
}

.reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}

.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}
</style>
