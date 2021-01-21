import { post } from './base'

export function sendEmailToWorkingGroup (groupId, message) {
  return post(`/workinggroups/${groupId}/email`, {
    message: message,
  })
}
