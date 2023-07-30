import { get, patch } from './base'

export function getFoodSharePointsNotification () {
  return get('/notifications/foodsharepoints')
}

export function listRegionsWithoutWorkingGroups () {
  return get('/notifications/regions')
}

export function getThreadsNotification () {
  return get('/notifications/forum')
}

export function getUserNotification () {
  return get('/notifications/user')
}

export function listWorkingGroups () {
  return get('/notifications/groups')
}

export function updateRegionsAndWorkgroupsNotification (regions) {
  return patch('/notifications/regions', regions)
}

export function setThreadsNotification (threads) {
  return patch('/notifications/forum', threads)
}

export function setFoodSharePointsNotification (foodSharePoints) {
  return patch('/notifications/foodsharepoints', foodSharePoints)
}

export function setUserNotification (newsletter, chat) {
  return patch('/notifications/user', {
    newsletter: newsletter,
    chat: chat,
  })
}
