import '@/core'
import '@/globals'
import './Dashboard.css'

import activity from '@/activity'
import { subscribeForPushNotifications } from '@/pushNotifications'
import { pulseSuccess } from '@/script'

activity.init()

// Push Notification Banner
const pushnotificationsBanner = document.querySelector('#top-banner-pushnotifications')
if (('PushManager' in window) && (Notification.permission === 'default') && !document.cookie.includes('pushNotificationBannerClosed=true')) {
  pushnotificationsBanner.style.display = ''

  const pushnotificationsButton = document.querySelector('#button-pushnotifications')
  pushnotificationsButton.onclick = async () => {
    await subscribeForPushNotifications()
    pulseSuccess('Push-Benachrichtigungen erfolgreich aktiviert')
    pushnotificationsBanner.style.display = 'none'
  }
}
const closeButton = document.querySelector('#close-top-banner-pushnotifications')
closeButton.onclick = () => {
  pushnotificationsBanner.style.display = 'none'
  document.cookie = 'pushNotificationBannerClosed=true;'
}
