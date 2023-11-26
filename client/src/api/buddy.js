import { put, remove } from './base'

export async function sendBuddyRequest (userId) {
  return await put(`/buddy/${userId}`)
}

export async function removeBuddyRequest (userId) {
  return await remove(`/buddy/${userId}`)
}
