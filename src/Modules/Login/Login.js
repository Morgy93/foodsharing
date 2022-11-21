import '@/core'
import '@/globals'
import './Login.css'
import { vueApply, vueRegister } from '@/vue'
import LoginPage from '@/views/pages/Login/LoginPage.vue'

vueRegister({
  LoginPage,
})
const selector = '#login-page'
const elements = document.querySelectorAll(selector)
if (Array.from(elements)?.length) {
  vueApply(selector)
}
