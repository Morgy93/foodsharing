<template>
  <a
    :href="$url('store', store.id)"
    class="d-flex dropdown-item search-result"
  >
    <div class="text-truncate flex-grow-1">
      <h6 class="m-0 text-truncate d-inline">
        <i
          v-if="store.is_manager"
          v-b-tooltip.noninteractive="$i18n('search.results.store.manager_tooltip')"
          class="fas fa-user-cog"
        />
        <i
          v-else-if="is_member"
          v-b-tooltip.noninteractive="$i18n('search.results.store.member_tooltip')"
          class="fas fa-user-check"
        />
        <i
          v-else-if="is_jumper"
          v-b-tooltip.noninteractive="$i18n('search.results.store.jumper_tooltip')"
          class="fas fa-running"
        />
        {{ store.name }}
      </h6>
      <br>
      <small class="separate">
        <span v-if="store.region_id">
          {{ $i18n('search.results.in') }}
          <a :href="$url('stores', store.region_id)">
            {{ store.region_name }}
          </a>
        </span>
        <span>
          {{ $i18n(`storestatus.${store.cooperation_status}`) }}
        </span>
        <span v-if="store.city">
          <span v-if="store.street">{{ store.street }},</span>
          <span v-if="store.zip">{{ store.zip }}</span>
          {{ store.city }}
        </span>
      </small>
    </div>
  </a>
</template>
<script>

export default {
  components: { },
  props: {
    store: {
      type: Object,
      required: true,
    },
  },
  computed: {
    is_member () {
      return this.store.membership_status === 1
    },
    is_jumper () {
      return this.store.membership_status === 2
    },
  },
}
</script>

<style lang="scss" scoped>
.separate>*:not(:last-child)::after {
  content: '•';
}
</style>
