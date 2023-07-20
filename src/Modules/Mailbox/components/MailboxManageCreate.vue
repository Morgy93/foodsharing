<template>
  <div>
    <p>Mailbox erstellen</p>
    <label>Angezeigter Name *</label>
    <b-form-input
      v-model="name"
    />
    <label>Name *</label>
    <b-form-input
      v-model="mailboxAlias"
    />
    <label>Ohne "@foodsharing.network"</label>
    <UserSearchInput
      button-icon="fa-user-plus"
      @user-selected="addNewUser"
    />
    <b-form-tags
      v-model="getMailboxUserName"
      input-id="tags-pills"
      tag-variant="primary"
      tag-pills
      size="lg"
      separator=" "
      placeholder="Enter new tags separated by space"
    />
    <b-button
      size="sm"
      variant="primary"
      :disabled="!isValidToCreate"
      @click="tryCreateMailbox"
    >
      Erstellen
    </b-button>
  </div>
</template>

<script>
import UserSearchInput from '@/components/UserSearchInput.vue'
import { createMailbox } from '@/api/mailbox'

export default {
  components: { UserSearchInput },
  data () {
    return {
      name: null,
      mailboxAlias: null,
      mailboxUserList: [],
    }
  },
  computed: {
    getMailboxUserName () {
      return this.mailboxUserList.map(user => user.name)
    },
    isValidToCreate () {
      return this.validateAlias && this.validateName && this.mailboxUserList.length > 0
    },
    validateName () {
      return this.name && this.name.length >= 3
    },
    validateAlias () {
      return this.mailboxAlias && this.mailboxAlias.length >= 3
    },
  },
  methods: {
    async addNewUser (userId, userName) {
      try {
        this.mailboxUserList.push({ id: userId, name: userName })
      } catch {

      }
    },
    async tryCreateMailbox () {
      const userIds = this.mailboxUserList.map(user => user.id)
      await createMailbox(this.name, this.mailboxAlias, userIds)
    },
  },
}
</script>
