import '@/core'
import '@/globals'
import './Profile.css'
import $ from 'jquery'
import { expose } from '@/utils'
import { sendBuddyRequest } from '@/api/buddy'
import { pulseError, pulseInfo } from '@/script'
import i18n from '@/i18n'
import { vueRegister, vueApply } from '@/vue'
import MediationRequest from './components/MediationRequest'
import ReportRequest from './components/ReportRequest'
import BananaList from './components/BananaList'
import PublicProfile from './components/PublicProfile'
import ProfileStoreList from './components/ProfileStoreList'
import EmailBounceList from './components/EmailBounceList'
import PickupsSection from './components/PickupsSection'
import ProfileCommitmentsStat from './components/ProfileCommitmentsStat'
// Wallpost
import { URL_PART } from '@/browser'
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

expose({ trySendBuddyRequest })

async function trySendBuddyRequest (userId) {
  try {
    const isBuddy = await sendBuddyRequest(userId)
    $('.buddyRequest').remove()
    if (isBuddy) { pulseInfo(i18n('buddy.request_accepted')) } else { pulseInfo(i18n('buddy.request_sent')) }
  } catch (err) {
    pulseError(i18n('error_unexpected'))
  }
}

vueRegister({
  BananaList,
  ProfileStoreList,
  PublicProfile,
  MediationRequest,
  ReportRequest,
  EmailBounceList,
  PickupsSection,
  ProfileCommitmentsStat,
})

vueApply('#vue-profile-bananalist', true) // BananaList
vueApply('#vue-profile-storelist', true) // ProfileStoreList
vueApply('#profile-public', true) // PublicProfile
vueApply('#mediation-Request', true) // MediationRequest
vueApply('#report-Request', true) // ReportRequest
vueApply('#email-bounce-list', true)
vueApply('#profile-commitments-stat', true)
vueApply('#pickups-section', true)

if (URL_PART(0) === 'profile') {
  const wallpostTable = (URL_PART(2) === 'notes') ? 'usernotes' : 'foodsaver'
  initWall(wallpostTable, URL_PART(1))
}
