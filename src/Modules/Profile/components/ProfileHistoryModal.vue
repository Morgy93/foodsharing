<template>
  <b-modal
    id="profileHistoryModal"
    :title="$i18n(title)"
    :ok-title="$i18n('globals.close')"

    @show="fetchHistory"
  >
    <div
      v-if="isLoading"
      class="loader-container mx-auto"
    >
      <i class="fas fa-spinner fa-spin" />
    </div>
    <ul
      v-if="historyEntries.length > 0"
    >
      <li
        v-for="entry in historyEntries"
        :key="entry.date"
      >
        {{ $dateFormatter.dateTime(entry.date) }} {{ $i18n('profile.history.actionBy') }}
        <a
          v-if="entry.actor !== null"
          :href="$url('profile', entry.actor.id)"
        >
          {{ entry.actor.name }}
        </a>
        <span v-else>
          {{ $i18n('profile.history.noActor') }}
        </span>
        <b-badge
          v-if="isVerificationHistory"
          :variant="entry.wasVerified ? 'success' : 'danger'"
          class="ml-3"
        >
          {{ $i18n(entry.wasVerified ? 'profile.history.wasVerified' : 'profile.history.lostVerification') }}
        </b-badge>
      </li>
    </ul>
    <p v-else>
      {{ $i18n('profile.history.noData') }}
    </p>
  </b-modal>
</template>

<script>
import { pulseError } from '@/script'
import { getPassHistory, getVerificationHistory } from '@/api/verification'
import { BBadge, BModal } from 'bootstrap-vue'

export default {
  components: { BBadge, BModal },
  data () {
    return {
      isLoading: false,
      userId: null,
      isVerificationHistory: false,
      historyEntries: [],
    }
  },
  computed: {
    title () {
      return this.isVerificationHistory ? 'profile.nav.verificationHistory' : 'profile.nav.history'
    },
  },
  methods: {
    showModal (userId, isVerificationHistory) {
      this.userId = userId
      this.isVerificationHistory = isVerificationHistory
      this.$bvModal.show('profileHistoryModal')
    },
    async fetchHistory () {
      this.isLoading = true
      try {
        this.historyEntries = await (this.isVerificationHistory ? getVerificationHistory(this.userId) : getPassHistory(this.userId))
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
      }

      this.isLoading = false
    },
  },
}
</script>
