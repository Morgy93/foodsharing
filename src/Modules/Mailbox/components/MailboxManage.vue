<template>
  <div>
    <b-row>
      <b-col
        cols="12"
        md="10"
      >
        {{ $i18n('mailbox.manage') }}
      </b-col>
      <b-col
        cols="12"
        md="2"
      >
        <b-button
          variant="primary"
        >
          Mailbox erstellen
        </b-button>
      </b-col>
    </b-row>
    <b-row
      v-for="mailbox in mailboxes"
      :key="mailbox.id"
      class="pb-2 pt-2"
    >
      <b-col class="bg-white">
        <h4 class="pb-2">
          {{ mailbox.name }}
        </h4>
        <div>{{ mailbox.email_name }}</div>
      </b-col>
      <b-col
        class="bg-white"
      >
        <h4
          v-for="member in mailbox.member"
          :key="member.id"
        >
          {{ mailbox.member.length }} Benutzer
        </h4>
        <span
          v-for="member in mailbox.member"
          :key="member.id"
        >
          {{ member.name }},
        </span>
      </b-col>
    </b-row>
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
