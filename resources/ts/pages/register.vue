<script setup lang="ts">
/**
 * Create Account — split layout per freedomstack-auth-screens-v2.html
 * Left: Freedom Number callout, headline, feature pills. Right: form with strength meter.
 */
import { serialize } from 'cookie-es'
import { themeConfig } from '@themeConfig'

const COOKIE_OPTS = { path: '/', maxAge: 60 * 60 * 24 * 30 }

function setAuthCookiesSync(accessToken: string, userData: unknown, userAbilityRules: unknown) {
  document.cookie = serialize('accessToken', accessToken, COOKIE_OPTS)
  const enc = (v: unknown) => encodeURIComponent(typeof v === 'string' ? v : JSON.stringify(v))
  document.cookie = serialize('userData', enc(userData), COOKIE_OPTS)
  document.cookie = serialize('userAbilityRules', enc(userAbilityRules), COOKIE_OPTS)
}

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const route = useRoute()
const router = useRouter()
const ability = useAbility()

// Freedom Number callout: only when user came from calculator (sessionStorage)
const freedomNumber = computed(() => {
  if (typeof sessionStorage === 'undefined') return null
  const v = sessionStorage.getItem('fs_freedom_number')
  if (!v) return null
  const n = parseInt(v, 10)
  return Number.isFinite(n) ? n : null
})
const freedomFormatted = computed(() =>
  freedomNumber.value != null
    ? '$' + freedomNumber.value.toLocaleString('en-US')
    : null,
)

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  privacyPolicies: false,
})

const errors = ref<Record<string, string | undefined>>({
  first_name: undefined,
  last_name: undefined,
  email: undefined,
  password: undefined,
  password_confirmation: undefined,
})

const isPasswordVisible = ref(false)
const isSubmitting = ref(false)
const submitError = ref<string | null>(null)

// Password strength: 0–4 bars → weak / medium / strong
const passwordStrength = computed(() => {
  const p = form.value.password
  if (!p.length) return { level: 0, label: '', strength: '' as const }
  let level = 0
  if (p.length >= 1) level++
  if (p.length >= 8) level++
  if (/[A-Z]/.test(p) && /[0-9]/.test(p)) level++
  if (/[^A-Za-z0-9]/.test(p) && p.length >= 10) level++
  const labels = ['', 'Weak', 'Getting there', 'Almost', 'Strong']
  const strengths: ('' | 'weak' | 'medium' | 'strong')[] = ['', 'weak', 'medium', 'medium', 'strong']
  return {
    level,
    label: labels[level],
    strength: strengths[level] ?? '',
  }
})

const register = async () => {
  if (!form.value.privacyPolicies) {
    errors.value = { ...errors.value, privacyPolicies: 'You must agree to the terms below.' }
    return
  }
  submitError.value = null
  try {
    isSubmitting.value = true
    errors.value = {}
    const res = await $api<{ accessToken: string; userData: unknown; userAbilityRules: unknown }>('/auth/register', {
      method: 'POST',
      body: {
        first_name: form.value.first_name,
        last_name: form.value.last_name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
      },
      onResponseError({ response }: { response: { _data?: { errors?: Record<string, string | string[]>; message?: string } } }) {
        const data = response._data
        if (data?.errors) {
          const normalized: Record<string, string | undefined> = {}
          for (const [key, val] of Object.entries(data.errors))
            normalized[key] = Array.isArray(val) ? val[0] : val
          errors.value = { ...errors.value, ...normalized }
        }
        if (data?.message)
          submitError.value = data.message
      },
    })
    const { accessToken, userData, userAbilityRules } = res
    ability.update(userAbilityRules)
    useCookie('userAbilityRules').value = userAbilityRules
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken
    setAuthCookiesSync(accessToken, userData, userAbilityRules)

    // Persist stored calculator result to freedom_calculations, then clear sessionStorage (non-blocking)
    const storedFreedom = sessionStorage.getItem('fs_freedom_number')
    const storedMonthly = sessionStorage.getItem('fs_monthly_expenses')
    const storedRate = sessionStorage.getItem('fs_withdrawal_rate')
    if (storedFreedom && storedMonthly && storedRate) {
      try {
        const monthlyCents = Math.round(parseFloat(storedMonthly) * 100)
        const ratePercent = Math.round(parseFloat(storedRate) * 100)
        await $api('/calculator/freedom-number/save', {
          method: 'POST',
          body: {
            monthly_expenses: monthlyCents,
            withdrawal_rate: ratePercent,
          },
        })
      } catch (_) {
        // non-blocking; user is registered
      }
      try {
        const keysToRemove: string[] = []
        for (let i = 0; i < sessionStorage.length; i++) {
          const key = sessionStorage.key(i)
          if (key?.startsWith('fs_')) keysToRemove.push(key)
        }
        keysToRemove.forEach(k => sessionStorage.removeItem(k))
      } catch (_) {
        // ignore
      }
    }

    // Client-side navigation: cookies are already in document.cookie so the guard will see isLoggedIn
    const target = route.query.to ? String(route.query.to) : '/onboarding'
    await router.replace(target)
  }
  catch (err: unknown) {
    console.error(err)
    submitError.value = err && typeof err === 'object' && 'data' in err && typeof (err as { data?: { message?: string } }).data?.message === 'string'
      ? (err as { data: { message: string } }).data.message
      : 'Registration failed. Please try again or check your connection.'
  }
  finally {
    isSubmitting.value = false
  }
}

