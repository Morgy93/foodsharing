import '@/core'
import '@/globals'
import { vueRegister, vueApply } from '@/vue'
import ChainList from '@/views/pages/ChainList/ChainList.vue'

import {
  GET,
} from '@/script'

if (GET('a') === undefined) {
  vueRegister({
    ChainList,
  })
  vueApply('#vue-chainlist', true)
}
