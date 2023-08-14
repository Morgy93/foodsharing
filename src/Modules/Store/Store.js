import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import StoreRegionList from './components/StoreRegionList.vue'
import StoreOwnList from './components/StoreOwnList.vue'

import { attachAddressPicker } from '@/addressPicker'
import { GET } from '@/script'

if (GET('a') === undefined) {
  vueRegister({
    StoreRegionList,
  })
  vueApply('#vue-store-region-list', true)
}

if (GET('a') === 'own') {
  vueRegister({
    StoreOwnList,
  })
  vueApply('#vue-store-own-list', true)
}

if (GET('a') === 'edit' || GET('a') === 'new') {
  attachAddressPicker()
}
