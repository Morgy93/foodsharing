/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import './Settings.css'
import 'jquery-jcrop'
import 'jquery-dynatree'
import {
  pulseSuccess,
  pulseError,
  collapse_wrapper,
  GET,
} from '@/script'
import { expose } from '@/utils'
import i18n from '@/helper/i18n'
import { subscribeForPushNotifications, unsubscribeFromPushNotifications } from '@/pushNotifications'
import { confirmDeleteUser } from '../Foodsaver/Foodsaver'
import { vueApply, vueRegister } from '@/vue'
import { setSleepStatus } from '@/api/user'
import $ from 'jquery'
import Calendar from './components/Calendar'
import ProfilePicture from './components/ProfilePicture'
import NameInput from './components/NameInput'

if (GET('sub') === 'calendar') {
  vueRegister({
    Calendar,
  })
  vueApply('#calendar')
} else if (GET('sub') === 'general') {
  vueRegister({
    ProfilePicture,
    NameInput,
  })
  vueApply('#image-upload')
  vueApply('#name-input')
}

expose({
  confirmDeleteUser,
  collapse_wrapper,
  trySetSleepMode,
})

// Fill the Push Notifications module with life
refreshPushNotificationSettings()

async function refreshPushNotificationSettings () {
  const pushNotificationsLabel = document.querySelector('#push-notification-label')

  if (!pushNotificationsLabel) {
    return // we seem to be on some settings page that doesn't contain no push notification settings
  }

  const pushNotificationsButton = document.querySelector('#push-notification-button')

  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    pushNotificationsLabel.textContent = i18n('settings.push.not-supported')
    pushNotificationsButton.style.display = 'none'
    return
  }

  if (Notification.permission === 'denied') {
    pushNotificationsLabel.textContent = i18n('settings.push.denied')
    pushNotificationsButton.style.display = 'none'
    return
  }

  const subscription = await (await navigator.serviceWorker.ready).pushManager.getSubscription()
  if (subscription === null) {
    pushNotificationsLabel.textContent = i18n('settings.push.info-on')
    pushNotificationsButton.text = i18n('settings.push.enable')
    pushNotificationsButton.addEventListener('click', async () => {
      try {
        await subscribeForPushNotifications()
        pulseSuccess(i18n('settings.push.success'))
        refreshPushNotificationSettings()
      } catch (error) {
        pulseError(i18n('error_ajax'))
        refreshPushNotificationSettings()
        throw error
      }
    }, { once: true })
    return
  }

  pushNotificationsLabel.textContent = i18n('settings.push.info-off')
  pushNotificationsButton.text = i18n('settings.push.disable')
  pushNotificationsButton.addEventListener('click', async () => {
    try {
      await unsubscribeFromPushNotifications()
      pulseSuccess(i18n('settings.push.disabled'))
      refreshPushNotificationSettings()
    } catch (error) {
      pulseError(i18n('error_ajax'))
      refreshPushNotificationSettings()
      throw error
    }
  }, { once: true })
}

async function trySetSleepMode () {
  try {
    const status = parseInt($('#sleep_status').val())
    const from = $('#sleeprange_from').val()
    const to = $('#sleeprange_to').val()
    const message = $('#sleep_msg').val()

    await setSleepStatus(status, from, to, message)
    pulseSuccess(i18n('settings.sleep.saved'))
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}
