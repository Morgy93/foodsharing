/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import 'jquery-jcrop'
import { attachAddressPicker } from '@/addressPicker'

import {
  pictureCrop,
  pictureReady
} from '@/script'
import { expose } from '@/utils'
import { GET } from '@/browser'

import './FoodSharePoint.css'

expose({
  pictureCrop,
  pictureReady
})

$('#wall-submit').on('mousedown', function () {
  $('#ft-public-link').trigger('click')
})

const sub = GET('sub')
if (sub === 'add' || sub === 'edit') {
  attachAddressPicker()
}
