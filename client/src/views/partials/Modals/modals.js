import { vueRegister, vueApply } from '@/vue'
import Modals from './Modals.vue'

vueRegister({ Modals })
vueApply('#vue-modals', true)
