import '@/core'
import '@/globals'
import '@/tablesorter'
import 'jquery.tinymce'
import './Quiz.css'

import { expose } from '@/utils'
import { ifconfirm } from '@/script'

// Wallpost
import { GET } from '@/browser'
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

import { vueApply, vueRegister } from '@/vue'
import QuizEditor from '@/views/pages/Quiz/QuizEditor'

vueRegister({ QuizEditor })
vueApply('#vue-quiz-editor', true)

const sub = GET('sub')
if (sub === 'wall') {
  initWall('question', GET('id'))
}

expose({
  ifconfirm,
})
