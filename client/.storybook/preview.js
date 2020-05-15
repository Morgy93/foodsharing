import { addDecorator } from '@storybook/vue';
import { withA11y } from '@storybook/addon-a11y';

addDecorator(withA11y);

import Vue from 'vue'
import i18n from '@/i18n'
import { url } from '@/urls'
//import { dateFormat, dateDistanceInWords } from '@/utils'
import BootstrapVue from 'bootstrap-vue'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import Vuelidate from 'vuelidate'


import '@/style'

Vue.use(BootstrapVue)
Vue.use(Vuelidate)

//Vue.filter('dateFormat', dateFormat)
//Vue.filter('dateDistanceInWords', dateDistanceInWords)

Vue.filter('i18n', (key, variables = {}) => {
  console.warn('i18n as a vue filter is deprecated. use i18n() as a vue functions')
  return i18n(key, variables)
})

Vue.prototype.$i18n = (key, variables = {}) => {
  return i18n(key, variables)
}

//Vue.prototype.$dateFormat = dateFormat

Vue.prototype.$url = url
