import { get } from './base'

export async function getChangelog () {
  return await get('/changelog/')
}
