<template>
  <div>
    {{ mailboxes }}
  </div>
</template>

<script>
import { getMemberBoxes } from '@/api/mailbox'

export default {
  data () {
    return {
      mailboxes: [],
    }
  },
  async mounted () {
    this.mailboxes = await getMemberBoxes()
  },
}
</script>

<style scoped lang="scss">

</style>
