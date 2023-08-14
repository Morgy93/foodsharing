import StorePage from '@/views/pages/Store/StorePage.vue'
import { vueApply, vueRegister } from '@/vue'

import '@/core'
import '@/globals'
import './StoreUser.css'

vueRegister({
  StorePage,
})

vueApply('#vue-store-page', true)
