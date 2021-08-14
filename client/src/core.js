import 'whatwg-fetch'

import '@/sentry'

import '@/style'

import $ from 'jquery'
import 'jquery-migrate'

import { initialize } from '@/script'

import 'jquery-ui'
import registerServiceWorker from '@/registerServiceWorker'
import './scss/bootstrap-theme.scss'
import './scss/index.scss'

import '@/menu'
import '@/becomeBezirk'
import '@/footer'

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

let deferredPrompt
const addBtn = document.querySelector('.pwa-install-btn')
addBtn.style.display = 'none'

window.addEventListener('beforeinstallprompt', (e) => {
  // Prevent Chrome 67 and earlier from automatically showing the prompt
  // e.preventDefault()
  // Stash the event so it can be triggered later.
  deferredPrompt = e
  // Update UI to notify the user they can add to home screen
  if (getMobileOperatingSystem() === 'Android') {
  // if (getMobileOperatingSystem() === 'unknown') {
  // if (getMobileOperatingSystem() === 'iOS') {
    addBtn.style.display = 'block'

    addBtn.addEventListener('click', () => {
      // hide our user interface that shows our A2HS button
      addBtn.style.display = 'none'
      // Show the prompt
      deferredPrompt.prompt()
      // Wait for the user to respond to the prompt
      deferredPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
          deferredPrompt = null
        }
      })
    })
  }
})

// Determine the mobile operating system.
function getMobileOperatingSystem () {
  var userAgent = navigator.userAgent || navigator.vendor || window.opera

  // Windows Phone must come first because its UA also contains "Android"
  if (/windows phone/i.test(userAgent)) {
    return 'Windows Phone'
  }

  if (/android/i.test(userAgent)) {
    return 'Android'
  }

  // iOS detection from: http://stackoverflow.com/a/9039885/177710
  if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
    return 'iOS'
  }

  return 'unknown'
}
