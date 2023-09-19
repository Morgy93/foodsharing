<template>
  <div v-if="email">
    <Container
      :tag="$i18n('mailbox.mail')"
      :title="$i18n('mailbox.mail')"
    >
      <div class="card bg-white">
        <MailboxMainNav
          :selected-email="email"
          :folder-type="email.mailboxFolder"
          @try-move-email="tryMoveEmail"
          @try-delete-email="tryDeleteEmail"
        />
        <div class="border-left border-right p-2">
          <div class="row">
            <div
              class="col col-6"
              v-text="email.from.name ? email.from.name : email.from.address"
            />
            <div class="col col-6 text-right">
              {{ $i18n('mailbox.date') }} : {{ displayedMailDate }} Uhr
            </div>
          </div>
          <div class="pt-2">
            <h5>{{ email.subject }}</h5>
            <div
              class="pt-2"
              v-html="getBody(email)"
            />
            <b-list-group
              v-if="email.attachments"
              horizontal
              class="pt-2"
            >
              <b-list-group-item
                v-for="attachment in email.attachments"
                :key="attachment.id"
              >
                <b-link
                  v-if="attachment.size > 0"
                  :download="attachment.fileName"
                  :href="$url('upload', attachment.hashedFileName)"
                >
                  {{ attachment.fileName }} ({{ formatFileSize(attachment.size) }})
                </b-link>
                <div
                  v-else
                  v-b-tooltip.hover="$i18n('mailbox.attachment.not_found_explanation')"
                >
                  {{ attachment.fileName }} ({{ $i18n('mailbox.attachment.not_found') }})
                </div>
              </b-list-group-item>
            </b-list-group>
          </div>
        </div>
        <MailboxFooterNav />
      </div>
    </container>
  </div>
</template>

<script>
import DOMPurify from 'dompurify'
import Container from '@/components/Container/Container.vue'
import MailboxFooterNav from './MailboxFooterNav.vue'
import MailboxMainNav from './MailboxMainNav.vue'
import { deleteEmail, setEmailProperties } from '@/api/mailbox'
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/helper/i18n'
import { store, MAILBOX_PAGE } from '@/stores/mailbox'

export default {
  components: { Container, MailboxMainNav, MailboxFooterNav },
  props: {
    email: { type: Object, default: null },
  },
  data () {
    return {
      isBusy: false,
    }
  },
  computed: {
    displayedMailDate () {
      return this.$dateFormatter.format(this.email.time, {
        day: 'numeric',
        month: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
      })
    },
  },
  methods: {
    async tryMoveEmail (folder) {
      showLoader()
      this.isBusy = true
      try {
        await setEmailProperties(this.email.id, null, folder)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async trySetEmailStatus (state) {
      showLoader()
      this.isBusy = true
      try {
        await setEmailProperties(this.email.id, null, state)
        this.setIsReadState(state)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async tryDeleteEmail () {
      showLoader()
      this.isBusy = true
      try {
        await deleteEmail(this.email.id)
        this.closeAndReturnToMailbox()
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    setIsReadState (state) {
      return this.email.isRead
    },
    closeAndReturnToMailbox () {
      store.setPage(MAILBOX_PAGE.EMAIL_LIST)
    },
    formatFileSize (bytes) {
      const units = ['B', 'kB', 'MB']
      let u = 0
      while (Math.round(Math.abs(bytes) * 10) / 10 >= 1024 && u < units.length - 1) {
        bytes /= 1024
        u++
      }

      return bytes.toFixed(1) + ' ' + units[u]
    },
    getBody (email) {
      return email.bodyHtml ? DOMPurify.sanitize(email.bodyHtml) : this.getPlainBody(DOMPurify.sanitize(email.body))
    },
    getPlainBody (text) {
      return this.addLineBreaks(this.addLinks((text)))
    },
    addLineBreaks (text) {
      return text ? text.replace(/\n/g, '<br>') : ''
    },
    addLinks (text) {
      const urlRegex = /(https?:\/\/[^\s]+)/g
      return text ? text.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>') : ''
    },
  },
}
</script>

<style scoped>

</style>
