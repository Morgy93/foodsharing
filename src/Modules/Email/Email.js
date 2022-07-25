import '@/core'
import '@/globals'
import 'js/dynatree/jquery.dynatree'
import 'js/dynatree/skin/ui.dynatree.css'
import 'js/tinymce/jquery.tinymce.min'
import $ from 'jquery'
import i18n from '@/helper/i18n'
import { pulseInfo, pulseError } from '@/script'
import { expose } from '@/utils'
import { sendTestEmail } from '@/api/newsletter'
import './Email.css'

expose({ trySendTestEmail })

async function trySendTestEmail () {
  try {
    await sendTestEmail($('#testemail').val(), $('#subject').val(), $('#message').tinymce().getContent())
    pulseInfo(i18n('recipients.test_email_sent'))
  } catch (err) {
    pulseError(i18n('recipients.test_email_invalid'))
  }
}
