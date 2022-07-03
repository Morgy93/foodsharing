<template>
  <Dropdown
    :title="$i18n('login.topbar')"
    direction="right"
    is-fixed-size
    class="testing-login-dropdown is-open-on-mobile"
  >
    <template #content>
      <LoginEntry />
    </template>
    <template #actions>
      <a
        :href="$url('passwordReset')"
        role="menuitem"
        class="testing-login-click-password-reset dropdown-item dropdown-action"
        v-html="$i18n('login.forgotten_password_label')"
      />
      <a
        :href="$url('joininfo')"
        role="menuitem"
        class="dropdown-item dropdown-action"
        v-html="$i18n('register.topbar')"
      />
    </template>
  </Dropdown>
</template>
<script>
// Components
import Dropdown from '../_NavItems/NavDropdown'
import LoginEntry from './NavLoginEntry'

export default {
  name: 'MenuStores',
  components: { Dropdown, LoginEntry },
}
</script>
<style lang="scss" scoped>
@import '../../../scss/icon-sizes.scss';
</style>
