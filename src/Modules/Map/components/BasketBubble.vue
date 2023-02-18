<template>
  <div>
    <div
      v-if="loading"
      class="loader-container mx-auto"
    >
      <i class="fas fa-spinner fa-spin" />
    </div>
    <div class="card mb-3 rounded">
      <Markdown :source="bubbleData.description" />
    </div>
  </div>
</template>

<script>
import Markdown from '@/components/Markdown/Markdown'
import { getBasketBubbleContent } from '@/api/map'
import { pulseError } from '@/script'

export default {
  components: { Markdown },
  props: {
    basketId: { type: Number, required: true },
  },
  data () {
    return {
      loading: true,
      bubbleData: '',
    }
  },
  async mounted () {
    this.loading = true
    try {
      this.bubbleData = await getBasketBubbleContent(this.basketId)
    } catch (e) {
      pulseError(this.$i18n('error_unexpected'))
    }
    this.loading = false
  },
}
</script>
