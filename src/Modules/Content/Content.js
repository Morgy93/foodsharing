import '@/core'
import '@/globals'
import '@/tablesorter'

import 'jquery.tinymce' // cannot go earlier!

import { GET } from '@/browser'
import { expose } from '@/utils'
import { ifconfirm } from '@/script'
import { vueRegister, vueApply } from '@/vue'

import './Content.css'
import ReleaseNotes from './components/ReleaseNotes.vue'
import Changelog from '@/views/pages/Changelog/Changelog.vue'

expose({
  ifconfirm,
})

if (GET('sub') === 'releaseNotes') {
  vueRegister({
    ReleaseNotes,
  })
  vueApply('#vue-release-notes')
}

if (GET('sub') === 'changelog') {
  vueRegister({
    Changelog,
  })
  vueApply('#vue-changelog')
}
