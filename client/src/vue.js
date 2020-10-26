import Vue from 'vue'
import i18n from '@/i18n'
import { url } from '@/urls'
import { dateFormat, dateDistanceInWords } from '@/utils'
import BootstrapVue from 'bootstrap-vue'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import Vuelidate from 'vuelidate'

Vue.use(BootstrapVue)
Vue.use(Vuelidate)

Vue.prototype.$i18n = (key, variables = {}) => {
  return i18n(key, variables)
}
Vue.prototype.$url = url
Vue.prototype.$dateFormat = dateFormat
Vue.prototype.$dateDistanceInWords = dateDistanceInWords

export function vueRegister (components) {
  for (const key in components) {
    Vue.component(key, components[key])
  }
}

export function vueApply (selector, disableElNotFoundException = false) {
  let elements = document.querySelectorAll(selector)

  // querySelectorAll().forEach() is broken in iOS 9
  elements = Array.from(elements)

  if (!elements.length) {
    if (disableElNotFoundException) {
      return
    }
    throw new Error(`vueUse-Error: no elements were found with selector '${selector}'`)
  }
  elements.forEach((el, index) => {
    const componentName = el.getAttribute('data-vue-component')
    const props = JSON.parse(el.getAttribute('data-vue-props')) || {}
    const initialData = JSON.parse(el.getAttribute('vue-initial-data')) || {}

    if (!componentName) {
      throw new Error('vueUse-Error: missing component name. pass it as <div data-vue-component="my-component" />')
    }

    // eslint-disable-next-line no-new
    const vm = new Vue({
      el,
      render (h) {
        return h(componentName, { props })
      },
    })
    if (initialData && typeof initialData === 'object') {
      for (const key in initialData) {
        if (typeof vm.$children[0][key] === 'undefined' || typeof vm.$children[0][key] === 'function') {
          throw new Error(`vueUse() Error: prop '${key}' needs to be defined in data()`)
        }
        vm.$children[0][key] = initialData[key]
      }
    }
  })
}
