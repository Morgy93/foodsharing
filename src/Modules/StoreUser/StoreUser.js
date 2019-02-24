/* eslint-disable eqeqeq,camelcase */

import '@/core'
import '@/globals'

import i18n from '@/i18n'

import { expose } from '@/utils'

import $ from 'jquery'
import {
  ajax,
  pulseInfo,
  pulseError,
  showLoader,
  hideLoader,
  GET,
  profile
} from '@/script'

import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import '@/tablesorter'

import { store, user } from '@/server-data'

import {
  u_clearDialogs,
  u_updatePosts,
  u_betrieb_sign_out,
  u_delPost,
  u_undate,
  u_fetchconfirm,
  u_fetchdeny,
  acceptRequest,
  warteRequest,
  denyRequest,
  createJumperMenu,
  createMenu,
  u_timetableAction,
  createConfirmedMenu,
  createUnconfirmedMenu,
  addContextMenu
} from './StoreUser.lib'
import { signup } from '@/api/stores'

expose({
  u_updatePosts,
  u_betrieb_sign_out,
  u_delPost,
  u_undate,
  u_fetchconfirm,
  u_fetchdeny,
  acceptRequest,
  warteRequest,
  denyRequest,
  createJumperMenu,
  createMenu,
  u_timetableAction,
  createConfirmedMenu,
  createUnconfirmedMenu
})

$('.cb-verantwortlicher').on('click', function () {
  if ($('.cb-verantwortlicher:checked').length >= 4) {
    pulseError(i18n('max_3_leader'))
    return false
  }
})

$('#team-form').on('submit', function (ev) {
  if ($('.cb-verantwortlicher:checked').length == 0) {
    pulseError(i18n('verantwortlicher_must_be'))
    ev.preventDefault()
    return false
  }
})

$('#team_msg-wrapper').hide()

$('#u_undate').dialog({
  autoOpen: false,
  modal: true,
  width: 400,
  buttons: [
    {
      text: i18n('have_backup'),
      click: function () {
        showLoader()
        $.ajax({
          url: '/xhr.php?f=delDate',
          data: { 'date': $('#undate-date').val(), 'bid': store.id },
          dataType: 'json',
          success: function (ret) {
            if (ret.status == 1) {
              $(`.fetch-${$('#undate-date').val().replace(/[^0-9]/g, '')}-${user.id}`).hide()
            } else {
              hideLoader()
            }
          },
          complete: function () {
            $('#u_undate').dialog('close')
            hideLoader()
          }
        })
      },
      id: 'have_backup'
    },
    {
      text: i18n('msg_to_team'),
      click: function () {
        $('#team_msg-wrapper').show()
        $('#have_backup').hide()
        $('#msg_to_team').hide()
        $('#send_msg_to_team').show()
      },
      id: 'msg_to_team'
    },
    {
      text: i18n('del_and_send'),
      click: function () {
        showLoader()
        $.ajax({
          url: '/xhr.php?f=delDate',
          data: { 'date': $('#undate-date').val(), 'msg': $('#team_msg').val(), 'bid': store.id },
          dataType: 'json',
          success: function (ret) {
            if (ret.status == 1) {
              $(`.fetch-${$('#undate-date').val().replace(/[^0-9]/g, '')}-${user.id}`).hide()
            } else {
              hideLoader()
            }
          },
          complete: function () {
            $('#u_undate').dialog('close')
            hideLoader()
          }
        })
      },
      id: 'send_msg_to_team',
      css: { 'display': 'none' }
    }
  ]
})

$('#comment-post').hide()

$('div#pinnwand form textarea').on('focus', function () {
  $('#comment-post').show()
})

$('div#pinnwand form input.submit').button().on('keydown', function (event) {
  $('div#pinnwand form').trigger('submit')
})

$('div#pinnwand form').on('submit', function (e) {
  e.preventDefault()
  if ($('div#pinnwand form textarea').val() != $('div#pinnwand form textarea').attr('title')) {
    $.ajax({
      dataType: 'json',
      data: $('div#pinnwand form').serialize(),
      url: `/xhr.php?f=addPinPost&team=${store.team_js}`,
      success: function (data) {
        if (data.status == 1) {
          $('div#pinnwand form textarea').val($('div#pinnwand form textarea').attr('title'))
          $('#pinnwand .posts').html(data.html)
        }
      }
    })
  }
})

$('#signout_shure').dialog({
  autoOpen: false,
  modal: true,
  buttons: [
    {
      text: $('#signout_shure .sure').text(),
      click: function () {
        showLoader()

        ajax.req('betrieb', 'signout', {
          data: { id: GET('id') },
          success: function () {

          }
        })
      }
    },
    {
      text: $('#signout_shure .abort').text(),
      click: function () {
        $('#signout_shure').dialog('close')
      }
    }
  ]
})

