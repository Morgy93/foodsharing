import '@/core'
import '@/globals'
import '@/tablesorter'
import { vueRegister, vueApply } from '@/vue'

import ReportList from './components/ReportList.vue'
import { GET } from '@/script'
// Wallpost
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

if (GET('sub') === 'foodsaver') {
  initWall('report', GET('id'))
}

// The container for the report list only exists if a region specific page is requested
const reportListContainerId = 'vue-reportlist'
if (document.getElementById(reportListContainerId)) {
  vueRegister({
    ReportList,
  })
  vueApply('#' + reportListContainerId)
}
