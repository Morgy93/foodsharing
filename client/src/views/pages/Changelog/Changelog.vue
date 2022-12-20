<template>
  <div class="bootstrap">
    <div class="page-container page-simple">
      <h1> {{ $i18n('content.changelog') }} </h1>
      <ChangelogList />
      <hr>
      <div v-html="oldChangelog" />
    </div>
  </div>
</template>

<script>
import ChangelogList from '@/components/Changelog/ChangelogList.vue'

export default {
  name: 'Changelog',
  components: {
    ChangelogList,
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
