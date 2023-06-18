<template>
  <div class="container bg-white">
    <b-row>
      <b-col md="10">
        <MailboxManageOverview v-if="!enableCreateMode" />
        <MailboxManageEdit v-if="enableEditMode" />
        <MailboxManageCreate v-if="enableCreateMode" />
      </b-col>
      <b-col v-if="!enableCreateMode">
        <b-button
          variant="primary"
          @click="setCreateMode"
        >
          Mailbox erstellen
        </b-button>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import MailboxManageOverview from './MailboxManageOverview.vue'
import MailboxManageEdit from './MailboxManageEdit.vue'
import MailboxManageCreate from './MailboxManageCreate.vue'

export default {
  components: {
    MailboxManageOverview,
    MailboxManageEdit,
    MailboxManageCreate,
  },
  data () {
    return {
      mailboxes: [],
      enableEditMode: false,
      enableCreateMode: false,
    }
  },
  methods: {
    setCreateMode () {
      this.enableCreateMode = true
    },
  },
}
</script>

<style scoped lang="scss">

</style>
