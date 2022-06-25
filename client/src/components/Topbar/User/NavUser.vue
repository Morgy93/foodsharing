<template>
  <Dropdown
    title="User"
    direction="right"
  >
    <template #icon>
      <Avatar :size="16" />
    </template>
    <template #content>
      <a
        v-if="isBeta"
        :href="$url('releaseNotes')"
        role="menuitem"
        class="dropdown-item dropdown-action list-group-item-warning"
      >
        <i class="fas fa-info-circle" /> {{ $i18n('menu.entry.release-notes') }}
      </a>
      <a
        v-if="isBeta || isDev"
        :href="$url('changelog')"
        role="menuitem"
        class="dropdown-item dropdown-action list-group-item-danger"
      >
        <i class="fas fa-info-circle" /> {{ $i18n('content.changelog') }}
      </a>
      <div
        v-if="isBeta || isDev"
        class="dropdown-divider"
      />
      <a
        v-if="hasMailBox"
        :title="$i18n('menu.entry.mailbox')"
        :href="$url('mailbox')"
        role="menuitem"
        class="dropdown-item dropdown-action position-relative"
      >
        <div class="badge badge-danger badge-user-inline">{{ getUnreadCount }}</div>
        <i class="fas fa-envelope" />
        {{ $i18n('menu.entry.mailbox') }}
      </a>
      <div
        v-if="hasMailBox"
        class="dropdown-divider"
      />
      <a
        :href="$url('profile', getUserId)"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-address-card" /> {{ $i18n('profile.title') }}
      </a>
      <a
        :href="$url('settings')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-cog" /> {{ $i18n('settings.header') }}
      </a>
      <div class="dropdown-divider" />
      <button
        role="menuitem"
        class="dropdown-item dropdown-action"
        @click.prevent="showLanguageChooser()"
      >
        <i class="fas fa-language" /> {{ $i18n('menu.entry.language') }}
      </button>
    </template>
    <template #actions>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </template>
  </Dropdown>
</template>
<script>
// Stores
import DataUser from '@/stores/user'
import DataLanguageChooser from '@/stores/languageChooser'
// Components
import Avatar from '../../Avatar.vue'
import Dropdown from '../_NavItems/NavDropdown'
// Mixins
import TopBarMixin from '@/mixins/TopBarMixin'
import RouteCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'

export default {
  components: {
    Avatar,
    Dropdown,
  },
  mixins: [TopBarMixin, RouteCheckMixin],
  computed: {
    getUserId () {
      return DataUser.getters.getUser()?.id
    },
    permissions () {
      return DataUser.getters.getPermissions()
    },
    hasPermissions () {
      return DataUser.getters.hasPermissions()
    },
  },
  methods: {
    showLanguageChooser () {
      DataLanguageChooser.mutations.show()
    },
  },
}
</script>
<style lang="scss" scoped>
::v-deep.user-menu .badge {
  top: 7px;
  left: 1.8rem;
}
</style>
