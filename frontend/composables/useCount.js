const state = 'counter';

export default {
  _raw: function () {
    return useState(state, () => 1)
  },

  increase: function () {
    return useState(state).value++
  },

  clear: function () {
    return useState(state).value = 0
  },
}
