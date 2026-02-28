/**
 * Dashboard badges for sidebar nav (FREEDOMSTACK_DASHBOARD_INSTRUCTIONS.md §6).
 * Populated when dashboard page loads; layout merges into nav items.
 */
import type { VerticalNavItems } from '@layouts/types'

export interface DashboardBadges {
  action_plan_count: number
  progress_needs_update: boolean
  profile_incomplete: boolean
}

const defaultBadges: DashboardBadges = {
  action_plan_count: 0,
  progress_needs_update: false,
  profile_incomplete: false,
}

export const useDashboardBadgesStore = defineStore('dashboardBadges', () => {
  const badges = ref<DashboardBadges>({ ...defaultBadges })

  function setBadges(payload: Partial<DashboardBadges>) {
    badges.value = { ...defaultBadges, ...payload }
  }

  return { badges, setBadges }
})

function mergeBadgesIntoNav(items: VerticalNavItems, badges: DashboardBadges): VerticalNavItems {
  return items.map(item => {
    if ('heading' in item) return item
    const base = { ...item }
    if (item.title === 'Action Plan') {
      if (badges.action_plan_count > 0) {
        base.badgeContent = String(badges.action_plan_count)
        base.badgeClass = 'bg-success'
      } else {
        base.badgeContent = undefined
        base.badgeClass = undefined
      }
    } else if (item.title === 'Progress') {
      if (badges.progress_needs_update) {
        base.badgeContent = 'Update'
        base.badgeClass = 'bg-warning'
      } else {
        base.badgeContent = undefined
        base.badgeClass = undefined
      }
    } else if (item.title === 'My Finances') {
      if (badges.profile_incomplete) {
        base.badgeContent = '•'
        base.badgeClass = 'bg-warning'
      } else {
        base.badgeContent = undefined
        base.badgeClass = undefined
      }
    }
    if ('children' in base && Array.isArray(base.children))
      base.children = mergeBadgesIntoNav(base.children as VerticalNavItems, badges)
    return base
  }) as VerticalNavItems
}

export function useNavItemsWithBadges(staticNavItems: VerticalNavItems) {
  const store = useDashboardBadgesStore()
  return computed(() => mergeBadgesIntoNav(staticNavItems, store.badges))
}
