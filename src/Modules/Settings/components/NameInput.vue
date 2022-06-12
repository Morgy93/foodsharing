
<template>
  <div
    class="input-wrapper"
  >
    <label
      class="wrapper-label ui-widget"
    >{{ $i18n('settings.name_change.title') }}</label>
    <div
      v-b-modal.name-change-info-modal
      class="desc"
      v-html="$i18n('settings.name_change.desc', {link:'href=\'#\''})"
    />
    <div class="element-wrapper">
      <input
        id="name"
        class="input text value"
        type="text"
        name="name"
        :value="name"
        disabled="disabled"
      >
      <input
        id="nachname"
        class="input text value"
        type="text"
        name="nachname"
        :value="lastName"
        disabled="disabled"
      >
    </div>
    <div class="clear" />
    <b-modal
      id="name-change-info-modal"
      :title="$i18n('settings.name_change.request')"
      ok-only
      centered
      modal-class="bootstrap"
      content-class="pr-3 pt-3"
      header-class="d-flex"
    >
      <div
        v-if="regionId > 0"
        v-html="$i18n('settings.name_change.foodsaver_info', {link: `href=/?page=bezirk&bid=${regionId}&sub=forum`})"
      />
      <div
        v-else
        v-html="$i18n('settings.name_change.foodsharer_info', { link: `href=${$url('support')}` })"
      />
    </b-modal>
  </div>
</template>

<script setup>
export default {
  props: {
    name: {
      type: String,
      default: '',
    },
    lastName: {
      type: String,
      default: '',
    },
    regionId: {
      type: Number,
      default: 0,
    },
  },

}
</script>

<style lang="scss">
input[disabled]{
  color: var(--fs-light-grey);
  background-color: rgba(var(--fs-light-grey-rgb), 0.2);
}

.element-wrapper{
  --first-input-width: 35%;
  #name{
    width: var(--first-input-width);
  }
  #nachname{
    width: calc(90% - var(--first-input-width) - 1em);
    margin-left: 1em;
    box-sizing: border-box;
  }
}

</style>
