import { ofetch } from 'ofetch'

// In browser, use same origin so 127.0.0.1 vs localhost doesn't cause CORS.
// Set VITE_API_BASE_URL only when API is on a different host (e.g. Vite dev server on :5173).
function getApiBaseURL(): string {
  if (typeof window !== 'undefined' && window.location?.origin)
    return `${window.location.origin}/api`
  return import.meta.env.VITE_API_BASE_URL || '/api'
}

export const $api = ofetch.create({
  baseURL: getApiBaseURL(),
  credentials: 'include',
  headers: {
    Accept: 'application/json',
  },
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken) {
      const token = `Bearer ${accessToken}`
      const h = options.headers as Headers | Record<string, string> | undefined
      if (h instanceof Headers)
        h.set('Authorization', token)
      else {
        const prev = h && typeof h === 'object' ? { ...h } : {}
        options.headers = new Headers({ ...prev, Authorization: token })
      }
    }
  },
})
