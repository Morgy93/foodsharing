<template>
  <div>
    <div
      v-if="loading"
      class="loader-container mx-auto"
    >
      <i class="fas fa-spinner fa-spin" />
    </div>
    <div
      v-else
      class="my-2"
    >
      <div
        v-if="bubbleData.photo"
        class="mb-1"
      >
        <img
          class="basketpicture"
          :src="photoPath"
        >
      </div>

      <div
        v-if="bubbleData.createdAt"
        class="mb-3"
      >
        <div
          class="mb-1 section-label"
        >
          {{ $i18n('basket.date') }}
        </div>
        <div>{{ displayDate }}</div>
      </div>

      <div
        class="mb-1 section-label"
      >
        {{ $i18n('basket.description') }}
      </div>
      <div class="mb-3">
        {{ bubbleData.description }}
      </div>

      <b-button
        variant="primary"
        class="mx-5"
        :href="$url('basket', bubbleData.id)"
      >
        {{ $i18n('basket.go') }}
      </b-button>
    </div>
  </div>
</template>

<script>
import { getBasketBubbleContent } from '@/api/map'
import { pulseError } from '@/script'

export default {
  props: {
    basketId: { type: Number, required: true },
  },
  data () {
    return {
      loading: true,
      bubbleData: '',
    }
  },
  computed: {
    photoPath () {
      return `/images/basket/medium-${this.bubbleData.photo}` ?? null
    },
    displayDate () {
      return this.bubbleData.createdAt
        ? this.$dateFormatter.format(this.bubbleData.createdAt, {
          day: 'numeric',
          weekday: 'long',
          month: 'short',
          hour: 'numeric',
          minute: 'numeric',
        })
        : null
    },
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

<style>
.basketpicture {
  width: 100%;
  overflow: hidden;
}
.section-label {
  color: var(--fs-color-primary-500);
  font-weight: 500;
}
</style>
