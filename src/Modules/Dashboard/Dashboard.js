import '@/core'
import '@/globals'

import { vueRegister, vueApply } from '@/vue'
import ActivityOverview from './components/ActivityOverview'
// import InstallPWA from './components/InstallPWA'
import DashboardWarning from './components/DashboardWarning'
import EventPanel from '../Event/components/EventPanel'

import './Dashboard.css'

import i18n from '@/i18n'
import { subscribeForPushNotifications } from '@/pushNotifications'
import { pulseSuccess, pulseError } from '@/script'

vueRegister({
  ActivityOverview,
  EventPanel,
})
vueApply('#activity-overview')
vueApply('#event-panel', true)

// vueRegister({
//   InstallPWA,
// })
// vueApply('#install-pwa')

if (document.querySelector('#dashboard-warning') !== null) {
  vueRegister({
    DashboardWarning,
  })
  vueApply('#dashboard-warning')
}

// Push Notification Banner
const pushnotificationsBanner = document.querySelector('#top-banner-pushnotifications')
if (('serviceWorker' in navigator) && ('PushManager' in window) && (Notification.permission === 'default') && !document.cookie.includes('pushNotificationBannerClosed=true')) {
  pushnotificationsBanner.style.display = ''

  const pushnotificationsButton = document.querySelector('#button-pushnotifications')
  pushnotificationsButton.addEventListener('click', async () => {
    try {
      await subscribeForPushNotifications()
      pulseSuccess(i18n('settings.push.success'))
      pushnotificationsBanner.classList.add('top-banner-pushnotifications-closed')
    } catch (error) {
      pulseError(i18n('error_ajax'))
      throw error
    }
  })
}
const closeButton = document.querySelector('#close-top-banner-pushnotifications')
closeButton.addEventListener('click', () => {
  pushnotificationsBanner.classList.add('top-banner-pushnotifications-closed')
  document.cookie = 'pushNotificationBannerClosed=true;'
})