$('#delete_shure').dialog({
  autoOpen: false,
  modal: true,
  buttons: [
    {
      text: $('#delete_shure .sure').text(),
      click: function () {
        showLoader()
        const pid = $(this).data('pid')
        $.ajax({
          url: '/xhr.php?f=delBPost',
          data: { 'pid': pid },
          success: function (ret) {
            if (ret == 1) {
              $(`.bpost-${pid}`).remove()
              $('#delete_shure').dialog('close')
            }
          },
          complete: function () {
            hideLoader()
          }
        })
      }
    },
    {
      text: $('#delete_shure .abort').text(),
      click: function () {
        $('#delete_shure').dialog('close')
      }
    }
  ]
})

$('.timedialog-add-me').on('click', function () {
  u_clearDialogs()

  if (user.verified) {
    const date = $(this).children('input')[0].value.split('::')[0]
    const day = $(this).children('input')[0].value.split('::')[2]
    const label = $(this).children('input')[0].value.split('::')[1]
    const id = $(this).children('input')[1].value

    $('#timedialog-date').val(date)
    $('#date-label').html(`${day}, ${label}`)
    $('#range-day-label').html(day.toLowerCase())
    $('#timedialog-id').val(id)
    $('#timedialog').dialog('open')
  } else {
    pulseInfo(i18n('not_verified'))
  }
})

$('#timedialog').dialog({
  title: 'Sicher?',
  resizable: false,
  modal: true,
  autoOpen: false,
  width: 500,
  buttons: {
    'Eintragen': async function () {
      const requestDate = ($('#timedialog-date').val()).replace(' ', 'T') + 'Z'
      try {
        const result = await signup(store.id, requestDate)
        u_clearDialogs()
        const timedialogId = $('#timedialog-id').val()
        const $button = $(`#${timedialogId}-button`)
        const $imglist = $(`#${timedialogId}-imglist`)
        $button.last().remove()

        const li = $(`<li><a class="img-link" href="#"><img src="${user.avatar.mini}" title="Du" /><span>&nbsp;</span></a></li>`)
          .addClass(result.confirmed ? 'confirmed' : 'unconfirmed')

        $imglist
          .prepend(li)
          .find('.img-link')
          .on('click', e => {
            e.preventDefault()
            profile(user.id)
          })

        if (!result.confirmed) pulseInfo(i18n('wait_for_confirm'))
        const $liLast = $imglist.find('li:last')
        if ($liLast.hasClass('empty')) {
          $liLast.remove()
        }

        $imglist.find('li.empty')
          .off('click')
          .addClass('nohover')
          .removeClass('filled')
          .find('a')
          .attr('title', '')
          .tooltip('option', { disabled: true }).tooltip('close')

      } catch (err) {
        u_clearDialogs()
        pulseError('Dieser Abholslot ist nicht verfügbar')
      }
      $(this).dialog('close')
    },
    'Abbrechen': function () {
      u_clearDialogs()
      $(this).dialog('close')
    }
  }
})

$('#changeStatus').button().on('click', () => {
  $('#changeStatus-hidden').dialog({
    title: i18n('change_status'),
    modal: true
  })
})

$('.nft-remove').button({
  text: false,
  icons: {
    primary: 'ui-icon-minus'
  }
}).on('click', function () {
  const $this = $(this)
  $this.parent().parent().remove()
})

addContextMenu('.context-confirmed', 57, createConfirmedMenu)
addContextMenu('.context-unconfirmed', 95, createUnconfirmedMenu)
addContextMenu('.context-team', 160, createMenu)
addContextMenu('.context-jumper', 95, createJumperMenu)

$('.timetable').on('keyup', '.fetchercount', function () {
  if (this.value != '') {
    let val = parseInt(`0${this.value}`, 10)
    if (val == 0) {
      val = 1
    } else if (val > 2) {
      pulseError('Du hast mehr als zwei Personen zum Abholen angegeben.<br />In der Regel sollten <strong>nicht mehr als zwei Leute</strong> zu einem Betrieb gehen. Zu viele Abholer führten schon oft zum Ende einer Kooperation. <br />Zur Not geht einer von Euch mit Auto oder Anhänger vor und Ihr trefft Euch außer Reichweite vom Betrieb.', {
        sticky: true
      })
    }
    this.value = val
  }
})

$('#nft-add').button({
  text: false
}).on('click', function () {
  $('table.timetable tbody').append($('table#nft-hidden-row tbody').html())
  let clname = 'odd'
  $('table.timetable tbody tr').each(function () {
    if (clname == 'odd') {
      clname = 'even'
    } else {
      clname = 'odd'
    }

    const $this = $(this)
    $this.removeClass('odd even')
    $this.addClass(clname)
  })
  $('.nft-remove').button({
    text: false,
    icons: {
      primary: 'ui-icon-minus'
    }
  }).on('click', function () {
    const $this = $(this)
    $this.parent().parent().remove()
  })
})
