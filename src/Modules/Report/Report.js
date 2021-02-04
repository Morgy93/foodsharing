import '@/core'
import '@/globals'
import '@/tablesorter'
import { vueRegister, vueApply } from '@/vue'

import ReportList from './components/ReportList.vue'
import { GET } from '@/script'
// Wallpost
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

initWall('fsreport', GET('id'))

// The container for the report list only exists if a region specific page is requested
var reportListContainerId = 'vue-reportlist'
if (document.getElementById(reportListContainerId)) {
  vueRegister({
    ReportList,
  })
  vueApply('#' + reportListContainerId)
}
