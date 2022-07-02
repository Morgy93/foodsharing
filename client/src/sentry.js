import Vue from 'vue'
import * as Sentry from '@sentry/vue'
import serverData from '@/scripts/server-data'

if (serverData.ravenConfig) {
  console.log('using sentry config from server', serverData.ravenConfig)
  Sentry.init({
    Vue: Vue,
    attachProps: true,
    logErrors: true,
    dsn: serverData.ravenConfig,
  })
}
