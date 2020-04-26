import '@/core'
import '@/globals'
import $ from 'jquery'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import StoreEdit from './components/StoreEdit.vue'
import StoreList from './components/StoreList.vue'
import { attachAddressPicker } from '@/addressPicker'
import {
  GET
} from '@/script'

$(document).ready(() => {
  switch (GET('a')) {
    case 'edit': {
      vueRegister({
        StoreEdit
      })
      vueApply('#vue-storeedit')
      attachAddressPicker()
      break
    }
    case 'new': {
      // ...
      attachAddressPicker()
      break
    }
    case undefined: {
      vueRegister({
        StoreList
      })
      vueApply('#vue-storelist', true)
      break
    }
    default: {
      break
    }
  }
})
