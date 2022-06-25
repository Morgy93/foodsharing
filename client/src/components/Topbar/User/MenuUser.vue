<template>
  <fs-dropdown-menu
    ref="dropdown"
    title="menu.entry.your_account"
    class="user-menu"
    right
    :badge="viewIsLG ? getUnreadCount : null"
    full-size
  >
    <template #heading-icon>
      <span class="icon img-thumbnail d-inline-flex">
        <Avatar
          :url="user.photo"
          :size="35"
          :is-sleeping="user.sleeping"
          :auto-scale="false"
          style="min-width: 24px;min-height: 24px;max-width: 24px;max-height: 24px;"
        />
      </span>
    </template>
    <template
      #content
    >
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
        v-if="permissions.administrateBlog"
        :href="$url('blogList')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-newspaper" /> {{ $i18n('menu.blog') }}
      </a>
      <a
        v-if="permissions.editQuiz"
        :href="$url('quizEdit')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-question-circle" /> {{ $i18n('menu.quiz') }}
      </a>
      <a
        v-if="permissions.handleReports"
        :href="$url('reports')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-exclamation" /> {{ $i18n('menu.reports') }}
      </a>
      <a
        v-if="permissions.administrateRegions"
        :href="$url('region')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-map" /> {{ $i18n('menu.manage_regions') }}
      </a>
      <a
        v-if="permissions.administrateNewsletterEmail"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-envelope" /> {{ $i18n('menu.email') }}
      </a>
      <a
        v-if="permissions.manageMailboxes"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-envelope" /> {{ $i18n('menu.manage_mailboxes') }}
      </a>
      <a
        v-if="permissions.editContent"
        :href="$url('contentEdit')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="fas fa-file-alt" /> {{ $i18n('menu.content') }}
      </a>
      <div
        v-if="hasPermissions"
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
        :href="$url('profile', user.id)"
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
  </fs-dropdown-menu>
</template>
<script>
// Stores
import DataUser from '@/stores/user'
import DataLanguageChooser from '@/stores/languageChooser'
// Components
import FsDropdownMenu from '../FsDropdownMenu'
import Avatar from '@/components/Avatar'
// Mixins
import TopBarMixin from '@/mixins/TopBarMixin'
import RouteCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'

export default {
  components: { FsDropdownMenu, Avatar },
  mixins: [TopBarMixin, RouteCheckMixin],
  computed: {
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