const onSubmit = () => {
  errors.value = {}
  if (!form.value.first_name?.trim()) errors.value.first_name = 'First name is required'
  if (!form.value.last_name?.trim()) errors.value.last_name = 'Last name is required'
  if (!form.value.email?.trim()) errors.value.email = 'Email is required'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) errors.value.email = 'Enter a valid email'
  if (!form.value.password) errors.value.password = 'Password is required'
  else if (form.value.password.length < 8) errors.value.password = 'Password must be at least 8 characters'
  if (form.value.password !== form.value.password_confirmation) errors.value.password_confirmation = 'Passwords do not match'
  if (!form.value.privacyPolicies) errors.value.privacyPolicies = 'You must agree to the terms below.'
  if (Object.keys(errors.value).some(k => errors.value[k])) return
  register()
}

const featurePills = [
  { icon: 'tabler-file-check', strong: 'Personalized action plan', rest: 'built from your debts, income, and goals' },
  { icon: 'tabler-clock', strong: 'Timeline impact', rest: 'see how each move shaves years off' },
  { icon: 'tabler-chart-line', strong: 'Progress tracking', rest: '60-second monthly check-ins' },
  { icon: 'tabler-lock', strong: 'Private by default', rest: 'no account linking, no data selling, no ads' },
]
</script>

