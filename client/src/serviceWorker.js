import { urls } from '@/helper/urls'
import { subscribeForPushNotifications } from '@/pushNotifications'

self.addEventListener('push', function (event) {
  if (!self.Notification || self.Notification.permission !== 'granted') {
    return
  }

  if (!event.data) {
    return
  }

  const data = event.data.json()
  event.waitUntil(self.registration.showNotification(data.title, data.options))
})

self.addEventListener('notificationclick', function (event) {
  event.waitUntil((async () => {
    const notifications = await self.registration.getNotifications()
    if (event.notification.data.action) {
      const { page, params } = event.notification.data.action
      const url = urls[page](...params)
      event.notification.close() // for android users you have to explicitly close the notification
      const notificationOfSameKind = notifications.filter(notification => notification.data.action.page === page)
      switch (page) {
        case 'conversations': // close all notifications of the same thread
          notificationOfSameKind
            .filter(notification => JSON.stringify(notification.data.action.params) === JSON.stringify(params))
            .forEach(notification => notification.close())
          break
        default: // close notification of the same kind
          notificationOfSameKind.forEach(notification => notification.close())
      }
      // open a new window or focus the foodsharing tab of same kind
      return await self.clients.matchAll({ type: 'window' }).then((clients) => {
        for (const client of clients) {
          const clientUrl = new URL(client.url)
          const notificationUrl = new URL(clientUrl.host + url)
          if (clientUrl.searchParams.get('page') === notificationUrl.searchParams.get('page')) {
            return client.focus().then(() => client.navigate(url))
          }
        }
        return self.clients.openWindow(url)
      })
    }
  })())
})

// Time to time, browsers decide to reset their push subscription data. Then all subscriptions for this browser become invalid, and we need to register a new one.
self.addEventListener('pushsubscriptionchange', function (event) {
  event.waitUntil(subscribeForPushNotifications(event.oldSubscription.options))
  // we don't need to care about the old subscription on the server, it's going to get removed automatically as soon as the server realizes it's invalid
})

// Ensure new workers to replace old ones...
// https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerGlobalScope/skipWaiting

self.addEventListener('install', event => {
  event.waitUntil(self.skipWaiting())
})

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim())
})
