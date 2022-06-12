import { remove, post } from './base'

export function deleteGroup (id) {
  return remove(`/groups/${id}`)
}

export function addMember (groupId, memberId) {
  return post(`/groups/${groupId}/members/${memberId}`)
}