<template>
  <div class="auth-split-page">
    <div class="auth-layout">
      <!-- Left Panel -->
      <div class="auth-left">
        <RouterLink to="/" class="auth-logo">
          <div class="logo-icon">F</div>
          {{ themeConfig.app.title }}
        </RouterLink>

        <div class="auth-left-content">
          <div v-if="freedomFormatted" class="saved-number-callout">
            <div class="callout-label">Your Freedom Number</div>
            <div class="callout-number">{{ freedomFormatted }}</div>
            <div class="callout-note">We saved this. Create an account to keep it and unlock your action plan.</div>
          </div>

          <h1 class="auth-left-headline">
            The number is the<br>starting line.<br>The <em>plan</em> is the race.
          </h1>

          <p class="auth-left-body">
            You know your Freedom Number. Now let's figure out how to get there — with a strategy built from your actual financial situation.
          </p>

          <div class="feature-pills">
            <div
              v-for="(pill, i) in featurePills"
              :key="i"
              class="pill"
            >
              <span class="pill-icon-wrap">
                <VIcon :icon="pill.icon" class="pill-icon" size="22" />
              </span>
              <span class="pill-text"><strong>{{ pill.strong }}</strong> — {{ pill.rest }}</span>
            </div>
          </div>
        </div>

        <div class="auth-left-footer">
          Not financial advice. FreedomStack provides information for educational purposes only.
        </div>
      </div>

      <!-- Right Panel: Form -->
      <div class="auth-right">
        <div class="auth-form-container">
          <!-- Mobile-only: compact freedom number -->
          <div v-if="freedomFormatted" class="mobile-callout">
            <div>
              <div class="mc-label">Your Freedom Number</div>
              <div class="mc-number">{{ freedomFormatted }}</div>
            </div>
            <div class="mc-note">Create an account to save it & unlock your plan.</div>
          </div>

          <div class="form-header">
            <h2>Create your account</h2>
            <p>Already have one? <RouterLink to="/login">Log in</RouterLink></p>
          </div>

          <form @submit.prevent="onSubmit">
            <div class="name-row">
              <div class="form-group">
                <label class="form-label">First name</label>
                <input
                  v-model="form.first_name"
                  type="text"
                  class="form-input"
                  placeholder="Marcus"
                  autocomplete="given-name"
                >
                <span v-if="errors.first_name" class="form-error">{{ errors.first_name }}</span>
              </div>
              <div class="form-group">
                <label class="form-label">Last name</label>
                <input
                  v-model="form.last_name"
                  type="text"
                  class="form-input"
                  placeholder="Johnson"
                  autocomplete="family-name"
                >
                <span v-if="errors.last_name" class="form-error">{{ errors.last_name }}</span>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="form-input"
                placeholder="marcus@email.com"
                autocomplete="email"
              >
              <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
            </div>

            <div class="form-group">
              <label class="form-label">Password</label>
              <div class="password-wrap">
                <input
                  v-model="form.password"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  class="form-input"
                  placeholder="At least 8 characters"
                  autocomplete="new-password"
                >
                <button
                  type="button"
                  class="password-toggle"
                  @click="isPasswordVisible = !isPasswordVisible"
                >
                  {{ isPasswordVisible ? 'Hide' : 'Show' }}
                </button>
              </div>
              <div class="password-strength">
                <div
                  v-for="i in 4"
                  :key="i"
                  class="strength-bar"
                  :class="{ active: i <= passwordStrength.level, [passwordStrength.strength]: i <= passwordStrength.level && passwordStrength.strength }"
                />
              </div>
              <div
                v-if="passwordStrength.label"
                class="strength-label"
                :class="passwordStrength.strength"
              >
                {{ passwordStrength.label }}
              </div>
              <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
            </div>

            <div class="form-group">
              <label class="form-label">Confirm password</label>
              <input
                v-model="form.password_confirmation"
                :type="isPasswordVisible ? 'text' : 'password'"
                class="form-input"
                placeholder="Repeat password"
                autocomplete="new-password"
              >
              <span v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation }}</span>
            </div>

            <div class="form-checkbox">
              <input
                id="disclaimer"
                v-model="form.privacyPolicies"
                type="checkbox"
                class="checkbox-input"
              >
              <label class="checkbox-label" for="disclaimer">
                I understand FreedomStack provides <a href="/terms" target="_blank" rel="noopener">educational information only</a>, not financial advice. I've read the <a href="/privacy" target="_blank" rel="noopener">privacy policy</a>.
              </label>
            </div>
            <VExpandTransition>
              <p v-if="errors.privacyPolicies" class="form-error">{{ errors.privacyPolicies }}</p>
            </VExpandTransition>

            <p v-if="submitError" class="form-error mb-3">
              {{ submitError }}
            </p>

            <button
              type="submit"
              class="btn-submit"
              :disabled="isSubmitting"
            >
              <span v-if="isSubmitting">Creating…</span>
              <template v-else>
                Create account & build my plan <span class="arrow">→</span>
              </template>
            </button>
          </form>

          <p class="form-footer">
            Free forever for basic features. No credit card required.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.auth-split-page {
  --fs-black: #0a0a0a;
  --fs-black-card: #111111;
  --fs-white: #fafaf9;
  --fs-green: #22c55e;
  --fs-green-glow: rgba(34, 197, 94, 0.12);
  --fs-red: #ef4444;
  --fs-amber: #f59e0b;
  --fs-gray-300: #d4d4d8;
  --fs-gray-400: #a1a1aa;
  --fs-gray-500: #71717a;
  --fs-gray-600: #52525b;
  --fs-gray-800: #27272a;
  min-height: 100vh;
  background: var(--fs-black);
  color: var(--fs-white);
  font-family: 'DM Sans', system-ui, sans-serif;
}

