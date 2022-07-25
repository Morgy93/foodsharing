import { vueRegister, vueApply } from '@/vue'
import Footer from './Footer.vue'

vueRegister({ Footer })
vueApply('#vue-footer', true)
