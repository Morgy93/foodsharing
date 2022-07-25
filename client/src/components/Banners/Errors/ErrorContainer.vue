<template>
  <div
    class="informations-wrapper position-relative"
    :class="{'multiply': count > 1 && !allVisible, 'single': allVisible}"
  >
    <ErrorFieldMissingRegion />
    <ErrorField
      v-for="(prompt, key) in list"
      :key="key"
      :type="prompt.type"
      :tag="prompt.tag"
      :icon="prompt.icon"
      :title="prompt.title"
      :description="prompt.description"
      :is-time-based="prompt.isTimeBased"
      :time="prompt.time"
      :is-closeable="prompt.isCloseable"
      :links="prompt.links"
      @close="count--"
    />
  </div>
</template>

<script>
import ErrorField from './ErrorField.vue'
import ErrorFieldMissingRegion from './ErrorFieldMissingRegion.vue'

export default {
  components: {
    ErrorField,
    ErrorFieldMissingRegion,
  },
  props: {
    allVisible: { type: Boolean, default: false },
    list: { type: Array, default: () => [] },
  },
  data () {
    return {
      count: this.list.length,
    }
  },
}
</script>

<style lang="scss" scoped>
.informations-wrapper:empty {
  display: none;
}

.single * {
  margin-bottom: 1rem;
}

.multiply {
  margin-bottom: 2rem;
}

.multiply *:nth-child(n+2) {
  display: none !important;
}

.multiply::after {
  background-color: currentColor;
  border-radius: var(--border-radius);
  content: '';
  height: 100%;
  left: 0;
  opacity: 0.15;
  position: absolute;
  top: 0;
  transform: translateY(14px) scale(0.95);
  width: 100%;
  z-index: -1;
}
</style>
