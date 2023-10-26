/* eslint-disable camelcase */
import $ from 'jquery'
import '@/core'
import '@/globals'
import '@/tablesorter'
import { checkAllCb } from '@/script'
import { expose } from '@/utils'
import './PassportGenerator.css'

expose({
  checkAllCb,
})

$('.checker').on('click', function (el) {
  const $this = $(this)
  if ($this[0].checked) {
    $(`input.checkbox.bezirk${$this.attr('value')}`).prop('checked', true)
  } else {
    $(`input.checkbox.bezirk${$this.attr('value')}`).prop('checked', false)
  }
})

$('a.fsname').on('click', function () {
  const $this = $(this)
  if ($(`input[value='${$this.next().val()}']`)[0].checked) {
    $(`input[value='${$this.next().val()}']`).prop('checked', false)
  } else {
    $(`input[value='${$this.next().val()}']`).prop('checked', true)
  }
  return false
})

$("a[href='#start']").on('click', function () {
  $('form#generate').trigger('submit')
  return false
})

$('a.dateclick').on('click', function () {
  const $this = $(this)
  const dstr = $this.next().val()
  if ($(`input.date${dstr}`)[0].checked) {
    $(`input.date${dstr}`).prop('checked', false)
  } else {
    $(`input.date${dstr}`).prop('checked', true)
  }
  return false
})
