import '@/core'
import '@/globals'
import 'jquery-dynatree'
import 'jquery.tinymce'
import $ from 'jquery'
import { pulseInfo, pulseError } from '@/script'
import { expose } from '@/utils'
import { sendTestEmail } from '@/api/newsletter'

expose({ trySendTestEmail })

async function trySendTestEmail () {
  try {
    await sendTestEmail($('#testemail').val(), $('#subject').val(), $('#message').tinymce().getContent())
    pulseInfo('E-Mail wurde versendet!')
  } catch (err) {
    pulseError('Mit der E-Mail-Adresse stimmt etwas nicht!')
  }
}
