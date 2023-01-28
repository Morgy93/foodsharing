import { remove, patch, post } from './base'

export function deleteGroup (id) {
  return remove(`/groups/${id}`)
}

export function addMember (groupId, memberId) {
  return post(`/groups/${groupId}/members/${memberId}`)
}

export function updateGroup (groupId, name, description, photo, applyType, requiredBananas, requiredPickups, requiredWeeks) {
  return patch(`/groups/${groupId}`, {
    name: name,
    description: description,
    photo: photo,
    applyType: applyType,
    requiredBananas: requiredBananas,
    requiredPickups: requiredPickups,
    requiredWeeks: requiredWeeks,
  })
}
