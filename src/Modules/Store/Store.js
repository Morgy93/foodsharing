import '@/core'
import '@/globals'
import 'jquery-dynatree'
import { vueRegister, vueApply } from '@/vue'
import StoreList from './components/StoreList.vue'
import { attachAddressPicker } from '@/addressPicker'
import {
  GET, pulseError,
} from '@/script'
import i18n from '@/i18n'
import { removeStoreMember } from '@/api/stores'

if (GET('a') === undefined) {
  vueRegister({
    StoreList,
  })
  vueApply('#vue-storelist', true)
}

if (GET('a') === 'edit' || GET('a') === 'new') {
  attachAddressPicker()
}

export async function removeFromTeam (fsId, fsName) {
  if (!fsId) {
    return
  }
  if (!confirm(i18n('store.sm.reallyRemove', { name: fsName }))) {
    return
  }

  this.isBusy = true
  try {
    await removeStoreMember(this.storeId, fsId)
  } catch (e) {
    pulseError(i18n('error_unexpected'))
    this.isBusy = false
    return
  }
  const index = this.foodsaver.findIndex(member => member.id === fsId)
  if (index >= 0) {
    this.foodsaver.splice(index, 1)
  }
  this.isBusy = false
}
