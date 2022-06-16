import '@/core'
import '@/globals'
import 'js/dynatree/jquery.dynatree'
import 'js/dynatree/skin/ui.dynatree.css'
import { vueApply, vueRegister } from '@/vue'
import RegisterForm from './components/RegisterForm.vue'

vueRegister({
  RegisterForm,
})
vueApply('#register-form')
