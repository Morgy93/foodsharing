<template>
  <div
    class="my-2 p-2 border border-primary"
  >
    <details>
      <summary
        @click.once="buildSignoutHistory"
      >
        {{ $i18n('pickup.signout_history.title') }}
        <i
          v-if="isLoading"
          class="fas fa-spinner fa-spin"
        />
      </summary>

      <button
        class="badge w-100 badge-secondary my-2"
        @click="buildSignoutHistory"
      >
        <i
          v-if="isLoading"
          class="fas fa-spinner fa-spin"
        /> {{ $i18n('pickup.signout_history.reload') }}
      </button>

      <div
        v-for="(pickupSlot, pickupSlotTimestamp) in signOutHistory"
        :key="pickupSlotTimestamp"
      >
        <SignoutHistoryEntry :pickup-slot="pickupSlot" />
      </div>

      <div
        v-if="!hasLogEntries && !isLoading"
        class="alert alert-info"
      >
        {{ $i18n('pickup.signout_history.no_signouts_found') }}
      </div>
    </details>
  </div>
</template>

<script>
import { getStoreLog } from '@/api/stores'
import SignoutHistoryEntry from '@/components/Stores/SignoutHistory/SignoutHistoryEntry'

export default {
  components: { SignoutHistoryEntry },
  props: {
    storeId: { type: Number, default: null },
  },

  data () {
    return {
      isLoading: false,
      signOutHistory: {},
      hasLogEntries: true,
    }
  },

  methods: {
    async buildSignoutHistory () {
      this.isLoading = true

      const storeLog = await getStoreLog(this.storeId, [12, 13])

      if (storeLog.length < 1) {
        this.hasLogEntries = false
        this.isLoading = false
        return
      }
      this.hasLogEntries = true

      const storeLogWithFoodsaverProfilUrl = this.getStoreLogWithAttachedFoodsaverProfilUrl(storeLog)
      const groupedLog = this.getGroupedHistory(storeLogWithFoodsaverProfilUrl)

      this.signOutHistory = this.getSortedHistory(groupedLog)
      this.isLoading = false
    },
    getStoreLogWithAttachedFoodsaverProfilUrl (storeLog) {
      storeLog.forEach((logEntry) => {
        if (logEntry.affected_foodsaver) {
          logEntry.affected_foodsaver.profil_url = this.$url('profile', logEntry.affected_foodsaver.id)
        }

        if (logEntry.performed_foodsaver) {
          logEntry.performed_foodsaver.profil_url = this.$url('profile', logEntry.performed_foodsaver.id)
        }
      })

      return storeLog
    },
    getGroupedHistory (storeLog) {
      const groupedHistory = {}

      storeLog.forEach(logEntry => {
        const pickupDateAsString = logEntry.date_reference
        const pickupDateAsTimestamp = new Date(pickupDateAsString).getTime()
        const performedAtDateAsTimestamp = new Date(logEntry.performed_at).getTime()

        if (!groupedHistory[pickupDateAsTimestamp]) {
          groupedHistory[pickupDateAsTimestamp] = {
            pickup_timestamp: pickupDateAsTimestamp,
            pickup_date: pickupDateAsString,
            pickup_date_object: new Date(pickupDateAsString),
            signouts: [],
          }
        }

        if (groupedHistory[pickupDateAsTimestamp]) {
          groupedHistory[pickupDateAsTimestamp].signouts.push({
            performed_at_timestamp: performedAtDateAsTimestamp,
            information: logEntry,
          })
        }
      })

      return groupedHistory
    },
    getSortedHistory (groupedHistory) {
      const sortedHistoryByPickupTimestamps = Object.keys(groupedHistory).sort().reduce(function (result, key) {
        result[key] = groupedHistory[key]
        return result
      }, {})

      Object.keys(sortedHistoryByPickupTimestamps).forEach(key => {
        sortedHistoryByPickupTimestamps[key].signouts.sort(function (a, b) {
          return a.performed_at_timestamp - b.performed_at_timestamp
        })
      })

      return sortedHistoryByPickupTimestamps
    },
  },
}
</script>

<style scoped>

</style>
