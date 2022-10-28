import { get } from './base'

function parametersBuilder ({ lat, lon, distance, teamStatus, cooperationStatus } = {}) {
  const params = new URLSearchParams()

  if (lat && lon) {
    params.append('cp', [lat, lon].join(','))
  }

  if (distance) {
    params.append('d', distance)
  }

  if (teamStatus) {
    teamStatus.forEach(s => params.append('teamStatus', s))
  }

  if (cooperationStatus) {
    cooperationStatus.forEach(s => params.append('cooperationStatus', s))
  }

  return params
}

export function getStoreMarkers (args) {
  return get(`/marker/stores?${parametersBuilder(args)}`)
}

export function getCommunitiesMarkers (args) {
  return get(`/marker/communities?${parametersBuilder(args)}`)
}

export function getFoodbasketsMarkers (args) {
  return get(`/marker/foodbaskets?${parametersBuilder(args)}`)
}

export function getFoodsharepointsMarkers (args) {
  return get(`/marker/foodsharepoints?${parametersBuilder(args)}`)
}