.auth-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
}

.auth-left {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 2.5rem;
  overflow: hidden;
}

.auth-left::before {
  content: '';
  position: absolute;
  top: -20%;
  left: -20%;
  width: 140%;
  height: 140%;
  background:
    radial-gradient(ellipse at 30% 20%, rgba(34, 197, 94, 0.08) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 80%, rgba(34, 197, 94, 0.05) 0%, transparent 50%);
  pointer-events: none;
}

.auth-left::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
  background-size: 60px 60px;
  pointer-events: none;
}

.auth-left-content {
  position: relative;
  z-index: 2;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  max-width: 480px;
}

.auth-logo {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 1.15rem;
  letter-spacing: -0.02em;
  text-decoration: none;
  color: var(--fs-white);
}

.logo-icon {
  width: 28px;
  height: 28px;
  background: var(--fs-green);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  color: var(--fs-black);
  font-weight: 700;
}

.auth-left-headline {
  font-family: 'Instrument Serif', Georgia, serif;
  font-size: clamp(2rem, 3.5vw, 2.8rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  margin-bottom: 1.5rem;
}

.auth-left-headline em {
  font-style: italic;
  color: var(--fs-green);
}

.auth-left-body {
  color: var(--fs-gray-300);
  font-size: 1.05rem;
  line-height: 1.7;
  margin-bottom: 2.5rem;
}

.feature-pills {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.pill {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.15rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  transition: all 0.25s;
}

.pill:hover {
  border-color: rgba(34, 197, 94, 0.2);
  background: rgba(34, 197, 94, 0.04);
}

.pill-icon-wrap {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid rgba(34, 197, 94, 0.35);
  background: rgba(34, 197, 94, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
}

.pill-icon {
  color: var(--fs-green);
}

.pill-text {
  font-size: 0.9rem;
  color: var(--fs-gray-300);
}

.pill-text strong {
  color: var(--fs-white);
}

.saved-number-callout {
  background: rgba(34, 197, 94, 0.06);
  border: 1px solid rgba(34, 197, 94, 0.15);
  border-radius: 14px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.saved-number-callout .callout-label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: var(--fs-gray-400);
  margin-bottom: 0.35rem;
}

.saved-number-callout .callout-number {
  font-family: 'JetBrains Mono', monospace;
  font-size: 2rem;
  font-weight: 600;
  color: var(--fs-green);
  letter-spacing: -0.02em;
}

.saved-number-callout .callout-note {
  font-size: 0.82rem;
  color: var(--fs-gray-500);
  margin-top: 0.35rem;
}

.auth-left-footer {
  position: relative;
  z-index: 2;
  font-size: 0.75rem;
  color: var(--fs-gray-600);
  line-height: 1.5;
}

.auth-right {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2.5rem;
  background: var(--fs-black-card);
  border-left: 1px solid rgba(255, 255, 255, 0.05);
}

.auth-form-container {
  width: 100%;
  max-width: 400px;
}

.form-header {
  margin-bottom: 2rem;
}

.form-header h2 {
  font-family: 'Instrument Serif', Georgia, serif;
  font-size: 1.75rem;
  letter-spacing: -0.02em;
  margin-bottom: 0.35rem;
  color: var(--fs-white);
}

.form-header p {
  font-size: 0.9rem;
  color: var(--fs-gray-300);
}

.form-header p a,
.form-header a {
  color: var(--fs-green);
  text-decoration: none;
  font-weight: 500;
}

.form-header p a:hover {
  opacity: 0.8;
}

.name-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--fs-gray-300);
  margin-bottom: 0.4rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: var(--fs-white);
  font-family: 'DM Sans', system-ui, sans-serif;
  font-size: 0.92rem;
  outline: none;
  transition: all 0.25s;
}

.form-input::placeholder {
  color: var(--fs-gray-500);
}

.form-input:focus {
  border-color: var(--fs-green);
  background: rgba(34, 197, 94, 0.04);
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.08);
}

