import { get, remove, patch, post } from './base'

export async function getMailUnreadCount () {
  return get('/emails/unread-count')
}

export async function setEmailStatus (emailId, read) {
  return patch(`/emails/${emailId}/${read ? 1 : 0}`)
}

export async function deleteEmail (emailId) {
  return remove(`/emails/${emailId}`)
}

export async function getMemberBoxes () {
  return get('/mailbox/member')
}

export async function createMailbox (name, alias, users) {
  return post('/mailbox/create', {
    name: name,
    alias: alias,
    users: users
  })
}
