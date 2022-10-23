/* eslint-disable camelcase */
import '@/core'
import '@/globals'
import $ from 'jquery'
import {
  checkEmail,
  pulseError,
  ajax,
  goTo,
} from '@/script'
import './Team.css'
import i18n from '@/helper/i18n'

const $form = $('#contactform-form')
if ($form.length > 0) {
  const $email = $('#email')

  $email.on('keyup', function () {
    const $el = $(this)
    if (checkEmail($el.val())) {
      $email.removeClass('input-error')
    }
  })

  $email.on('blur', function () {
    const $el = $(this)
    if (!checkEmail($el.val())) {
      $email.addClass('input-error')
      pulseError(i18n('teamjs.wrongmail'))
    }
  })

  $form.on('submit', function (ev) {
    ev.preventDefault()
    if (!checkEmail($email.val())) {
      $email.trigger('select')
      $email.addClass('input-error')
      pulseError(i18n('teamjs.entermail'))
    } else {
      ajax.req('team', 'contact', {
        data: $form.serialize(),
        method: 'post',
      })
    }
  })
}

const $teamList = $('#team-list')
$teamList.find('.foot i').on('mouseover', function () {
  const $this = $(this)

  const val = $this.children('span').text()
  if (val !== '') {
    $this.parent().parent().attr('href', val).attr('target', '_blank')
  }
})

$teamList.find('.foot i').on('click', function (ev) {
  const $this = $(this)
  if ($this.hasClass('fa-lock')) {
    ev.preventDefault()
  }

  if ($this.hasClass('fa-envelope')) {
    ev.preventDefault()
    goTo($this.parent().parent().attr('href'))
  }
})

$teamList.find('.foot i').on('mouseout', function () {
  const $this = $(this).parent().parent()

  $this.attr('href', `/team/${$this.attr('id').substring(2)}`).attr('target', '_self')
})
