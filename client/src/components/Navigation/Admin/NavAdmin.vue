<template>
  <Dropdown
    v-if="hasAdminPermissions"
    :title="$i18n('navigation.system_administration')"
    direction="right"
  >
    <template #content>
      <a
        v-if="permissions.administrateBlog"
        :href="$url('blogList')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-newspaper" /> {{ $i18n('system_administration.blog') }}
      </a>
      <a
        v-if="permissions.editQuiz"
        :href="$url('quiz_admin_edit')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-question-circle" /> {{ $i18n('system_administration.quiz') }}
      </a>
      <a
        v-if="permissions.handleReports"
        :href="$url('reports')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-exclamation" /> {{ $i18n('system_administration.reports') }}
      </a>
      <a
        v-if="permissions.administrateRegions"
        :href="$url('region')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-map" /> {{ $i18n('system_administration.regions') }}
      </a>
      <a
        v-if="permissions.administrateNewsletterEmail"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-envelope" /> {{ $i18n('system_administration.email') }}
      </a>
      <a
        v-if="permissions.manageMailboxes"
        :href="$url('email')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-envelope" /> {{ $i18n('system_administration.mailboxes') }}
      </a>
      <a
        v-if="permissions.editContent"
        :href="$url('contentEdit')"
        role="menuitem"
        class="dropdown-item dropdown-action"
      >
        <i class="icon-subnav fas fa-file-alt" /> {{ $i18n('system_administration.content') }}
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
