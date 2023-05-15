import { get } from './base'

export async function getContent (contentId) {
  return await get(`/content/${contentId}`)
}
