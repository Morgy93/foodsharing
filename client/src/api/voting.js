import { get, patch, post, put, remove } from './base'

/**
 *
 * Wrong Time sending?
 *
 * @param {*} date
 * @returns
 */
function toISOStringWithTimezone (date) {
  const tzOffset = -date.getTimezoneOffset()
  const diff = tzOffset >= 0 ? '+' : '-'
  const pad = n => `${Math.floor(Math.abs(n))}`.padStart(2, '0')
  return date.getFullYear() +
    '-' + pad(date.getMonth() + 1) +
    '-' + pad(date.getDate()) +
    'T' + pad(date.getHours()) +
    ':' + pad(date.getMinutes()) +
    ':' + pad(date.getSeconds()) +
    diff + pad(tzOffset / 60) +
    ':' + pad(tzOffset % 60)
}

export async function getPoll (pollId) {
  return get(`/polls/${pollId}`)
}

export async function listPolls (groupId) {
  return get(`/groups/${groupId}/polls`)
}

export function createPoll (regionId, name, description, startDate, endDate, scope, type, options, shuffleOptions, notifyVoters) {
  return post('/polls', {
    regionId: regionId,
    name: name,
    description: description,
    startDate: toISOStringWithTimezone(startDate),
    endDate: toISOStringWithTimezone(endDate),
    scope: scope,
    type: type,
    options: options,
    shuffleOptions: shuffleOptions,
    notifyVoters: notifyVoters,
  })
}

export function editPoll (pollId, name, description, options) {
  return patch(`/polls/${pollId}`, {
    name: name,
    description: description,
    options: options,
  })
}

export async function deletePoll (pollId) {
  return remove(`/polls/${pollId}`)
}

export async function vote (pollId, options) {
  return put(`/polls/${pollId}/vote`, {
    options: options,
  })
}
