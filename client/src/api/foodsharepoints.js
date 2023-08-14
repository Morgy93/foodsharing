import { get } from './base'

export async function listFoodSharePoints (regionId) {
  return await get(`/regions/${regionId}/foodSharePoints`)
}
