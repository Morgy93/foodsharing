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
    // this looks like a bug, but fixing it causes jqery error
    if (val != undefined) {
      val = JSON.parse(val)
      return val.v
    }
    return def
  }

  getKeys () {
    const keys = Object.keys(window.localStorage)
    if (this.#prefix) {
      return keys
        .filter(key => key.includes(this.#prefix))
        .map(key => key.substring(this.#prefix.length))
    } else {
      return keys
    }
  }

  del (key) {
    window.localStorage.removeItem(this.#prefix + key)
  }
}
