/* eslint-disable eqeqeq */
import '@/core'
import '@/globals'
import $ from 'jquery'
import './WorkGroup.css'
import { GET } from '@/browser'
import { vueApply, vueRegister } from '@/vue'
import WorkingGroupEditForm from '@/components/workinggroups/WorkingGroupEditForm'

const $groups = $('.groups .field')
if ($groups.length > 3) {
  $groups.children('.head').css({
    cursor: 'pointer',
    'margin-bottom': '10px',
  }).on('mouseover', function () {
    $(this).css('text-decoration', 'underline')
  }).on('mouseout', function () {
    $(this).css('text-decoration', 'none')
  }).on('click', function () {
    const $this = $(this)

    if (!$this.next('.ui-widget.ui-widget-content.corner-bottom').is(':visible')) {
      $groups.children('.ui-widget.ui-widget-content.corner-bottom').hide()

      $groups.children('.head').css({
        'margin-bottom': '10px',
      })

      $this.css({
        'margin-bottom': '0px',
      }).next('.ui-widget.ui-widget-content.corner-bottom').show()
    } else {
      $this.css('margin-bottom', '10px')
      $groups.children('.ui-widget.ui-widget-content.corner-bottom').hide()
    }
  })

  $groups.children('.ui-widget.ui-widget-content.corner-bottom').hide()
}

if (GET('sub') === 'edit') {
  vueRegister({
    WorkingGroupEditForm,
  })
  vueApply('#vue-group-edit-form')
}
