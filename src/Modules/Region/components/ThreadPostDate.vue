<template>
  <div :class="classes">
    <a
      :href="link"
      @click.prevent="$emit('scroll')"
    >
      <small>
        {{ $dateFormatter.base(date) }}
      </small>
    </a>
  </div>
</template>

<script>

export default {
  props: {
    date: { type: Date, default: null },
    link: { type: String, default: '' },
    classes: { type: String, default: '' },
  },
}
</script>

<style lang="scss" scoped>
</style>
