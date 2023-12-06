import '@/core'
import '@/globals'
import { vueRegister, vueApply } from '@/vue'
import Mailbox from './components/Mailbox.vue'
import MailboxManage from './components/MailboxManage.vue'
import { GET } from '@/script'

if (GET('a') === 'manage') {
  vueRegister({
    MailboxManage,
  })
  vueApply('#vue-mailbox-manage', true) // Mailbox
}

vueRegister({
  Mailbox,
})
vueApply('#vue-mailbox', true) // Mailbox
