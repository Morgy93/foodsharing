import { get, remove, patch } from './base'

export async function getMailUnreadCount () {
  return get('/emails/unread-count')
}

export async function setEmailStatus (emailId, read) {
  return patch(`/emails/${emailId}/${read ? 1 : 0}`)
}

export async function deleteEmail (emailId) {
  return remove(`/emails/${emailId}`)
}
