import { createApp } from 'vue'

import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'

// Styles
import '@core-scss/template/index.scss'
import '@styles/styles.scss'

// Log unhandled promise rejections (e.g. failed plugin or async setup)
if (typeof window !== 'undefined') {
  window.addEventListener('unhandledrejection', event => {
    console.error('[Unhandled rejection]', event.reason)
  })
}

// Create vue app
const app = createApp(App)

// Register plugins
registerPlugins(app)

// Mount vue app with error handling so blank screen shows a message if mount fails
const mountEl = document.getElementById('app')
if (!mountEl) {
  document.body.innerHTML = '<div style="padding:2rem;font-family:system-ui;text-align:center;"><h1>FreedomStack</h1><p>Mount point #app not found.</p></div>'
} else {
  app.config.errorHandler = (err, _instance, info) => {
    console.error('[Vue error]', info, err)
  }
  try {
    app.mount('#app')
  } catch (err) {
    console.error('[Mount failed]', err)
    mountEl.innerHTML = `<div style="padding:2rem;font-family:system-ui;max-width:480px;margin:0 auto;"><h1>FreedomStack</h1><p>App failed to start. Open the browser console (F12) for details.</p><pre style="text-align:left;overflow:auto;font-size:12px;">${err instanceof Error ? err.message : String(err)}</pre></div>`
  }
}
