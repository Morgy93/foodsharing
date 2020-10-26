/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-jcrop'
import { attachAddressPicker } from '@/addressPicker'
import { vueApply, vueRegister } from '@/vue'
import FileUploadVForm from '@/components/upload/FileUploadVForm'

import {
  pictureCrop,
  pictureReady,
} from '@/script'
import { expose } from '@/utils'
import { GET } from '@/browser'

import './FoodSharePoint.css'

vueRegister({
  FileUploadVForm,
})

expose({
  pictureCrop,
  pictureReady,
})

const sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddressPicker()
  vueApply('#image-upload')
}
