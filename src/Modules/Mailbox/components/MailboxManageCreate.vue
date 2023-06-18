<template>
  <div>
    <h4>Create</h4>
    <b-form-input
      v-model="name"
      placeholder="Angezeigter Name *"
    />
    <b-form-input
      v-model="mailboxAlias"
      placeholder="Name *"
    />
    <UserSearchInput
      button-icon="fa-user-plus"
      @user-selected="addNewUser"
    />
    <b-form-tags
      v-model="mailboxUserList"
      input-id="tags-pills"
      tag-variant="primary"
      tag-pills
      size="lg"
      separator=" "
      placeholder="Enter new tags separated by space"
    />
  </div>
</template>

<script>
import UserSearchInput from '@/components/UserSearchInput.vue'

export default {
  components: { UserSearchInput },
  data () {
    return {
      name: null,
      mailboxAlias: null,
      mailboxUser: [],
      searchQuery: null,
      mailboxUserList: [],
    }
  },
  methods: {
    async addNewUser (userId) {
      try {
        this.mailboxUserList.push(userId)
      } catch {

      }
    },
  },
}
</script>
