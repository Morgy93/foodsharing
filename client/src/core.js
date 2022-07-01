import 'whatwg-fetch'

import '@/sentry'

import '@/style'

import $ from 'jquery'
import 'jquery-migrate'

import { initialize } from '@/script'

import 'jquery-ui'
import registerServiceWorker from '@/registerServiceWorker'

/*
  Loads a lot of CSS stylings
*/
import './scss/index.scss'

import '@/becomeBezirk'
import '@/views/views'

import serverData from '@/server-data'

import socket from '@/socket'
import { getCsrfToken } from '@/api/base'

initialize()
registerServiceWorker()

if (serverData.user.may) {
  socket.connect()
}

// add CSRF-Token to all jquery requests
$.ajaxPrefilter(function (options) {
  if (!options.beforeSend) {
    options.beforeSend = function (xhr, settings) {
      if (settings.url.startsWith('/') && !settings.url.startsWith('//')) {
        xhr.setRequestHeader('X-CSRF-Token', getCsrfToken())
      } else {
        // don't send for external domains (must be a relative url)
      }
    }
  }
})
