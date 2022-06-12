<template>
  <fs-dropdown-menu
    title="menu.entry.your_account"
    class="user"
    right
    full-size
  >
    <template #heading-icon>
      <span class="icon img-thumbnail d-inline-flex">
        <Avatar
          :url="user.photo"
          :size="16"
          :auto-scale="false"
          style="min-width: 24px;min-height: 24px;"
        />
      </span>
    </template>
    <template #content>
      <a
        v-if="permissions.administrateBlog"
        :href="$url('blogList')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-newspaper" /> {{ $i18n('menu.blog') }}
      </a>
      <a
        v-if="permissions.editQuiz"
        :href="$url('quizEdit')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-question-circle" /> {{ $i18n('menu.quiz') }}
      </a>
      <a
        v-if="permissions.handleReports"
        :href="$url('reports')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-exclamation" /> {{ $i18n('menu.reports') }}
      </a>
      <a
        v-if="permissions.administrateRegions"
        :href="$url('region')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-map" /> {{ $i18n('menu.manage_regions') }}
      </a>
      <a
        v-if="permissions.administrateNewsletterEmail"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-envelope" /> {{ $i18n('menu.email') }}
      </a>
      <a
        v-if="permissions.manageMailboxes"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-envelope" /> {{ $i18n('menu.manage_mailboxes') }}
      </a>
      <a
        v-if="permissions.editContent"
        :href="$url('contentEdit')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-file-alt" /> {{ $i18n('menu.content') }}
      </a>
      <div
        v-if="hasAdminRights"
        class="dropdown-divider"
      />
      <a
        :href="$url('profile', user.id)"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-address-card" /> {{ $i18n('profile.title') }}
      </a>
      <a
        :href="$url('settings')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-cog" /> {{ $i18n('settings.header') }}
      </a>
      <div class="dropdown-divider" />
      <a
        href="#"
        role="menuitem"
        class="dropdown-item"
        @click="$refs.languageChooser.show()"
      >
        <i class="fas fa-language" /> {{ $i18n('menu.entry.language') }}
      </a>
    </template>
    <template #actions>
      <a
        :href="$url('logout')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
      <language-chooser ref="languageChooser" />
    </template>
  </fs-dropdown-menu>
</template>
<script>
import FsDropdownMenu from '../FsDropdownMenu'
import LanguageChooser from './LanguageChooser'
import Avatar from '@/components/Avatar'

import TopBarMixin from '@/mixins/TopBarMixin'

export default {
  components: { LanguageChooser, FsDropdownMenu, Avatar },
  mixins: [TopBarMixin],
  computed: {
    permissions () {
      return this.user.permissions || {}
    },
    hasAdminRights () {
      return (this.permissions.administrateBlog ||
              this.permissions.editQuiz ||
              this.permissions.handleReports ||
              this.permissions.editContent ||
              this.permissions.manageMailboxes ||
              this.permissions.administrateNewsletterEmail ||
              this.permissions.administrateRegions)
    },
  },
}
</script>
