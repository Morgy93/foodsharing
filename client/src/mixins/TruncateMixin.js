export default {
  methods: {
    truncate (str, maxLength = 30) {
      if (str.length > maxLength) {
        return str.substring(0, maxLength) + '...'
      }
      return str
    },
  },
}
