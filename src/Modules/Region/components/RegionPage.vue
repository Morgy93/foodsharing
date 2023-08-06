<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <RegionTop
          :name="name"
          :food-saver-count="foodSaverCount"
          :food-saver-home-district-count="foodSaverHomeDistrictCount"
          :food-saver-has-sleeping-hat-count="foodSaverHasSleepingHatCount"
          :ambassador-count="ambassadorCount"
          :stores-count="storesCount"
          :stores-cooperation-count="storesCooperationCount"
          :stores-pickups-count="storesPickupsCount"
          :stores-fetched-weight="storesFetchedWeight"
        />
      </div>
    </div>
    <div class="row">
      <div class="col-4">
        <RegionSideNav :region-id="regionId" />
        <ResponsibleUsers
          :responsible-users="admins"
          :title="$i18n('terminology.ambassadors')"
        />
        <ResponsibleUsers
          :responsible-users="welcomeAdmins"
          :title="$i18n('terminology.welcomeAdmins')"
        />
      </div>
      <div class="col-8">
        <ThreadList
          v-if="activeSubpage === 'forum'"
          :subforum-id="subForumId"
          :group-id="regionId"
        />
        <b-button
          v-if="activeSubpage === 'events'"
          variant="primary"
          :href="$url('addEvents', regionId)"
        >
          Jetzt neuen Termin eintragen
        </b-button>
        <EventList
          v-if="activeSubpage === 'events'"
          :region-id="regionId"
        />
        <FoodSharePointsList
          v-if="activeSubpage === 'fairteiler'"
          :region-name="name"
          :region-id="regionId"
        />
        <PollList
          v-if="activeSubpage === 'polls'"
          :region-id="regionId"
        />
        <MemberList
          v-if="activeSubpage === 'members'"
          :group-id="regionId"
        />
        <Options
          v-if="activeSubpage === 'options'"
          :region-id="regionId"
        />
        <Statistics v-if="activeSubpage === 'statistic'" />
        <Pin v-if="activeSubpage === 'pin'" />
      </div>
    </div>
  </div>
</template>

<script>
import RegionTop from './RegionTop.vue'
import RegionSideNav from './RegionSideNav.vue'
import ThreadList from './ThreadList.vue'
import EventList from '../../Event/components/EventList.vue'
import FoodSharePointsList from './FoodSharePointsList.vue'
import PollList from './PollList.vue'
import MemberList from './MemberList.vue'
import Options from './Options.vue'
import Statistics from './Statistics.vue'
import Pin from './Pin.vue'
import ResponsibleUsers from './ResponsibleUsers.vue'

export default {
  components: { ResponsibleUsers, Statistics, Options, MemberList, PollList, FoodSharePointsList, RegionTop, RegionSideNav, ThreadList, EventList, Pin },
  props: {
    regionId: { type: Number, required: true },
    name: { type: String, required: true },
    isWorkGroup: { type: Boolean, required: true },
    isHomeDistrict: { type: Boolean, required: true },
    storesAndMembersDisabled: { type: Array, required: true },
    isRegion: { type: Boolean, required: true },
    foodSaverCount: { type: Number, required: true },
    foodSaverHomeDistrictCount: { type: Number, required: true },
    foodSaverHasSleepingHatCount: { type: Number, required: true },
    ambassadorCount: { type: Number, required: true },
    storesCount: { type: Number, required: true },
    storesCooperationCount: { type: Number, required: true },
    storesPickupsCount: { type: Number, required: true },
    storesFetchedWeight: { type: Number, required: true },
    activeSubpage: { type: String, required: true },
    admins: { type: Array, required: true },
    welcomeAdmins: { type: Array, required: true },
  },
  computed: {
    subForumId () {
      return this.activeSubpage === 'botforum' ? 1 : 0
    },
  },
}
</script>
