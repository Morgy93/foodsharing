<template>
  <MenuItem
    :title="$i18n('menu.entry.mailbox')"
    :url="$url('mailbox')"
    icon="fa-envelope"
    :badge="unread"
  />
</template>

<script>
import { getMailUnreadCount } from '@/api/mailbox'
import MenuItem from '../MenuItem'

export default {
  components: { MenuItem },
  data () {
    return {
      count: 0,
    }
  },
  computed: {
    unread () {
      if (this.count) {
        return this.count < 99 ? this.count : '99+'
      }
      return null
    },
  },
  async mounted () {
    this.count = await getMailUnreadCount()
  },
}

</script>
