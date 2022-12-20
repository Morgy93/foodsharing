<template>
  <div class="bootstrap">
    <div class="page-container page-simple">
      <h1> {{ $i18n('content.changelog') }} </h1>

      <hr>

      <h1>Changelog bis 2022</h1>

      <div v-html="oldChangelog" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'Changelog',
  components: {
  },
  props: {
    oldChangelog: { type: String, default: '' },
  },
  data () {
    return {

    }
  },
}
</script>

<style scoped>

</style>
