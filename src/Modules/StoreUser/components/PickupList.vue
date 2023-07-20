<template>
  <div class="bootstrap">
    <div class="card rounded">
      <div class="card-header text-white bg-primary">
        <div class="row align-items-center">
          <div class="col text-truncate font-weight-bold">
            {{ $i18n('pickup.dates') }}
          </div>
          <div class="col col-5 text-right">
            <div
              class="btn-group slot-actions"
              role="group"
            >
              <button
                v-if="isCoordinator"
                v-b-tooltip
                :title="$i18n('pickup.add_onetime_pickup')"
                class="btn btn-primary btn-sm"
                @click="$bvModal.show('AddPickupModal')"
              >
                <i class="fas fa-plus" />
              </button>
            </div>
          </div>
        </div>
      </div>
      <div
        :class="{disabledLoading: isLoading}"
        class="pickup-list card-body"
      >
        <div v-if="!hasPickups">
          {{ $i18n('pickup.no_slots_available') }}
        </div>
        <div v-if="hasPickups">
          <Pickup
            v-for="pickup in pickups"
            :key="pickup.date.valueOf()"
            v-bind="pickup"
            :store-id="storeId"
            :store-title="storeTitle"
            :is-coordinator="isCoordinator"
            :user="user"
            :description="pickup.description"
            class="pickup-block"
            @leave="leave"
            @kick="kick"
            @join="join"
            @confirm="confirm"
            @delete="setSlots(pickup.date, 0, pickup.description)"
            @add-slot="setSlots(pickup.date, pickup.totalSlots + 1, pickup.description)"
            @remove-slot="setSlots(pickup.date, pickup.totalSlots - 1, pickup.description)"
            @team-message="sendTeamMessage"
            @edit-description="editDescription"
          />
        </div>
      </div>
    </div>
    <AddPickupModal
      :store-id="storeId"
    />
  </div>
</template>

<script>
import { VBTooltip } from 'bootstrap-vue'
import Pickup from './Pickup'
import AddPickupModal from './AddPickupModal.vue'
import { setPickupSlots, confirmPickup, joinPickup, leavePickup, listPickups } from '@/api/pickups'
import { sendMessage } from '@/api/conversations'
import DataUser from '@/stores/user'
import { ajreq, pulseError, pulseSuccess } from '@/script'

export default {
  components: { Pickup, AddPickupModal },
  directives: { VBTooltip },
  props: {
    storeId: {
      type: Number,
      required: true,
    },
    storeTitle: {
      type: String,
      default: '',
    },
    isCoordinator: {
      type: Boolean,
      default: false,
    },
    teamConversationId: {
      type: Number,
      default: null,
    },
  },
  data () {
    return {
      pickups: [],
      hasPickups: false,
      isLoading: false,
      isModalOpen: false,
      user: DataUser.getters.getUser(),
    }
  },
  _interval: null,
  async created () {
    await this.reload()

    // pull for updates every 30 seconds
    this._interval = setInterval(() => {
      this.reload(true) // reload without loading indicator
    }, 30 * 1000)
  },
  destroyed () {
    clearInterval(this._interval)
  },
  methods: {
    openModal () {
      this.isModalOpen = true
    },
    closeModal () {
      this.isModalOpen = false
    },
    async reload (silent = false) {
      if (!silent) this.isLoading = true
      try {
        this.pickups = await listPickups(this.storeId)
        this.hasPickups = this.pickups.length > 0
      } catch (e) {
        pulseError(this.$i18n('pickuplist.error_loadingPickup') + e)
      }

      if (!silent) this.isLoading = false
    },
    async join (date) {
      this.isLoading = true
      try {
        await joinPickup(this.storeId, date, DataUser.getters.getUserId())
      } catch (e) {
        console.error(e)
        pulseError(this.$i18n('pickuplist.tooslow') + '<br /><br />' + this.$i18n('pickuplist.tryagain'))
      }
      this.reload()
    },
    async leave (date) {
      this.isLoading = true
      try {
        await leavePickup(this.storeId, date, DataUser.getters.getUserId())
      } catch (e) {
        pulseError(this.$i18n('pickuplist.error_leave') + e)
      }
      this.reload()
    },
    async kick (data) {
      this.isLoading = true
      try {
        await leavePickup(this.storeId, data.date, data.fsId, data.message)
      } catch (e) {
        pulseError(this.$i18n('pickuplist.error_kick') + e)
      }
      this.reload()
    },
    async confirm (data) {
      this.isLoading = true
      try {
        await confirmPickup(this.storeId, data.date, data.fsId)
      } catch (e) {
        pulseError(this.$i18n('pickuplist.error_confirm') + e)
      }
      this.reload()
    },
    async setSlots (date, totalSlots, description) {
      this.isLoading = true
      try {
        await setPickupSlots(this.storeId, date, totalSlots, description)
      } catch (e) {
        pulseError(this.$i18n('pickuplist.error_changeSlotCount') + e)
      }
      this.reload()
    },
    async sendTeamMessage (msg) {
      try {
        await sendMessage(this.teamConversationId, msg)
        pulseSuccess(this.$i18n('pickup.team_message_success'))
      } catch (e) {
        console.error(e)
        pulseError(this.$i18n('pickuplist.error_whileSending'))
      }
    },
    async editDescription (date, totalSlots, description) {
      this.isLoading = true
      try {
        await setPickupSlots(this.storeId, date, totalSlots, description)
      } catch (e) {
        pulseError(this.$i18n('pickuplist.error_changeSlotCount') + e)
      }
      this.reload()
    },
    loadAddPickupModal () {
      ajreq(
        'adddate',
        {
          app: 'betrieb',
          id: this.storeId,
        },
      )
    },
  },
}
</script>

<style lang="scss" scoped>
.pickup-list {
  padding: 10px;

  .pickup-block:last-child {
    margin-bottom: -10px;
  }
}

.btn-group.slot-actions {
  // counter the .card definition of padding: 6px 8px;
  margin: -6px -8px;

  button {
    line-height: 21px;
    padding: 5px 10px;
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
  }

  i.fas {
    font-size: 14px;
  }
}
</style>
