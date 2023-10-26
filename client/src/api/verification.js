import { post, get, patch, remove } from './base'

export async function verifyUser (userId, message) {
  return patch(`/user/${userId}/verification`, { message })
}

export async function deverifyUser (userId, message) {
  return remove(`/user/${userId}/verification`, { message })
}

export async function getVerificationHistory (userId) {
  return await get(`/user/${userId}/verificationhistory`)
}

export async function getPassHistory (userId) {
  return await get(`/user/${userId}/passhistory`)
}

export async function createPassportAsUser () {
  return await post('/user/current/passport', '', { responseType: 'blob' })
}
