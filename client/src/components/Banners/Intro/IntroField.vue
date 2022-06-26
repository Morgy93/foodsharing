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
        :url="user.photo"
        :is-sleeping="user.sleeping"
        :size="50"
        style="max-width: 50px;"
      />
    </a>
    <div class="ml-3 d-flex flex-column">
      <h1 v-html="$i18n('dashboard.greeting', {name: user.firstname})" />
      <p
        v-if="!isFoodsaver || !user.regionName"
        class="mb-0"
        v-html="$i18n('dashboard.foodsharer')"
      />
      <p
        v-else-if="stats.count > 0 && stats.weight > 0"
        class="mb-0"
        v-html="$i18n('dashboard.foodsaver_amount', {pickups: stats.count, weight: stats.weight})"
      />
      <p
        v-else
        class="mb-0"
        v-html="$i18n('dashboard.homeRegion', {region: user.regionName})"
      />
    </div>
  </div>
</template>

<script>
// Stores
import { getters } from '@/stores/user'
// Components
import Avatar from '@/components/Avatar'

export default {
  name: 'IntroField',
  components: {
    Avatar,
  },
  props: {
    title: { type: String, default: 'dashboard.my.regions' },
  },
  computed: {
    user () {
      return getters.getUser()
    },
    stats () {
      return getters.getStats()
    },
    isFoodsaver () {
      return getters.isFoodsaver()
    },
  },
}
</script>
