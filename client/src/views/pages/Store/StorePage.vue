<template>
  <section
    v-if="storeInformation"
    class="container my-3 my-sm-5"
  >
    <div class="row">
      <div class="col-lg-3">
        <StoreOptions
          :store-name="storeInformation.name"
          :team-conversion-id="permissions.teamConversionId"
          :jumper-conversation-id="permissions.jumperConversationId"
          :may-edit-store="permissions.mayEditStore"
          :is-user-in-store="isUserInStore"
          :may-leave-store-team="permissions.mayLeaveStoreTeam"
          :is-jumper="isJumper"
          :may-do-pickup="permissions.mayDoPickup"
          :fs-id="userId"
          :store-id="storeId"
        />
        <StoreTeam
          :fs-id="userId"
          :may-edit-store="permissions.mayEditStore"
          :team="storeMember"
          :store-id="storeId"
          :store-title="storeInformation.name"
          :region-id="storeInformation.region.id"
        />
      </div>
      <div class="col">
        <div
          v-if="isJumper"
          class="alert alert-info"
          role="alert"
        >
          {{ $i18n('store.willgetcontacted') }}
        </div>
        <div
          v-if="!permissions.mayDoPickup && !isJumper"
          class="alert alert-info"
          role="alert"
        >
          {{ $i18n('store.not_verified') }}
        </div>
        <PickupHistory
          v-if="isCoordinator"
          :store-id="storeId"
          :coop-start="storeInformation.cooperationStart"
        />
        <StoreWall
          :may-do-pickup="permissions.mayDoPickup"
          :is-jumper="isJumper"
          :store-id="storeId"
          :managers="storeManagers"
          :may-write-post="permissions.mayWritePost"
          :may-delete-everything="permissions.mayDeleteEverything"
        />
      </div>
      <div class="col-lg-3">
        <StoreInfos
          :particularities-description="storeInformation.description"
          :weight-type="storeInformation.weight"
          :store-title="storeInformation.name"
          :street="storeInformation.address.street"
          :postcode="storeInformation.address.zipCode"
          :city="storeInformation.address.city"
          :last-fetch-date="lastFetchDate"
          :press="storeInformation.publicity"
          :region-pickup-rules="storeInformation.options.useRegionPickupRule"
          :region-pickup-rule-active="regionPickupRule.regionPickupRuleActive"
          :region-pickup-rule-timespan="regionPickupRule.regionPickupRuleTimespan"
          :region-pickup-rule-limit="regionPickupRule.regionPickupRuleLimit"
          :region-pickup-rule-limit-day="regionPickupRule.regionPickupRuleLimitDay"
          :region-pickup-rule-inactive="regionPickupRule.regionPickupRuleInactive"
        />
        <PickupList
          v-if="!isJumper && permissions.mayDoPickup"
          :may-do-pickup="permissions.mayDoPickup"
          :is-jumper="isJumper"
          :store-id="storeId"
          :is-coordinator="isCoordinator"
        />
      </div>
    </div>
  </section>
</template>

<script>
import StoreOptions from '@/components/Stores/StoreOptions.vue'
import StoreTeam from '@/components/Stores/StoreTeam/StoreTeam.vue'
import StoreInfos from '@/components/Stores/StoreInfos.vue'
import PickupHistory from '@/components/Stores/PickupHistory.vue'
import StoreWall from '@/components/Stores/StoreWall.vue'
import PickupList from '@/components/Stores/PickupList.vue'
import DataUser from '@/stores/user'
import StoreData from '@/stores/stores'

export default {
  components: {
    StoreOptions,
    StoreTeam,
    StoreInfos,
    PickupHistory,
    StoreWall,
    PickupList,
  },
  props: {
    storeId: { type: Number, required: true },
    collectionQuantity: { type: String, default: '' },
    storeManagers: { type: Array, default: () => [] },
  },
  data () {
    return {
      isUserInStore: false,
      lastFetchDate: null,
      isJumper: null,
      isCoordinator: null,
    }
  },
  computed: {
    userId () {
      return DataUser.getters.getUserId()
    },
    storeMember () {
      return StoreData.getters.getStoreMember()
    },
    storeInformation () {
      return StoreData.getters.getStoreInformation()
    },
    permissions () {
      return StoreData.getters.getStorePermissions()
    },
    regionPickupRule () {
      return StoreData.getters.getStoreRegionOptions()
    },
  },
  async mounted () {
    await StoreData.mutations.loadPermissions(this.storeId)
    await StoreData.mutations.loadStoreInformation(this.storeId)
    await StoreData.mutations.loadGetRegionOptions(this.storeInformation.region.id)
    await StoreData.mutations.loadStoreMember(this.storeId)
    this.getIsJumper()
    this.checkIsUserInStore()
    this.getLastFetchDate()
    this.getIsCoordinator()
  },
  methods: {
    getIsJumper () {
      this.isJumper = this.storeMember.some(item => item.id === this.userId && item.team_active === 2)
    },
    getIsCoordinator () {
      this.isCoordinator = this.storeMember.some(item => item.id === this.userId && item.verantwortlich === 1)
    },
    checkIsUserInStore () {
      this.isUserInStore = this.storeMember.some(item => item.id === this.userId)
    },
    getLastFetchDate () {
      const userItem = this.storeMember.find(item => item.id === this.userId)
      if (userItem) {
        const MILLISECONDS_PER_SECOND = 1000
        // Multiplication by 1000 used in this context to convert a Unix timestamp from seconds to milliseconds.
        // JavaScript expects Unix timestamps in milliseconds, while it comes from databases in seconds.
        this.lastFetchDate = userItem?.last_fetch ? new Date(userItem.last_fetch * MILLISECONDS_PER_SECOND) : null
      }
    },
  },
}

</script>