.form-error {
  display: block;
  font-size: 0.75rem;
  color: var(--fs-red);
  margin-top: 0.35rem;
}

.password-wrap {
  position: relative;
}

.password-wrap .form-input {
  padding-right: 4rem;
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--fs-gray-300);
  cursor: pointer;
  font-size: 0.82rem;
  font-family: inherit;
}

.password-toggle:hover {
  color: var(--fs-white);
}

.password-strength {
  display: flex;
  gap: 0.35rem;
  margin-top: 0.5rem;
}

.strength-bar {
  height: 3px;
  flex: 1;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 100px;
  transition: background 0.3s;
}

.strength-bar.active.weak { background: var(--fs-red); }
.strength-bar.active.medium { background: var(--fs-amber); }
.strength-bar.active.strong { background: var(--fs-green); }

.strength-label {
  font-size: 0.72rem;
  font-family: 'JetBrains Mono', monospace;
  margin-top: 0.25rem;
  min-height: 1em;
}

.strength-label.weak { color: var(--fs-red); }
.strength-label.medium { color: var(--fs-amber); }
.strength-label.strong { color: var(--fs-green); }

.form-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  margin-bottom: 1rem;
}

.checkbox-input {
  appearance: none;
  width: 18px;
  height: 18px;
  border: 1.5px solid rgba(255, 255, 255, 0.15);
  border-radius: 5px;
  background: transparent;
  cursor: pointer;
  flex-shrink: 0;
  margin-top: 2px;
  position: relative;
}

.checkbox-input:checked {
  background: var(--fs-green);
  border-color: var(--fs-green);
}

.checkbox-input:checked::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.7rem;
  color: var(--fs-black);
  font-weight: 700;
}

.checkbox-label {
  font-size: 0.82rem;
  color: var(--fs-gray-300);
  line-height: 1.5;
}

.checkbox-label a {
  color: var(--fs-gray-300);
  text-decoration: underline;
  text-underline-offset: 2px;
}

.btn-submit {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: var(--fs-green);
  color: var(--fs-black);
  padding: 0.85rem;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  font-family: inherit;
  border: none;
  cursor: pointer;
  transition: all 0.25s;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 24px rgba(34, 197, 94, 0.3);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-submit .arrow {
  transition: transform 0.2s;
}

.btn-submit:hover:not(:disabled) .arrow {
  transform: translateX(3px);
}

.form-footer {
  text-align: center;
  margin-top: 1.75rem;
  font-size: 0.82rem;
  color: var(--fs-gray-400);
}

.mobile-callout {
  display: none;
}

@media (max-width: 900px) {
  .auth-layout {
    grid-template-columns: 1fr;
  }

  .auth-right {
    order: 1;
    border-left: none;
    padding: 3rem 1.5rem 2rem;
  }

  .auth-left {
    order: 2;
    min-height: auto;
    padding: 2rem 1.5rem 2.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
  }

  .auth-left-content {
    max-width: 100%;
  }

  .saved-number-callout {
    display: none;
  }

  .mobile-callout {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    background: rgba(34, 197, 94, 0.06);
    border: 1px solid rgba(34, 197, 94, 0.15);
    border-radius: 10px;
    padding: 0.85rem 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
  }

  .mobile-callout .mc-label {
    font-size: 0.72rem;
    color: var(--fs-gray-400);
    font-family: 'JetBrains Mono', monospace;
    text-transform: uppercase;
    letter-spacing: 0.1em;
  }

  .mobile-callout .mc-number {
    font-family: 'JetBrains Mono', monospace;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--fs-green);
  }

  .mobile-callout .mc-note {
    font-size: 0.75rem;
    color: var(--fs-gray-300);
  }

  .auth-left-headline {
    font-size: 1.6rem;
    margin-bottom: 1rem;
  }

  .auth-left-body {
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
  }

  .name-row {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 480px) {
  .name-row {
    grid-template-columns: 1fr;
  }

  .form-header h2 {
    font-size: 1.5rem;
  }
}
</style>
