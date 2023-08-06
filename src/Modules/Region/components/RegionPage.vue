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
        <RegionSideNav />
      </div>
      <div class="col-8">
        <ThreadList
          subforum-id="subFormId"
          group-id="regionOrGroupId"
        />
      </div>
    </div>
  </div>
</template>

<script>
import RegionTop from './RegionTop.vue'
import RegionSideNav from './RegionSideNav.vue'
import ThreadList from './ThreadList.vue'

export default {
  components: { RegionTop, RegionSideNav, ThreadList },
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
  },
}
</script>
