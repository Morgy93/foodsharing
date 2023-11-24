import '@/core'
import '@/globals'
import { acceptApplication, declineApplication } from '@/api/applications'
import { pulseError, pulseInfo, goTo } from '@/script'
import i18n from '@/helper/i18n'
import { expose } from '@/utils'
// Wallpost
import { GET } from '@/browser'
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

initWall('application', GET('fid'))

expose({
  tryAcceptApplication,
  tryDeclineApplication,
})

async function tryAcceptApplication (groupId, userId) {
  try {
    await acceptApplication(groupId, userId)
    pulseInfo(i18n('group.apply.accepted'))
    goTo(`/region?bid=${groupId}`)
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}

async function tryDeclineApplication (groupId, userId) {
  try {
    await declineApplication(groupId, userId)
    pulseInfo(i18n('group.apply.declined'))
    goTo(`/region?bid=${groupId}`)
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}
