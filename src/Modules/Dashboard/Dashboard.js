import '@/core'
import '@/globals'

import { vueRegister, vueApply } from '@/vue'

// View: Dashboard
import './Dashboard.scss'
import Dashboard from './Dashboard.vue'

vueRegister({
  Dashboard,
})
vueApply('#dashboard')
