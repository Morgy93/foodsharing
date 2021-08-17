import { get, patch, post, put, remove } from './base'
import { formatISO } from 'date-fns'

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
    startDate: formatISO(startDate),
    endDate: formatISO(endDate),
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
