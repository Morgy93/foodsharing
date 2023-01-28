<template>
  <div class="card rounded">
    <div class="card-header text-white bg-primary">
      {{ $i18n('login.form_title') }}
    </div>
    <LoginForm />
  </div>
</template>

<script>
import LoginForm from '@/components/Login/LoginForm.vue'

export default {
  components: { LoginForm },
}
</script>

<style lang="scss" scoped>
.card.rounded {
    max-width: 500px;
    margin: auto;
    box-shadow: var(--fs-shadow);
  }
</style>
