<template>
  <div class="nav-item p-3">
    <h3
      v-html="$i18n('login.form_title')"
    />
    <form
      class="mt-3"
      @submit.prevent
    >
      <label class="d-block">
        <div class="mb-1">
          <i class="fas fa-user mr-1" />
          {{ $i18n('login.email_address') }}
        </div>
        <input
          ref="email"
          v-model="email"
          :placeholder="$i18n('login.email_address')"
          :aria-label="$i18n('login.email_address')"
          type="email"
          name="login-email"
          class="form-control"
          autocomplete="email"
          autofocus
          @keydown.enter="submit"
          @focus="focusLogin=true"
        >
      </label>
      <label class="d-block">
        <div class="mb-1">
          <i class="fas fa-key mr-1" />
          {{ $i18n('login.password') }}
        </div>
        <input
          v-model="password"
          :placeholder="$i18n('login.password')"
          :aria-label="$i18n('login.password')"
          type="password"
          name="login-password"
          class="form-control"
          autocomplete="current-password"
          @keydown.enter="submit"
        >
      </label>
      <label class="d-flex align-items-center mt-3 mb-3">
        <input
          v-model="rememberMe"
          class="mr-2"
          type="checkbox"
          name="login-remember"
          @keydown.enter="submit"
        >
        {{ $i18n('login.steady_login') }}
      </label>
      <b-overlay :show="isLoading">
        <template #overlay>
          <i class="fas fa-spinner fa-spin" />
        </template>
        <b-button
          id="login-btn"
          :aria-label="$i18n('login.login_button_label')"
          type="submit"
          secondary
          class="btn btn-block"
          @click="submit"
        >
          <span>
            {{ $i18n('login.submit_btn') }}
          </span>
          <i class="fas fa-arrow-right mr-auto" />
        </b-button>
      </b-overlay>
      <a
        :href="$url('passwordReset')"
        class="d-block mt-3"
        v-html="$i18n('login.forgotten_password_label')"
      />
    </form>
  </div>
</template>

<script>
import { login } from '@/api/user'

import { pulseError, pulseSuccess } from '@/script'
import serverData from '@/server-data'

export default {
  name: 'MenuLogin',
  data () {
    return {
      email: serverData.isDev ? 'userbot@example.com' : '',
      password: serverData.isDev ? 'user' : '',
      rememberMe: false,
      isLoading: false,
      error: null,
      focusLogin: false,
    }
  },
  watch: {
    focusLogin: function (val) {
      if (val) {
        this.$refs.email.focus()
        this.$refs.email.select()
      }
    },
  },
  created () {
    if (localStorage.getItem('login-rememberme')) {
      this.rememberMe = true
    }
  },
  methods: {

    async submit () {
      if (this.rememberMe) {
        localStorage.setItem('login-rememberme', 'true')
      } else {
        localStorage.removeItem('login-rememberme')
      }
      if (!this.email) {
        pulseError(this.$i18n('login.error_no_email'))
        return
      }
      if (!this.password) {
        pulseError(this.$i18n('login.error_no_password'))
        return
      }
      this.isLoading = true
      try {
        const user = await login(this.email, this.password, this.rememberMe)
        pulseSuccess(this.$i18n('login.success', { user_name: user.name }))

        const urlParams = new URLSearchParams(window.location.search)

        if (urlParams.has('ref')) {
          window.location.href = decodeURIComponent(urlParams.get('ref'))
        } else {
          window.location.href = this.$url('dashboard')
        }
      } catch (err) {
        this.isLoading = false
        if (err.code && err.code === 401) {
          pulseError(this.$i18n('login.error_no_auth'))
          setTimeout(() => {
          }, 2000)
        } else {
          pulseError(this.$i18n('error_unexpected'))
          throw err
        }
      }
    },
    focusRef (ref) {
      // Some references may be a component, functional component, or plain element
      // This handles that check before focusing, assuming a `focus()` method exists
      // We do this in a double `$nextTick()` to ensure components have
      // updated & popover positioned first
      this.$nextTick(() => {
        this.$nextTick(() => {
          ;(ref.$el || ref).focus()
        })
      })
    },
  },
}
</script>
