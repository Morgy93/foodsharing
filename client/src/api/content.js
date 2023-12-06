import { get, remove } from './base'

export async function getContent (contentId) {
  return await get(`/content/${contentId}`)
}

export async function listContent () {
  return await get('/content')
}

export async function deleteContent (contentId) {
  return await remove(`/content/${contentId}`)
}
