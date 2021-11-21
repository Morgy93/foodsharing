/* eslint-disable eqeqeq */
import '@/core'
import '@/globals'
import $ from 'jquery'
import {
  goTo,
  GET,
  pulseError,
} from '@/script'
import i18n from '@/i18n'
import './Region.css'
import { vueRegister, vueApply } from '@/vue'
import Thread from './components/Thread'
import MemberList from './components/MemberList'
import GenderList from './components/GenderList'
import PickupList from './components/PickupList'
import UtilizationList from './components/UtilizationList'
import AgeBandList from './components/AgeBandList'
import ThreadList from './components/ThreadList'
import PollList from './components/PollList'
import Options from './components/Options'
import Pin from './components/Pin'
import { leaveRegion } from '@/api/regions'
// Wallpost
import '../WallPost/WallPost.css'
import { initWall } from '@/wall'

$(document).ready(() => {
  $('a[href=\'#signout\']').on('click', function () {
    $('#signout_sure').dialog('open')
    return false
  })

  $('#signout_sure').dialog({
    resizable: false,
    autoOpen: false,
    modal: true,
    width: 'auto',
    buttons: {
      [i18n('button.yes_i_am_sure')]: async function () {
        try {
          await leaveRegion($('input', this).val())
          goTo(`/?page=relogin&url=${encodeURIComponent('/?page=dashboard')}`)
        } catch (e) {
          console.error(e.code)
          if (e.code === 409) {
            pulseError(i18n('region.store_managers_cannot_leave'))
          } else {
            pulseError(i18n('error_unexpected'))
          }
          $(this).dialog('close')
        }
      },
      [i18n('button.cancel')]: function () {
        $(this).dialog('close')
      },
    },
  })

  if (GET('sub') == 'wall') {
    initWall('bezirk', GET('bid'))
  } else if (GET('sub') === 'members') {
    vueRegister({
      MemberList,
    })
    vueApply('#vue-memberlist')
  } else if (GET('sub') == 'statistic') {
    vueRegister({
      GenderList,
      PickupList,
      UtilizationList,
      AgeBandList,
    })
    vueApply('#vue-genderlist')
    vueApply('#vue-utilizationlist')
    vueApply('#vue-ageBandlist')
    vueApply('#vue-pickuplist', true)
  } else if (['botforum', 'forum'].includes(GET('sub'))) {
    if (GET('tid') !== undefined) {
      vueRegister({
        Thread,
      })
      vueApply('#vue-thread')
    } else if (!GET('newthread')) {
      vueRegister({
        ThreadList,
      })
      vueApply('#vue-threadlist')
    }
  } else if (GET('sub') === 'polls') {
    vueRegister({
      PollList,
    })
    vueApply('#vue-polllist')
  } else if (GET('sub') === 'options') {
    vueRegister({
      Options,
    })
    vueApply('#vue-options')
  } else if (GET('sub') === 'pin') {
    vueRegister({
      Pin,
    })
    vueApply('#vue-pin')
  }
})
