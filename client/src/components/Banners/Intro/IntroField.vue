<template>
  <div
    v-if="user"
    class="intro alert alert-primary d-flex align-items-center"
  >
    <a
      v-if="user"
      :href="$url('profile', user.id)"
      class="img-thumbnail position-relative"
    >
      <Avatar
        :url="getAvatar"
        :is-sleeping="isSleeping"
        :size="50"
        style="max-width: 50px;"
      />
    </a>
    <div class="ml-3 d-flex flex-column">
      <h1
        class="testing-intro-field"
        v-html="viewIsMD ? $i18n('dashboard.greeting', {name: user.firstname}) : $i18n('dashboard.greeting_short', {name: user.firstname})"
      />
      <p
        v-if="!isFoodsaver && !getHomeRegionName"
        class="mb-0"
        v-html="$i18n('dashboard.foodsharer')"
      />
      <p
        v-if="!getHomeRegionName && stats.count > 0 && stats.weight > 0"
        class="mb-0"
        v-html="$i18n('dashboard.foodsaver_amount', {pickups: stats.count, weight: stats.weight})"
      />
      <p
        v-if="getHomeRegionName && stats.count > 0 && stats.weight > 0"
        class="mb-0"
        v-html="$i18n('dashboard.full_subline', {pickups: stats.count, weight: stats.weight, region: getHomeRegionName})"
      />
      <p
        v-else-if="isFoodsaver && getHomeRegionName"
        class="mb-0"
        v-html="$i18n('dashboard.homeRegion', {region: getHomeRegionName})"
      />
    </div>
  </div>
</template>

<script>
// Stores
import { mutations, getters } from '@/stores/user'
// Components
import Avatar from '@/components/Avatar'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  name: 'IntroField',
  components: {
    Avatar,
  },
  mixins: [MediaQueryMixin],
  props: {
    title: { type: String, default: 'dashboard.my.regions' },
  },
  computed: {
    user () {
      return getters.getUser()
    },
    isSleeping () {
      return getters.isSleeping()
    },
    stats () {
      return getters.getStats()
    },
    getHomeRegionName () {
      const regionHome = getters.getHomeRegionName()
      if (regionHome?.length > 0) {
        return regionHome
      }
      return null
    },
    getAvatar () {
      return getters.getAvatar()
    },
    isFoodsaver () {
      return getters.isFoodsaver()
    },
  },
  async mounted () {
    await mutations.fetchDetails()
  },
}
</script>
