<template>
  <a
    :href="$url('forumThread', thread.region_id, thread.id)"
    class="d-flex dropdown-item search-result"
  >
    <div class="flex-grow-1">
      <h6 class="m-0 text-truncate d-inline">
        <i
          v-if="thread.sticky"
          v-b-tooltip.noninteractive="'Thema ist angeheftet.'"
          class="fas fa-thumbtack"
        />
        <i
          v-if="thread.closed"
          v-b-tooltip.noninteractive="'Thema ist geschlossen.'"
          :class="{'ml-1': thread.sticky}"
          class="fas fa-lock"
        />
        {{ thread.name }}
      </h6>
      <br>
      <small class="separate">
        <span v-if="thread.region_id">
          im
          <a :href="$url('forum', thread.region_id)">
            {{ thread.is_ambassador_forum ? 'BOT-' : '' }}Forum
            {{ thread.region_name }}
          </a>
        </span>
        <span>
          letzter Beitrag {{ $dateFormatter.relativeTime(new Date(thread.time)) }}
        </span>
      </small>
    </div>
  </a>
</template>
<script>
import DataUser from '@/stores/user'

export default {
  props: {
    thread: {
      type: Object,
      required: true,
    },
  },
  computed: {
    is_ambassador () {
      // eslint-disable-next-line eqeqeq
      return this.region.ambassadors.includes(ambassador => ambassador.id == DataUser.getters.getUserId())
    },
    is_home () {
      return this.region.id === DataUser.getters.getHomeRegion()
    },
  },
}
</script>

<style lang="scss" scoped>
.separate>*:not(:last-child)::after {
  content: '•';
}
</style>
