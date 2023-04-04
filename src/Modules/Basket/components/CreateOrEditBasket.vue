<template>
  <div class="card mb-3 rounded">
    <div
      class="card-header text-white bg-primary"
    >
      {{ $i18n('basket.add') }}
    </div>
    <div
      class="card-body p-0"
    />
    <div class="row m-2">
      <div class="col col-8">
        <label>Beschreibung</label>
        <b-form-textarea
          id="forum-create-thread-form-body"
          v-model="body"
          rows="6"
        />
        <button
          class="btn btn-primary"
        >
          {{ $i18n('upload.image') }}
        </button>
        <b-form-group
          v-slot="{ ariaDescribedby }"
          label="Individual radios"
        >
          <b-form-radio
            v-model="selected"
            :aria-describedby="ariaDescribedby"
            name="some-radios"
            value="A"
          >
            Option A
          </b-form-radio>
          <b-form-radio
            v-model="selected"
            :aria-describedby="ariaDescribedby"
            name="some-radios"
            value="B"
          >
            Option B
          </b-form-radio>
        </b-form-group>
      </div>
    </div>
  </div>
</template>

<script>
export default {
}
</script>

<style scoped>

</style>
