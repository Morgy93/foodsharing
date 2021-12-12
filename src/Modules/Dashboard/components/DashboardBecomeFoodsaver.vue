<template>
  <div>
    <div class="bootstrap ui-widget-content corner-bottom margin-bottom ui-padding">
      <div>{{ $i18n('dashboard.become_foodsaver.salutation') }}</div>
      <br>
      <div>{{ $i18n('dashboard.become_foodsaver.text') }}</div>
      <br>
      <div>
        <b-button
          variant="secondary"
          class="pl-5 pr-5"
          :href="$url('upgradeToFs')"
        >
          {{ $i18n('dashboard.become_foodsaver.button') }}
        </b-button>
      </div>
    </div>
  </div>
</template>

<script>
</script>

<style scoped>

</style>
