import '@/core'
import '@/globals'
import $ from 'jquery'
import i18n from '@/helper/i18n.js'
import 'js/tageditWrapper'
import 'js/dynatree/jquery.dynatree'
import 'js/dynatree/skin/ui.dynatree.css'
import 'leaflet'
import 'js/leafletWrapper'
import { initMap } from '@/mapUtils'
import { goTo, img, pulseError, pulseSuccess } from '@/script'
import { expose } from '@/utils'
import './RegionAdmin.css'
import { deleteGroup } from '@/api/groups'
import { masterUpdate } from '@/api/regions'
import { searchUser } from '@/api/search'

expose({
  img,
  deleteActiveGroup,
  initMap,
  tryMasterUpdate,
  searchUser,
})

async function deleteActiveGroup () {
  const groupName = $('#tree-hidden-name').val()
  const groupId = $('#tree-hidden').val()
  if (window.confirm(i18n('group.delete_group_sure', { groupName }))) {
    try {
      await deleteGroup(groupId)
      pulseSuccess(i18n('success'))
      goTo('/?page=region')
    } catch (err) {
      if (err.code === 409) {
        pulseError(i18n('region.still_contains_elements'))
      } else {
        pulseError(i18n('error_unexpected'))
      }
      throw err
    }
  }
}

async function tryMasterUpdate (regionId) {
  try {
    masterUpdate(regionId)
    pulseSuccess(i18n('success'))
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}
