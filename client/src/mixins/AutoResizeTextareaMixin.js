export default {
  methods: {
    resizeTextarea (e) {
      e.target.style.height = 'auto'
      e.target.style.height = `${e.target.scrollHeight}px`
    },
  },
}
