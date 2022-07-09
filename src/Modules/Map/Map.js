import '@/core'
import '@/globals'

import { vueRegister, vueApply } from '@/vue'

// View: Map
// import '@/views/pages/Dashboard/Dashboard.scss'
import MapGlobal from '@/views/pages/Map/MapGlobal.vue'

vueRegister({
  MapGlobal,
})
vueApply('#MapGlobal')
