export default {
  data () {
    return {
      scrollPosition: { x: 0, y: 0 },
    }
  },
  methods: {
    debounce (fn, delay) {
      let timeoutID = null
      return function () {
        clearTimeout(timeoutID)
        const args = arguments
        const that = this
        timeoutID = setTimeout(function () {
          fn.apply(that, args)
        }, delay)
      }
    },
    _scrollListener () {
      this.scrollPosition = {
        x: Math.round(window.pageXOffset),
        y: Math.round(window.pageYOffset),
      }
    },
  },
  created () {
    // Call listener once to detect initial position
    this._scrollListener()
    // When scrolling, update the position
    window.addEventListener('scroll', this._scrollListener)
  },
  beforeDestroy () {
    // Detach the listener when the component is gone
    window.removeEventListener('scroll', this._scrollListener)
  },
}
