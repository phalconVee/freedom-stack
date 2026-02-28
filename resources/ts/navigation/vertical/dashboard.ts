/**
 * FreedomStack sidebar navigation — matches FREEDOMSTACK_USER_JOURNEY.md Section 5 exactly.
 * Only journey-defined items; no template demo modules.
 */
import type { VerticalNavItems } from '@layouts/types'

export default [
  {
    title: 'Dashboard',
    icon: { icon: 'tabler-smart-home' },
    to: '/dashboard',
  },
  { heading: 'PLAN' },
  {
    title: 'Calculator',
    icon: { icon: 'tabler-calculator' },
    to: '/calculator',
  },
  {
    title: 'My Finances',
    icon: { icon: 'tabler-wallet' },
    children: [
      { title: 'Profile', to: '/finances/profile' },
      { title: 'Expenses', to: '/finances/expenses' },
      { title: 'Debts', to: '/finances/debts' },
      { title: 'Investments', to: '/finances/investments' },
    ],
  },
  {
    title: 'Action Plan',
    icon: { icon: 'tabler-robot' },
    to: '/action-plan',
  },
  {
    title: 'Scenarios',
    icon: { icon: 'tabler-bolt' },
    to: '/scenarios',
    badgeContent: 'Coming Soon',
    badgeClass: 'bg-secondary',
  },
  { heading: 'TRACK' },
  {
    title: 'Progress',
    icon: { icon: 'tabler-chart-line' },
    to: '/progress',
  },
  { heading: 'LEARN' },
  {
    title: 'Resources',
    icon: { icon: 'tabler-book' },
    to: '/resources',
    badgeContent: 'Coming Soon',
    badgeClass: 'bg-secondary',
  },
  { heading: ' ' },
  {
    title: 'Settings',
    icon: { icon: 'tabler-settings' },
    to: '/settings',
  },
] as VerticalNavItems
