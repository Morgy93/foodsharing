import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import StoreOverview from './components/StoreOverview.vue'

vueRegister({
  StoreOverview
})
vueApply('#store-overview')
