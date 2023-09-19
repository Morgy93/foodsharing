<template>
  <div>
    <div class="border">
      <b-row class="p-2">
        <b-col cols="6">
          <b-button
            v-if="page === MAILBOX_PAGE.EMAIL_LIST && !isEditModeEnabled"
            size="sm"
            variant="outline-primary"
            @click="enableEditMode"
          >
            {{ $i18n('button.edit') }}
          </b-button>
          <b-button
            v-if="page === MAILBOX_PAGE.READ_EMAIL || isEditModeEnabled"
            v-b-tooltip.hover
            :title="$i18n('mailbox.delete')"
            size="sm"
            variant="outline-primary"
            :disabled="areMailsNotSelected && page === MAILBOX_PAGE.EMAIL_LIST"
            @click="showModalToDeleteEmail"
          >
            <i class="fas fa-trash-alt" />
          </b-button>
          <b-button
            v-if="page === MAILBOX_PAGE.READ_EMAIL"
            size="sm"
            variant="outline-primary"
            @click="showAnswerMailPage"
          >
            {{ $i18n('mailbox.reply.short') }}
          </b-button>
          <b-button
            v-if="page === MAILBOX_PAGE.EMAIL_LIST && !isRead && isEditModeEnabled"
            v-b-tooltip.hover
            :title="$i18n('mailbox.mark_as_unread')"
            size="sm"
            variant="outline-primary"
            :disabled="areMailsNotSelected"
            @click="mailboxViewSetReadState"
          >
            <i class="fas fa-check" />
          </b-button>
          <b-button
            v-if="page === MAILBOX_PAGE.EMAIL_LIST && !isSelected && isEditModeEnabled"
            size="sm"
            variant="outline-primary"
            @click="mailboxViewSelectAllRows"
          >
            {{ $i18n('mailbox.mark_all') }}
          </b-button>
          <b-button
            v-else-if="page === MAILBOX_PAGE.EMAIL_LIST && isEditModeEnabled"
            size="sm"
            variant="outline-primary"
            @click="mailboxViewClearSelected"
          >
            {{ $i18n('mailbox.delete_selected') }}
          </b-button>
          <b-dropdown
            v-if="page === MAILBOX_PAGE.READ_EMAIL || isEditModeEnabled"
            id="dropdown-move-to"
            :text="$i18n('mailbox.move_to')"
            class="m-md-2"
            size="sm"
            variant="outline-primary"
            :disabled="areMailsNotSelected && page === MAILBOX_PAGE.EMAIL_LIST"
          >
            <b-dropdown-item
              @click="moveEmail"
            >
              {{ getMovedToFolderTranslation() }}
            </b-dropdown-item>
          </b-dropdown>
        </b-col>
        <b-col
          cols="6"
          class="text-right"
        >
          <b-button
            v-if="!isEditModeEnabled"
            size="sm"
            variant="primary"
            @click="showNewMailPage"
          >
            {{ $i18n('mailbox.write') }}
          </b-button>
          <b-button
            v-else
            size="sm"
            variant="primary"
            @click="disableEditMode"
          >
            {{ $i18n('mailbox.close_edit_mode') }}
          </b-button>
        </b-col>
      </b-row>
    </div>
    <b-modal
      v-model="showEmailDeletionConfirmationModal"
      :title="$i18n('mailbox.conformation_modal.title')"
      :cancel-title="$i18n('globals.close')"
      @ok="mailboxEmailSingleViewDeleteEmail"
      @cancel="cancelEmailDeletion"
    >
      {{ $i18n('mailbox.conformation_modal.message') }}
    </b-modal>
  </div>
</template>

<script>
import { store, MAILBOX_PAGE, MAILBOX_FOLDER } from '@/stores/mailbox'

export default {
  props: {
    folderType: { type: Number, required: true },
    selectedEmail: { type: [Array, Object], required: true },
  },
  data () {
    return {
      isSelected: false,
      isRead: false,
      isEditModeEnabled: false,
      showEmailDeletionConfirmationModal: false,
    }
  },
  computed: {
    page () {
      return store.state.page
    },
    areMailsNotSelected () {
      return this.selectedEmail < 1
    },
  },
  created () {
    this.MAILBOX_PAGE = MAILBOX_PAGE
  },
  methods: {
    getMovedToFolderTranslation () {
      const translations = {
        [MAILBOX_FOLDER.INBOX]: this.$i18n('mailbox.trash'),
        [MAILBOX_FOLDER.SENT]: this.$i18n('mailbox.trash'),
        [MAILBOX_FOLDER.TRASH]: this.$i18n('mailbox.inbox'),
      }
      return translations[this.folderType]
    },
    showNewMailPage () {
      store.setAnswerMode(false)
      store.setPage(MAILBOX_PAGE.NEW_EMAIL)
    },
    showAnswerMailPage () {
      store.setAnswerMode(true)
      store.setPage(MAILBOX_PAGE.NEW_EMAIL)
    },
    mailboxViewSelectAllRows () {
      this.$emit('select-all-rows')
      this.isSelected = true
    },
    mailboxViewSetReadState () {
      this.$emit('set-read-state')
    },
    mailboxViewClearSelected () {
      this.$emit('clear-selected')
      this.isSelected = false
    },
    showModalToDeleteEmail () {
      this.showEmailDeletionConfirmationModal = true
    },
    mailboxEmailSingleViewDeleteEmail () {
      this.$emit('try-delete-email')
      this.showEmailDeletionConfirmationModal = false
    },
    cancelEmailDeletion () {
      this.showEmailDeletionConfirmationModal = false
    },
    enableEditMode () {
      this.isEditModeEnabled = true
    },
    disableEditMode () {
      this.isEditModeEnabled = false
    },
    moveEmail () {
      const folderMappings = {
        [MAILBOX_FOLDER.INBOX]: MAILBOX_FOLDER.TRASH,
        [MAILBOX_FOLDER.SENT]: MAILBOX_FOLDER.TRASH,
        [MAILBOX_FOLDER.TRASH]: MAILBOX_FOLDER.INBOX,
      }
      const newFolder = folderMappings[this.folderType]
      this.$emit('try-move-email', newFolder)
      store.setPage(MAILBOX_PAGE.EMAIL_LIST)
    },
  },
}
</script>

<style scoped>

</style>
