<template>
  <div>
    <div
      v-if="loading"
      class="loader-container mx-auto"
    >
      <i class="fas fa-spinner fa-spin" />
    </div>
    <div class="card mb-3 rounded">
      <h3>{{ name }}</h3>
      <Markdown :source="description" />
    </div>
  </div>
</template>

<script>
import Markdown from '@/components/Markdown/Markdown'
import { getCommunityBubbleContent } from '@/api/map'
import { pulseError } from '@/script'

export default {
  components: { Markdown },
  props: {
    regionId: { type: Number, required: true },
  },
  data () {
    return {
      loading: true,
      name: '',
      description: '',
    }
  },
  async mounted () {
    this.loading = true
    try {
      const bubbleData = await getCommunityBubbleContent(this.regionId)
      this.regionName = bubbleData.name
      this.description = bubbleData.description
    } catch (e) {
      pulseError(this.$i18n('error_unexpected'))
    }
    this.loading = false
  },
}
</script>
