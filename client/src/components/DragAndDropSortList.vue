<template>
  <div>
    <div
      v-for="(item, key) in items"
      :key="key"
      @drop="reposition(key)"
      @dragover.prevent="onDragOver"
    >
      <slot
        name="item"
        :item="item"
        :onDragStart="onDragStart.bind(null, key)"
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
      hoverPosition: null,
    }
  },
  computed: {
    items: {
      get () {
        return this.value
      },
      set (value) {
        // console.log('emit something', value)
        this.$emit('input', value)
      },
    },
  },
  methods: {
    onDragStart (startPosition) {
      this.startPosition = startPosition
    },
    onDragOver (hoverPosition) {
      this.hoverPosition = hoverPosition
    },
    reposition (dropPosition) {
      // don't alter the props, instead...
      const items = this.items.concat()
      console.log(`drop ${this.startPosition} onto ${dropPosition}`)
      // console.log('before:', this.items.map(item => item))
      // reposition
      const item = items.splice(this.startPosition, 1)[0]
      items.splice(dropPosition, 0, item)
      // ...emit the changes
      this.items = items
    },
  },
}
</script>
