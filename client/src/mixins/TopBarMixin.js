// Stores
import DataUser from '@/stores/user'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  mixins: [MediaQueryMixin],
  props: {
    showTitle: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    user: () => DataUser.getters.getUser(),
    isFoodsaver: () => DataUser.getters.isFoodsaver(),
    hasMailBox: () => DataUser.getters.getMailBox() > 0,
    getUnreadCount: () => DataUser.getters.getMailUnreadCount(),
    isVisibleOnMobile () {
      return !this.viewIsXL
    },
    homeHref () {
      return (this.user) ? this.$url('dashboard') : this.$url('home')
    },
    loginReferrerHref () {
      return this.$url('login') + '&ref=' + encodeURIComponent(this.loginReferrer())
    },
  },
  methods: {
    loginReferrer () {
      const url = new URL(window.location.href)
      const path = url.pathname + url.search
      return (path === '/') ? this.$url('dashboard') : path
    },
  },
}
