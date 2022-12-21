import '@/core'
import '@/globals'

import { vueRegister, vueApply } from '@/vue'

// View: Message
import '@/views/pages/Message/MessagePage.scss'
import MessagePage from '@/views/pages/Message/MessagePage.vue'
import LiteChat from '@/views/pages/Message/LiteChat/LiteChat.vue'

vueRegister({
  MessagePage,
  LiteChat,
})
vueApply('#message')
