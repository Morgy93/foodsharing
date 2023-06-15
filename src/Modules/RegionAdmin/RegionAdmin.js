import '@/core'
import '@/globals'
import { img } from '@/script'
import { expose } from '@/utils'
import './RegionAdmin.css'
import { vueApply, vueRegister } from '@/vue'
import RegionAdminPanel from '@/components/RegionAdmin/RegionAdminPanel'

expose({
  img,
})

vueRegister({ RegionAdminPanel })
vueApply('#region-admin-panel')
