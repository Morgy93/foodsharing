import QuizEditor from '@/views/pages/Quiz/QuizEditor.vue'
import { vueApply, vueRegister } from '@/vue'

import '@/core'
import '@/globals'

vueRegister({
  QuizEditor,
})

vueApply('#vue-quiz-editor', true)
