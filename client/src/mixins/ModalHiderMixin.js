let observer = null

export default {
  data () {
    return {
      isVisible: false,
    }
  },
  mounted () {
    observer = new MutationObserver((o) => {
      const wasHidden = o[0].oldValue === 'true'
      if (wasHidden) {
        this.isVisible = wasHidden
      } else {
        // delay to allow modal to be transitioned out
        setTimeout(() => {
          this.isVisible = wasHidden
        }, 1000)
      }
    })

    observer.observe(this.$el, {
      attributes: true,
      attributeOldValue: true,
      attributeFilter: ['aria-hidden'],
    })
  },
  beforeDestroy () {
    observer.disconnect()
  },
}
