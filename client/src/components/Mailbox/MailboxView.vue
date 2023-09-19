<template>
  <div v-if="selectedMailbox">
    <Container
      :tag="$i18n('mailbox.mails')"
      :title="$i18n('mailbox.mails')"
      :toggle-visibility="selected"
    >
      <div class="card bg-white">
        <MailboxMainNav
          :selected-email="selected"
          :folder-type="selectedMailbox[2]"
          @try-delete-email="tryDeleteEmail"
          @try-move-email="tryMoveEmail"
          @select-all-rows="selectAllRows"
          @set-read-state="setReadState"
          @clear-selected="clearSelected"
        />
        <b-table
          v-if="selectedMailbox[2] != null && mailboxMails.length > 0"
          ref="selectableTable"
          :fields="columns"
          :items="mailboxMails"
          select-mode="multi"
          responsive="sm"
          selectable
          small
          stacked="sm"
          hover
          @row-selected="onRowSelected"
        >
          <template #cell(selected)="{ rowSelected }">
            <template v-if="rowSelected">
              <span aria-hidden="true">&check;</span>
              <span class="sr-only">Selected</span>
            </template>
            <template v-else>
              <span aria-hidden="true">&nbsp;</span>
              <span class="sr-only">Not selected</span>
            </template>
          </template>
          <template #cell(sender)="row">
            <a
              :key="row.item.id"
              href="#"
              :class="{ unReadMail: !row.item.isRead }"
              @click="showEmail(row.item.id)"
            >
              {{ formatEmailAddress(row.item.from) }}
            </a>
          </template>
          <template #cell(subject)="row">
            <a
              :key="row.item.id"
              href="#"
              :class="{ unReadMail: !row.item.isRead }"
              @click="showEmail(row.item.id)"
            >
              {{ row.item.subject }}
            </a>
          </template>
          <template #cell(date)="row">
            {{ $dateFormatter.format(row.item.time, {
              day: 'numeric',
              month: 'numeric',
              year: 'numeric',
            }) }}
          </template>
          <template #cell(attachments)="row">
            <i
              v-if="row.item.attachments && row.item.attachments.length > 0"
              class="fas fa-paperclip"
            />
          </template>
        </b-table>
        <div v-else>
          {{ $i18n('mailbox.empty') }}
        </div>
        <div
          v-if="emailId"
          class="border p-2"
        />
      </div>
    </Container>
  </div>
</template>

<script>
import Container from '@/components/Container/Container.vue'
import MailboxMainNav from './MailboxMainNav.vue'
import { BTable } from 'bootstrap-vue'
import { hideLoader, pulseError, showLoader } from '@/script'
import { deleteEmail, getAllEmails, setEmailProperties } from '@/api/mailbox'
import i18n from '@/helper/i18n'
import { store, MAILBOX_PAGE } from '@/stores/mailbox'

export default {
  components: { Container, BTable, MailboxMainNav },
  data () {
    return {
      columns: [
        {
          key: 'selected',
          label: '',
          sortable: false,
          class: 'align-middle leftcolumn',
        },
        {
          key: 'sender',
          label: this.$i18n('mailbox.sender'),
          sortable: false,
          class: 'align-middle',
        },
        {
          key: 'subject',
          sortable: false,
          label: this.$i18n('mailbox.subject'),
          class: 'align-middle',
        },
        {
          key: 'date',
          label: this.$i18n('mailbox.date'),
          sortable: false,
          class: 'align-middle',
        },
        {
          key: 'attachments',
          label: '',
          sortable: true,
          class: 'align-middle',
        },
      ],
      emailId: null,
      mailboxMails: [],
      selected: [],
    }
  },
  computed: {
    selectedMailbox () {
      return store.state.selectedMailbox
    },
  },
  watch: {
    selectedMailbox () {
      this.tryGetAllEmails()
    },
  },
  created () {
    this.tryGetAllEmails()
  },
  methods: {
    async tryDeleteEmail () {
      showLoader()
      this.isBusy = true
      try {
        await Promise.all(this.selected.map(email => deleteEmail(email.id)))
        await this.tryGetAllEmails()
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async tryMoveEmail (folder) {
      showLoader()
      this.isBusy = true
      try {
        await Promise.all(this.selected.map(email => setEmailProperties(email.id, null, folder)))
        await this.tryGetAllEmails()
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async tryGetAllEmails () {
      showLoader()
      this.isBusy = true
      try {
        this.mailboxMails = await getAllEmails(this.selectedMailbox[0], this.selectedMailbox[2])
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    formatEmailAddress (address) {
      return (address.name !== undefined && address.name !== null) ? address.name : address.address
    },
    showEmail (emailId) {
      this.emailId = emailId
      store.setPage(MAILBOX_PAGE.READ_EMAIL)
      this.$emit('update:selected-email-id', this.emailId)
    },
    setReadState () {
      this.selected.forEach((item) => {
        if (item.isRead === false) { item.isRead = true }
      })
    },
    onRowSelected (items) {
      this.selected = items
    },
    selectAllRows () {
      this.$refs.selectableTable.selectAllRows()
    },
    clearSelected () {
      this.$refs.selectableTable.clearSelected()
    },
  },
}
</script>

<style>
@media (max-width: 576px) {
  .table th, .table td {
    border-top: none;
  }
  .table.b-table.b-table-stacked-sm > tbody > tr > [data-label]::before {
    content: none;
  }

  table.b-table.b-table-stacked-sm > tbody > tr > td {
    min-width: 500px;
  }
}
</style>
