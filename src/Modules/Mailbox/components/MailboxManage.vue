<template>
  <div>
    <MailboxManageOverview />
    <MailboxManageEdit v-if="enableEditMode" />
  </div>
</template>

<script>
import MailboxManageOverview from './MailboxManageOverview.vue'
import MailboxManageEdit from './MailboxManageEdit.vue'

export default {
  components: {
    MailboxManageOverview,
    MailboxManageEdit,
  },
  data () {
    return {
      mailboxes: [],
      enableEditMode: false,
    }
  },
}
</script>

<style scoped lang="scss">

</style>
