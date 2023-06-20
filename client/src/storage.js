/* eslint-disable eqeqeq */

export default class Storage {
  #prefix = ''

  constructor (name) {
    this.setPrefix(name)
  }

  setPrefix (prefix) {
    this.#prefix = `${prefix}:`
  }

  set (key, val) {
    val = JSON.stringify({ v: val })
    window.localStorage.setItem(this.#prefix + key, val)
  }

  get (key, def = undefined) {
    let val = window.localStorage.getItem(this.#prefix + key)
    if (val != undefined) {
      val = JSON.parse(val)
      return val.v
    }
    return def
  }

  del (key) {
    window.localStorage.removeItem(this.#prefix + key)
  }
}
