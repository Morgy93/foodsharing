export default {
  data: function () {
    return {
      activeRegionId: null,
    }
  },
  mounted () {
    this.setActiveRegion()
  },
  methods: {
    getActiveRegion () {
      return JSON.parse(localStorage.getItem('lastRegion'))
    },
    setActiveRegion (id) {
      id = id || this.getActiveRegion()

      if (id !== this.activeRegionId) {
        this.activeRegionId = id
        localStorage.setItem('lastRegion', JSON.stringify(id))
      } else {
        this.activeRegionId = this.getActiveRegion()
      }
    },
  },
}
