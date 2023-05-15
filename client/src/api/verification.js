import { post, get, patch, remove } from './base'

export async function verifyUser (userId) {
  return await patch(`/user/${userId}/verification`)
}

export async function deverifyUser (userId) {
  return await remove(`/user/${userId}/verification`)
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
