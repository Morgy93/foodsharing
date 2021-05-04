/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import { vueApply, vueRegister } from '@/vue'
import BusinessCardForm from './components/BusinessCardForm'

vueRegister({
  BusinessCardForm,
})
vueApply('#business-card-form')
