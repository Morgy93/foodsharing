<template>
  <div class="team-ava">
    <a
      v-b-tooltip.hover="$i18n('pickup.open_profile')"
      :href="`/profile/${user.id}`"
    >
      <Avatar
        :url="user.avatar"
        :size="50"
        class="member-pic"
        :class="{'jumper': user.isJumper}"
        :is-sleeping="user.sleepStatus"
      />
    </a>

    <!-- eslint-disable-next-line vue/max-attributes-per-line -->
    <b-tooltip :target="`fetchcount-${user.id}`" triggers="hover blur">
      <div>
        {{ $i18n('store.fetchCount', {'count': user.fetchCount}) }}
      </div>
      <div v-if="user.mayAmb">
        {{ $i18n('store.mayAmb') }}
      </div>
      <div v-if="user.mayManage">
        {{ $i18n('store.mayManage') }}
      </div>
      <div v-if="user.isJumper">
        {{ $i18n('store.isJumper') }}
      </div>
      <div v-if="!user.isVerified">
        {{ $i18n('store.isNotVerified') }}
      </div>
    </b-tooltip>
    <b-badge
      :id="`fetchcount-${user.id}`"
      class="member-fetchcount"
      :class="{'maysm': user.mayManage, 'waiting': user.isWaiting}"
      tag="span"
    >
      <span v-if="user.isJumper">
        <i class="fas fa-fw fa-star member-jumper" />
      </span>
      <span v-else-if="!user.isVerified">
        <i class="fas fa-fw fa-eye-slash member-unverified" />
      </span>
      <span v-else>{{ user.fetchCount }}</span>
    </b-badge>
  </div>
</template>

<script>
import Avatar from '@/components/Avatar'

export default {
  components: { Avatar },
  props: {
    user: { type: Object, required: true },
  },
  methods: {
  },
}
</script>

<style lang="scss" scoped>
// separate because of loader issues with deep selectors in scoped + nested SCSS
// (see https://github.com/vuejs/vue-loader/issues/913 for a discussion)
.team-ava .member-pic ::v-deep img {
  width: 50px;
}
</style>

<style lang="scss" scoped>
.team-ava {
  --fetchcount-bg: var(--fs-color-primary-200);
  --fetchcount-fg: var(--fs-color-primary-500);
  --fetchcount-border: var(--fs-color-primary-500);
  --role-may-manage-store: var(--fs-color-secondary-500);
  --role-may-ambassador: var(--fs-color-warning-500);
  --role-other: var(--fs-color-primary-200);
}

a {
  display: inline-block;
}

.member-pic.jumper {
  opacity: 0.5;
}

.member-fetchcount {
  position: absolute;
  top: 0;
  right: -10px;
  border: 2px solid var(--fs-color-primary-500);
  min-width: 1.5rem;
  opacity: 0.9;
  background-color: var(--fs-color-primary-300);
  color: var(--fs-color-primary-500);

  &.maysm {
    border-color: var(--fs-color-role-storemanager);
  }
  // &.mayamb {
  //   border-color: var(--fs-color-role-ambassador);
  // }
  &.waiting {
    border-color: var(--fs-color-gray-500);
  }
}
</style>
