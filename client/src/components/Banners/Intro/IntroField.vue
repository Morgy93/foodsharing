<template>
  <div
    v-if="user"
    class="introfield"
  >
    <a
      v-if="user"
      :href="$url('profile', user.id)"
      class="introfield__avatar"
    >
      <Avatar
        :url="getAvatar"
        :is-sleeping="isSleeping"
        :size="50"
        style="max-width: 50px;"
      />
    </a>
    <div class="introfield__content">
      <h3
        class="introfield__title testing-intro-field"
        v-text="viewIsMD ? $i18n('dashboard.greeting', {name: user.firstname}) : $i18n('dashboard.greeting_short', {name: user.firstname})"
      />
      <p
        v-if="!isFoodsaver && !getHomeRegionName"
        class="introfield__description"
        v-text="$i18n('dashboard.foodsharer')"
      />
      <p
        v-if="!getHomeRegionName && stats.count > 0 && stats.weight > 0"
        class="introfield__description"
        v-html="$i18n('dashboard.foodsaver_amount', {pickups: stats.count, weight: stats.weight})"
      />
      <p
        v-if="getHomeRegionName && stats.count > 0 && stats.weight > 0"
        class="introfield__description"
        v-html="$i18n('dashboard.full_subline', {pickups: stats.count, weight: stats.weight, region: getHomeRegionName})"
      />
      <p
        v-else-if="isFoodsaver && getHomeRegionName"
        class="introfield__description"
        v-text="$i18n('dashboard.homeRegion', {region: getHomeRegionName})"
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

<style lang="scss" scoped>
@import "@/scss/bootstrap-theme.scss";

.introfield {
  @extend .alert;

  color: var(--fs-color-primary-500);
  background-color: var(--fs-color-primary-200);
  border-color: var(--fs-color-primary-300);

  display: flex;
  align-items: center;
}

.introfield__avatar {
  @extend .img-thumbnail;
  margin-right: 1rem;
}

.introfield__title {
  margin-top: 0;
  margin-bottom: .25rem;
}

.introfield__description {
  margin-bottom: 0;
  @media (max-width: 576px) {
    display: none;
  }
}

.errorfield__link {
  @extend .btn;
  @extend .btn-sm;
  @extend .btn-danger;

  font-weight: 600;

  &:not(:last-child) {
    margin-right: .5rem;
  }
}
</style>
