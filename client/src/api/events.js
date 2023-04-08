import { patch } from './base'

export function updateInvitationResponse (eventId, status) {
  return patch(`/users/current/events/${eventId}/invitation`, { status: status })
}
