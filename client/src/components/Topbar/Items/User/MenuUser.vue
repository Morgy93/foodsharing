<template>
  <fs-dropdown-menu
    v-b-tooltip="$i18n('menu.entry.your_account')"
    no-caret
    class="user"
    right
    menu-title="menu.entry.your_account"
  >
    <template #heading-icon>
      <img
        width="22px"
        :src="avatar"
        class="rounded-circle border border-primary mr-2"
      >
    </template>
    <template #content>
      <a
        v-if="may.administrateBlog"
        :href="$url('blogList')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-newspaper" /> {{ $i18n('menu.blog') }}
      </a>
      <a
        v-if="may.editQuiz"
        :href="$url('quizEdit')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-question-circle" /> {{ $i18n('menu.quiz') }}
      </a>
      <a
        v-if="may.handleReports"
        :href="$url('reports')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-exclamation" /> {{ $i18n('menu.reports') }}
      </a>
      <a
        v-if="may.administrateRegions"
        :href="$url('region')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-map" /> {{ $i18n('menu.manage_regions') }}
      </a>
      <a
        v-if="may.administrateNewsletterEmail"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-envelope" /> {{ $i18n('menu.email') }}
      </a>
      <a
        v-if="may.manageMailboxes"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-envelope" /> {{ $i18n('menu.manage_mailboxes') }}
      </a>
      <a
        v-if="may.editContent"
        :href="$url('contentEdit')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-file-alt" /> {{ $i18n('menu.content') }}
      </a>
      <div
        v-if="hasAdminRights()"
        class="dropdown-divider"
      />
      <a
        :href="$url('profile', userId)"
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
      <div class="dropdown-divider" />
      <a
        :href="$url('logout')"
        role="menuitem"
        class="dropdown-item"
      >
        <i class="fas fa-power-off" /> {{ $i18n('login.logout') }}
      </a>
    </template>
    <template #actions>
      <language-chooser ref="languageChooser" />
    </template>
  </fs-dropdown-menu>
</template>
<script>
import FsDropdownMenu from '../FsDropdownMenu'
import LanguageChooser from './LanguageChooser'

export default {
  components: { LanguageChooser, FsDropdownMenu },
  props: {
    userId: {
      type: Number,
      default: null,
    },
    avatar: {
      type: String,
      default: null,
    },
    may: {
      type: Object,
      default: () => {},
    },
    showTitle: { type: Boolean, default: false },
  },
  methods: {
    hasAdminRights () {
      return this.may.administrateBlog || this.may.editQuiz || this.may.handleReports || this.may.editContent || this.may.manageMailboxes || this.may.administrateNewsletterEmail || this.may.administrateRegions
    },
  },
}
</script>
