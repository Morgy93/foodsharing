<template>
  <Dropdown
    v-if="hasAdminPermissions"
    title="Admin"
    direction="right"
  >
    <template #content>
      <a
        v-if="permissions.administrateBlog"
        :href="$url('blogList')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="nav-icon fas fa-newspaper" /> {{ $i18n('menu.blog') }}
      </a>
      <a
        v-if="permissions.editQuiz"
        :href="$url('quizEdit')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="nav-icon fas fa-question-circle" /> {{ $i18n('menu.quiz') }}
      </a>
      <a
        v-if="permissions.handleReports"
        :href="$url('reports')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="nav-icon fas fa-exclamation" /> {{ $i18n('menu.reports') }}
      </a>
      <a
        v-if="permissions.administrateRegions"
        :href="$url('region')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="nav-icon fas fa-map" /> {{ $i18n('menu.manage_regions') }}
      </a>
      <a
        v-if="permissions.administrateNewsletterEmail"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="nav-icon fas fa-envelope" /> {{ $i18n('menu.email') }}
      </a>
      <a
        v-if="permissions.manageMailboxes"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="nav-icon fas fa-envelope" /> {{ $i18n('menu.manage_mailboxes') }}
      </a>
      <a
        v-if="permissions.editContent"
        :href="$url('contentEdit')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="nav-icon fas fa-file-alt" /> {{ $i18n('menu.content') }}
      </a>
    </template>
    <template #actions>
      <span
        class="dropdown-item dropdown-action disabled"
        style="user-select: none"
      >
        Special snowflake, HA? ❄️
      </span>
    </template>
  </Dropdown>
</template>
<script>
// Stores
import DataUser from '@/stores/user'
// Components
import Dropdown from '../_NavItems/NavDropdown'
// Mixins
import RouteCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'

export default {
  components: {
    Dropdown,
  },
  mixins: [RouteCheckMixin],
  computed: {
    permissions () {
      return DataUser.getters.getPermissions()
    },
    hasAdminPermissions () {
      return DataUser.getters.hasAdminPermissions()
    },
  },
}
</script>
