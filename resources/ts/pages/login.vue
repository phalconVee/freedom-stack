<script setup lang="ts">
/**
 * Login — split layout per freedomstack-auth-screens-v2.html
 * Left: headline "Welcome back. Your number is waiting.", stat grid. Right: minimal form.
 */
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const route = useRoute()
const router = useRouter()
const ability = useAbility()

const credentials = ref({
  email: '',
  password: '',
})

const rememberMe = ref(false)
const isPasswordVisible = ref(false)
const errors = ref<Record<string, string | undefined>>({
  email: undefined,
  password: undefined,
})

const loginStats = [
  { num: '60s', label: 'Average monthly check-in' },
  { num: '12+ yr', label: 'Avg time saved with a plan' },
  { num: '0', label: "Ads you'll ever see here" },
  { num: '100%', label: 'Your data stays yours' },
]

const login = async () => {
  errors.value = {}
  if (!credentials.value.email?.trim()) {
    errors.value.email = 'Email is required'
    return
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(credentials.value.email)) {
    errors.value.email = 'Enter a valid email'
    return
  }
  if (!credentials.value.password) {
    errors.value.password = 'Password is required'
    return
  }
  try {
    const res = await $api('/auth/login', {
      method: 'POST',
      body: {
        email: credentials.value.email,
        password: credentials.value.password,
      },
      onResponseError({ response }: { response: { _data?: { errors?: Record<string, string | string[]> } } }) {
        const data = response._data
        if (data?.errors) {
          const normalized: Record<string, string | undefined> = {}
          for (const [key, val] of Object.entries(data.errors))
            normalized[key] = Array.isArray(val) ? val[0] : val
          errors.value = { ...errors.value, ...normalized }
        }
      },
    })

    const { accessToken, userData, userAbilityRules } = res

    useCookie('userAbilityRules').value = userAbilityRules
    ability.update(userAbilityRules)
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    await nextTick(() => {
      router.replace(route.query.to ? String(route.query.to) : '/dashboard')
    })
  }
  catch (err) {
    console.error(err)
  }
}

const onSubmit = () => {
  login()
}
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
          <h1 class="auth-left-headline">
            Welcome back.<br>Your number is<br><em>waiting.</em>
          </h1>

          <p class="auth-left-body">
            Pick up where you left off — your Freedom Number, action plan, and progress are all right where you left them.
          </p>

          <div class="login-stat-grid">
            <div
              v-for="(stat, i) in loginStats"
              :key="i"
              class="login-stat"
            >
              <div class="stat-num">{{ stat.num }}</div>
              <div class="stat-label">{{ stat.label }}</div>
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
          <div class="form-header">
            <h2>Welcome back</h2>
            <p>Don't have an account? <RouterLink to="/register">Create one free</RouterLink></p>
          </div>

          <form @submit.prevent="onSubmit">
            <div class="form-group">
              <label class="form-label">Email</label>
              <input
                v-model="credentials.email"
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
                  v-model="credentials.password"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  class="form-input"
                  placeholder="Enter your password"
                  autocomplete="current-password"
                >
                <button
                  type="button"
                  class="password-toggle"
                  @click="isPasswordVisible = !isPasswordVisible"
                >
                  {{ isPasswordVisible ? 'Hide' : 'Show' }}
                </button>
              </div>
              <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
            </div>

            <div class="form-extras">
              <label class="remember-me">
                <input
                  v-model="rememberMe"
                  type="checkbox"
                >
                Remember me
              </label>
              <RouterLink to="/forgot-password" class="forgot-link">Forgot password?</RouterLink>
            </div>

            <button
              type="submit"
              class="btn-submit"
            >
              Log in <span class="arrow">→</span>
            </button>
          </form>

          <p class="form-footer">
            Your data is encrypted and never shared. <RouterLink to="/privacy">Privacy policy</RouterLink>
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
  --fs-gray-300: #d4d4d8;
  --fs-gray-400: #a1a1aa;
  --fs-gray-500: #71717a;
  --fs-gray-600: #52525b;
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

.login-stat-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.login-stat {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 1.25rem;
}

.login-stat .stat-num {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--fs-green);
  margin-bottom: 0.15rem;
}

.login-stat .stat-label {
  font-size: 0.8rem;
  color: var(--fs-gray-300);
}

.auth-left-footer {
  position: relative;
  z-index: 2;
  font-size: 0.75rem;
  color: var(--fs-gray-400);
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

.form-header a {
  color: var(--fs-green);
  text-decoration: none;
  font-weight: 500;
}

.form-header a:hover {
  opacity: 0.8;
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
  font-family: inherit;
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
  color: #ef4444;
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

.form-extras {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.75rem;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.82rem;
  color: var(--fs-gray-300);
  cursor: pointer;
}

.remember-me input {
  appearance: none;
  width: 16px;
  height: 16px;
  border: 1.5px solid rgba(255, 255, 255, 0.15);
  border-radius: 4px;
  background: transparent;
  cursor: pointer;
  position: relative;
}

.remember-me input:checked {
  background: var(--fs-green);
  border-color: var(--fs-green);
}

.remember-me input:checked::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.6rem;
  color: var(--fs-black);
  font-weight: 700;
}

.forgot-link {
  font-size: 0.82rem;
  color: var(--fs-gray-300);
  text-decoration: none;
}

.forgot-link:hover {
  color: var(--fs-green);
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

.btn-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 24px rgba(34, 197, 94, 0.3);
}

.btn-submit .arrow {
  transition: transform 0.2s;
}

.btn-submit:hover .arrow {
  transform: translateX(3px);
}

.form-footer {
  text-align: center;
  margin-top: 1.75rem;
  font-size: 0.82rem;
  color: var(--fs-gray-400);
}

.form-footer a {
  color: var(--fs-green);
  text-decoration: none;
  font-weight: 500;
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

  .auth-left-headline {
    font-size: 1.6rem;
    margin-bottom: 1rem;
  }

  .auth-left-body {
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
  }

  .login-stat-grid {
    gap: 0.75rem;
  }

  .login-stat {
    padding: 1rem;
  }

  .login-stat .stat-num {
    font-size: 1.25rem;
  }
}

@media (max-width: 480px) {
  .form-header h2 {
    font-size: 1.5rem;
  }
}
</style>
