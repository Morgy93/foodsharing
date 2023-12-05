<template>
  <b-button
    variant="primary"
    class="jump-btn"
    :class="{
      'up': scrollDirection === -1,
      'down': scrollDirection === 1,
      hidden,
      isAtTop,
      isAtBottom,
    }"
    @click="clickHandler"
  >
    <i class="fas fa-angle-double-down" />
  </b-button>
</template>

<script>
const SCROLL_PADDING_IN_PX = 100

export default {
  props: {
    elementId: {
      type: String,
      required: true,
    },
  },
  data () {
    return {
      lastScrollHeight: undefined,
      scrollDirection: 1,
      timeoutId: undefined,
      hidden: true,
      isAtTop: false,
      isAtBottom: false,
      element: null,
    }
  },
  async created () {
    addEventListener('scroll', this.scrollHandler)
  },
  methods: {
    scrollHandler () {
      if (!this.lastScrollHeight) {
        this.lastScrollHeight = window.scrollY
        return
      }
      if (!this.element) {
        this.element = document.getElementById(this.elementId)
        if (!this.element) return
      }
      window.clearTimeout(this.timeoutId)
      this.scrollDirection = Math.sign(window.scrollY - this.lastScrollHeight)
      this.hidden = false
      this.lastScrollHeight = window.scrollY
      this.timeoutId = window.setTimeout(() => { this.hidden = true }, 3000)
      this.isAtTop = this.element.getBoundingClientRect().top > SCROLL_PADDING_IN_PX
      this.isAtBottom = window.innerHeight - this.element.getBoundingClientRect().bottom > SCROLL_PADDING_IN_PX
    },
    clickHandler () {
      let position = window.scrollY - window.innerHeight / 2
      if (this.scrollDirection === 1) {
        position += this.element.getBoundingClientRect().bottom
      } else {
        position += this.element.getBoundingClientRect().top
      }
      scrollTo({ top: position, behavior: 'smooth' })
    },
  },
}
</script>

<style lang="scss" scoped>
.jump-btn {
  position: fixed;
  bottom: 50px;
  right: 50px;
  border-radius: 50%;
  box-shadow: 0 0 5px 0 black;
  z-index: 1;
  transition: all .2s;
  &.up {
    transform: rotate(180deg);
  }
  &.hidden:not(:hover),
  &.up.isAtTop,
  &.down.isAtBottom {
    opacity: 0;
    pointer-events: none;
  }
}
</style>
