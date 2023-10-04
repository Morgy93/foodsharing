<template>
  <a
    :href="$url('profile', user.id)"
    class="d-flex dropdown-item text-truncate search-result"
  >
    <Avatar
      class="mr-2"
      :size="35"
      :url="user.photo"
    />
    <div class="text-truncate">
      <h6 class="m-0 text-truncate d-inline">
        {{ user.name }} {{ user.last_name }}
      </h6>
      <small>ID: {{ user.id }}</small>
      <br>
      <small>
        <a
          v-if="user.region_id"
          :href="$url('forum', user.region_id)"
        >
          {{ user.region_name }}
        </a>
        <i v-else>Kein Stammbezirk</i>
      </small>
    </div>
  </a>
</template>
<script>
import Avatar from '@/components/Avatar.vue'
export default {
  components: { Avatar },
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
}
</script>
