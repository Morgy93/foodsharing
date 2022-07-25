import { vueRegister, vueApply } from '@/vue'

import Topbar from './Navigation.vue'

vueRegister({ Topbar })
vueApply('#vue-topbar')
