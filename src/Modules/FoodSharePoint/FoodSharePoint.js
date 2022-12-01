/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-jcrop'
import { attachAddressPicker } from '@/addressPicker'
import { vueApply, vueRegister } from '@/vue'
import FileUploadVForm from '@/components/upload/FileUploadVForm'
import { GET } from '@/browser'
import AvatarList from '@/components/AvatarList'

import './FoodSharePoint.css'

// Wallpost
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

vueRegister({
  FileUploadVForm, AvatarList,
})

const sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddressPicker()
  vueApply('#image-upload')
} else if (sub === 'ft') {
  initWall('fairteiler', GET('id'))

  // The lists of followers and managers are only included if they are not empty
  if (document.getElementById('fsp-followers')) {
    vueApply('#fsp-followers')
  }
  if (document.getElementById('fsp-managers')) {
    vueApply('#fsp-managers')
  }
}
