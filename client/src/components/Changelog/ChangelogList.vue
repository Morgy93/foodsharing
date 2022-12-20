<template>
  <p>Here you can see our changes since 2023.</p>
</template>

<script>
import { getChangelog } from '@/api/changelog'

export default {
  name: 'ChangelogList',
  components: {

  },
  async mounted () {
    this.changelog = await getChangelog()
    console.log('CLG', this.changelog)
  },
}
</script>

<style scoped>

</style>
