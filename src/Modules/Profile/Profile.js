import '@/core'
import '@/globals'
import './Profile.css'
import { vueRegister, vueApply } from '@/vue'
import BananaList from './components/BananaList'
import PublicProfile from './components/PublicProfile'
import ProfileStoreList from './components/ProfileStoreList'
import EmailBounceList from './components/EmailBounceList'
import PickupsSection from '@/components/PickupTable/PickupsSection'
import ProfileCommitmentsStat from './components/ProfileCommitmentsStat'
import ProfileMenu from './components/ProfileMenu'
import ProfileInfos from './components/ProfileInfos'
// Wallpost
import { URL_PART } from '@/browser'
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

vueRegister({
  BananaList,
  ProfileStoreList,
  PublicProfile,
  EmailBounceList,
  PickupsSection,
  ProfileCommitmentsStat,
  ProfileInfos,
  ProfileMenu,
})

vueApply('#vue-profile-bananalist', true) // BananaList
vueApply('#vue-profile-storelist', true) // ProfileStoreList
vueApply('#profile-public', true) // PublicProfile
vueApply('#email-bounce-list', true)
vueApply('#profile-commitments-stat', true)
vueApply('#pickups-section', true)
vueApply('#vue-profile-infos', true)
vueApply('#vue-profile-menu', true)

if (URL_PART(0) === 'profile') {
  const wallpostTable = (URL_PART(2) === 'notes') ? 'usernotes' : 'foodsaver'
  initWall(wallpostTable, URL_PART(1))
}
