// mostly copied from https://github.com/jofftiquez/vue-media-query-mixin
const mediaQuery = {
  xs: {
    max: 575,
  },
  sm: {
    min: 576,
    max: 767,
  },
  md: {
    min: 768,
    max: 991,
  },
  lg: {
    min: 992,
    max: 1999,
  },
  xl: {
    min: 1200,
  },
}

export default {
  data: function () {
    return {
      windowWidth: 0,
      wXS: false,
      wSM: false,
      wMD: false,
      wLG: false,
      wXL: false,
    }
  },
  mounted () {
    this.$nextTick(function () {
      window.addEventListener('resize', this.getWindowWidth)
      this.getWindowWidth()
    })
  },
  // https://getbootstrap.com/docs/5.0/layout/breakpoints/
  computed: {
    viewIsXS () {
      return this.windowWidth < 576
    },
    viewIsSM () {
      return this.windowWidth >= 576
    },
    viewIsMD () {
      return this.windowWidth >= 768
    },
    viewIsLG () {
      return this.windowWidth >= 992
    },
    viewIsXL () {
      return this.windowWidth >= 1200
    },
    viewIsXXL () {
      return this.windowWidth >= 1400
    },
  },
  methods: {
    getWindowWidth () {
      const w = window.innerWidth
      this.windowWidth = w
      this.wXS = w <= mediaQuery.xs.max
      this.wSM = w >= mediaQuery.sm.min && w <= mediaQuery.sm.max
      this.wMD = w >= mediaQuery.md.min && w <= mediaQuery.md.max
      this.wLG = w >= mediaQuery.lg.min && w <= mediaQuery.lg.max
      this.wXL = w >= mediaQuery.xl.min
    },
  },
  beforeDestroy () {
    window.removeEventListener('resize', this.getWindowWidth)
  },
}
