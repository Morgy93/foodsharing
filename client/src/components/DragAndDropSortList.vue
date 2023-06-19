<template>
  <div>
    <div
      v-for="(item, key) in items"
      :key="key"
    >
      <slot
        name="item"
        :item="item"
        draggable="true"
        @dragstart="startPosition = key"
        @drop="reposition(key)"
        @dragover.prevent
      />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Array,
      required: true,
    },
  },
  data () {
    return {
      startPosition: null,
    }
  },
  computed: {
    items: {
      get () {
        return this.value
      },
      set (value) {
        this.$emit('input', value)
      },
    },
  },
  methods: {
    reposition (dropPosition) {
      console.log(`drop ${this.startPosition} onto ${dropPosition}`)
      // console.log('before:', this.items.map(item => item))
      // reposition
      const item = this.items.splice(this.startPosition, 1)[0]
      this.items.splice(dropPosition, 0, item)
      // console.log('after:', this.items.map(item => item))
    },
  },
}
</script>
