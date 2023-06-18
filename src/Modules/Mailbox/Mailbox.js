/* eslint-disable eqeqeq,camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import 'corejs-typeahead'
import 'jquery-tagedit'
import 'jquery-tagedit-auto-grow-input'
import { expose } from '@/utils'
import {
  ajreq,
  GET,
  pulseInfo,
  pulseError,
  checkEmail,
} from '@/script'
import { vueRegister, vueApply } from '@/vue'
import './Mailbox.css'
import Mailbox from './components/Mailbox.vue'
import i18n from '@/helper/i18n'
import { deleteEmail, setEmailStatus } from '@/api/mailbox'
import MailboxManage from './components/MailboxManage.vue'

expose({
  mb_finishFile,
  mb_removeLast,
  mb_mailto,
  mb_deleteEmail,
  mb_reset,
  mb_answer,
  mb_forward,
  mb_clearEditor,
  mb_closeEditor,
  mb_send_message,
  mb_refresh,
  checkEmail,
  u_handleNewEmail,
  u_addTypeHead,
  setAutocompleteAddresses,
  mb_foldRecipients,
  trySetEmailStatus,
})

if (GET('a') === 'manage') {
  vueRegister({
    MailboxManage,
  })
  vueApply('#vue-mailbox-manage', true) // Mailbox
}

vueRegister({
  Mailbox,
})
vueApply('#vue-mailbox', true) // Mailbox

function mb_finishFile (newname) {
  $('ul#et-file-list li:last').addClass('finish').append(`<input type="hidden" class="tmp" value="${newname}" name="tmp_${$('ul#et-file-list li').length}" />`)
  $('#etattach-button').button('option', 'disabled', false)
}

function mb_removeLast () {
  $('ul#et-file-list li:last').remove()
  $('#etattach-button').button('option', 'disabled', false)
}

export function mb_new_message (email) {
  mb_clearEditor()
  $('#message-editor').dialog('open')
  if ($('.edit-an').length > 0) {
    $('.edit-an')[0].focus()
  }
  if (email != undefined) {
    $('.edit-an:first').val(email)
    u_handleNewEmail(email, $('.edit-an:first'))
    $('#edit-subject')[0].focus()
  }
}

function mb_mailto (email) {
  mb_clearEditor()
  $('.edit-an:first').val(email)
  $('#message-body').dialog('close')
  $('#message-editor').dialog('open')
  if ($('#edit-subject').length > 0) {
    $('#edit-subject')[0].focus()
  }
}

async function mb_deleteEmail () {
  const emailId = $('#mb-hidden-id').val()
  try {
    await deleteEmail(emailId)
    $(`tr#message-${emailId}`).remove()
    $('#message-body').dialog('close')
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}

function mb_reset () {
  $('#et-file-list').html('')
}

function mb_answer () {
  $('#edit-body').val($('#mailbox-body-plain').val())
  $('#edit-reply').val($('#mb-hidden-id').val())
  mb_reset()

  let subject = $('#mb-hidden-subject').val()
  if (subject.substring(0, 3) != 'Re:') {
    subject = `Re: ${subject}`
  }

  $('#message-editor').dialog('option', {
    title: subject,
  })

  $('#edit-subject').val(subject)
  $('input.edit-an:first').val($('#mb-hidden-email').val())

  u_handleNewEmail($('input.edit-an:first').val(), $('input.edit-an:first'))

  $('#message-body').dialog('close')
  $('#message-editor').dialog('open')

  if ($('#edit-body').length > 0) {
    $('#edit-body')[0].focus()
  }
}

function mb_forward () {

}

export function mb_setMailbox (mb_id) {
  if ($('#edit-von').length > 0) {
    const email = $(`#edit-von option.mb-${mb_id}`).text()
    $(`#edit-von option.mb-${mb_id}`).remove()
    const html = $('#edit-von').html()
    $('#edit-von').html('')

    $('#edit-von').html(`<option value="${mb_id}" class="mb-${mb_id}">${email}</option>${html}`)
  }
}

function mb_clearEditor () {
  $('#edit-von').val('')
  u_handleNewEmail('') // fixes some wired bug, where the edit-an-field is missing after reopening the form
  for (let i = 1; i < $('.edit-an').length; i++) {
    $('.edit-an:last').parent().parent().parent().remove()
  }
  $('.edit-an').val('')
  $('#edit-subject').val('')
  $('#edit-body').val('')
  $('#edit-reply').val('0')
  $('#message-editor').dialog('option', {
    title: i18n('chat.new_message'),
  })
  mb_reset()
}

function mb_closeEditor () {
  $('#message-editor').dialog('close')
}

function mb_send_message () {
  let mbid = $('#h-edit-von').val()
  if ($('#edit-von').length > 0) {
    mbid = $('#edit-von').val()
  }

  const attach = []
  let i = 0
  $('#et-file-list li').each(function () {
    attach[i] = {
      name: $(this).text(),
      tmp: $('#et-file-list li input')[i].value,
    }

    i++
  })

  let an = ''
  $('.edit-an').each(function () {
    an = `${an};${$(this).val()}`
  })
  // console.log(an, $('.edit-an'))
  if (an.indexOf('@') == -1) {
    $('.edit-an')[0].focus()
    pulseInfo(i18n('chat.receivermissing'))
  } else if (an.indexOf('noreply') !== -1) {
    $('.edit-an')[0].focus()
    pulseInfo(i18n('mail.noreply_addresses_not_allowed'))
  } else {
    ajreq('send_message', {
      mb: mbid,
      an: an.substring(1),
      sub: $('#edit-subject').val(),
      body: $('#edit-body').val(),
      attach: attach,
      reply: parseInt($('#edit-reply').val()),
    }, 'post')
  }
}

function mb_refresh () {
  ajreq('loadmails', {
    mb: $('#mbh-mailbox').val(),
    folder: $('#mbh-folder').val(),
    type: $('#mbh-type').val(),
  })
}

const substringMatcher = function (strs) {
  return function findMatches (q, cb) {
    // regex used to determine if a string contains the substring `q`
    const substringRegex = new RegExp(q, 'i')

    // an array that will be populated with substring matches
    const matches = []

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function (i, str) {
      if (substringRegex.test(str)) {
        matches.push({ value: str })
      }
    })

    cb(matches)
  }
}

let addresses = []

function setAutocompleteAddresses (adr) {
  addresses = adr
}

function u_addTypeHead () {
  $('.edit-an').typeahead('destroy')
  $('.edit-an:last').typeahead({
    hint: true,
    minLength: 2,
  }, {
    name: 'addresses',
    displayKey: 'value',
    source: substringMatcher(addresses),
    limit: 15,
  })

  $('.edit-an').on('typeahead:selected typeahead:autocompleted', function (e, datum) {
    window.setTimeout(() => (u_handleNewEmail(datum.value, $(this))), 100)
  }).on('blur', function () {
    const $this = this
    if ($this.value != '' && !checkEmail($this.value)) {
      pulseError(i18n('chat.incorrect_address'))
      $this.focus()
    } else if ($this.value != '') {
      window.setTimeout(() => (u_handleNewEmail(this.value, $(this))), 100)
    }
  })
}

function u_handleNewEmail (email, el) {
  if (u_anHasChanged()) {
    const availmail = []
    let availmail_count = 0
    $('.edit-an').each(function () {
      const $this = $(this)
      if (!checkEmail($this.val()) || (availmail[$this.val()] != undefined)) {
        // $this.parent().parent().parent().remove();
      } else {
        availmail[$this.val()] = true
        availmail_count++
      }
    })

    $('#mail-subject').before('<tr><td class="label">&nbsp;</td><td class="data"><input type="text" name="an[]" class="edit-an" value="" /></td></tr>')

    u_addTypeHead()
    const height = $('#edit-body').height() - (availmail_count * 28)
    if (height > 40) {
      $('#edit-body').css('height', `${height}px`)
    }

    $('.edit-an:last').trigger('focus')
  }
}

let mailcheck = ''

function u_anHasChanged () {
  let check = ''
  $('.edit-an').each(function () {
    check += this.value
  })
  if (mailcheck == '') {
    mailcheck = check
    return true
  } else if (mailcheck != check) {
    mailcheck = check
    return true
  } else {
    return false
  }
}

function mb_foldRecipients (fullString, shortString) {
  const button = $('#mail-fold-icon')
  const label = $('#mail-to-list')

  if (label.data('folded') === true) {
    label.html(fullString)
  } else {
    label.html(shortString)
  }

  button.toggleClass('fa-sort-down')
  button.toggleClass('fa-sort-up')
  label.data('folded', !label.data('folded'))
}

function trySetEmailStatus (emailId, read) {
  try {
    setEmailStatus(emailId, read)
    $('#message-body').dialog('close')
    $('tr#message-' + emailId).removeClass('read-1').addClass('read-0')
    $('span#message-' + emailId + '-status').removeClass('read-1').addClass('read-0')
  } catch (e) {
    pulseError(i18n('error_unexpected'))
  }
}
