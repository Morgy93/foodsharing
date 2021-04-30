import { get, post, put, patch, remove } from './base'

export function listThreads (groupId, subforumId, offset = 0) {
  return get(`/forum/${groupId}/${subforumId}?offset=${offset}`)
}

export function getThread (threadId) {
  return get(`/forum/thread/${threadId}`)
}
export function deleteThread (threadId) {
  return remove(`/forum/thread/${threadId}`)
}

export function followThreadByEmail (threadId) {
  return post(`/forum/thread/${threadId}/follow/email`)
}

export function followThreadByBell (threadId) {
  return post(`/forum/thread/${threadId}/follow/bell`)
}

export function unfollowThreadByEmail (threadId) {
  return remove(`/forum/thread/${threadId}/follow/email`)
}

export function unfollowThreadByBell (threadId) {
  return remove(`/forum/thread/${threadId}/follow/bell`)
}

export function stickThread (threadId) {
  return patch(`/forum/thread/${threadId}`, {
    isSticky: true,
  })
}

export function unstickThread (threadId) {
  return patch(`/forum/thread/${threadId}`, {
    isSticky: false,
  })
}

export function activateThread (threadId) {
  return patch(`/forum/thread/${threadId}`, {
    isActive: true,
  })
}

export function setThreadStatus (threadId, status) {
  return patch(`/forum/thread/${threadId}`, {
    status: status,
  })
}

export function createPost (threadId, body) {
  return post(`/forum/thread/${threadId}/posts`, {
    body: body,
  })
}

export function updatePost (postId, body) {
  return put('/forum/post', {
    body: body,
  })
}

export function deletePost (postId) {
  return remove(`/forum/post/${postId}`)
}

export function addReaction (postId, key) {
  return post(`/forum/post/${postId}/reaction/${key}`)
}

export function removeReaction (postId, key) {
  return remove(`/forum/post/${postId}/reaction/${key}`)
}
