export default {
  props: {
    list: { type: Array, default: () => [] },
  },
  data () {
    const defaultAmount = 5
    return {
      defaultAmount: defaultAmount,
      amount: defaultAmount,
    }
  },
  computed: {
    filteredList () {
      return this.list.slice(0, this.amount)
    },
  },
  methods: {
    showFullList () {
      this.amount = this.list.length
    },
    reduceList () {
      this.amount = this.defaultAmount
    },
  },
}
