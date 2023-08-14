import { get, patch, remove } from './base'

export async function acceptApplication (groupId, userId) {
  return await patch(`/applications/${groupId}/${userId}`)
}

export async function declineApplication (groupId, userId) {
  return await remove(`/applications/${groupId}/${userId}`)
}

export async function getApplications (groupId) {
  return await get(`/applications/${groupId}`)
}
