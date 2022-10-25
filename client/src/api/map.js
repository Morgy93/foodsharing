import { get } from './base'

export function getMapMarkers (types = ['baskets'], status) {
  const params = new URLSearchParams()
  types.forEach(t => params.append('types[]=', t))
  if (status) {
    status.forEach(s => params.append('status[]=', s))
  }
  return get(`/map/markers?${params}`)
}

export function getCommunityBubbleContent (regionId) {
  return get(`/map/regions/${regionId}`)
}
