import '@/core'
import '@/globals'
import './Event.css'
import { GET } from '@/browser'
// Wallpost
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'
import { vueRegister, vueApply } from '@/vue'

import EventPanel from './components/EventPanel'
import EventEditForm from '@/components/events/EventEditForm'

const sub = GET('sub')
console.log({ sub: sub, x: sub === 'add' })

if (sub === 'add' || sub === 'edit') {
  vueRegister({ EventEditForm })
  vueApply('#event-edit-form')
} else {
  initWall('event', GET('id'))
  vueRegister({
    EventPanel,
  })

  vueApply('#event-panel', true)
}
