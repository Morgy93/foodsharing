<template>
  <div>
    <div class="bootstrap ui-widget-content corner-bottom margin-bottom ui-padding">
      <b-container>
        <b-row>
          <div>{{ $i18n('dashboard.become_foodsaver.salutation') }}</div>
        </b-row>
        <b-row
          class="mt-4"
        >
          <div>{{ $i18n('dashboard.become_foodsaver.text') }}</div>
        </b-row>
        <b-row
          class="mt-4 mb-4 justify-content-center"
        >
          <div>
            <b-button
              variant="secondary"
              class="pl-5 pr-5"
              :href="$url('upgradeToFs')"
            >
              {{ $i18n('dashboard.become_foodsaver.button') }}
            </b-button>
          </div>
        </b-row>
      </b-container>
    </div>
  </div>
</template>

<script>
</script>

<style scoped>

</style>
