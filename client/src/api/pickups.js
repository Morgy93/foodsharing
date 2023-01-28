import { get, patch, post, remove } from './base'

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.pickups.map(c => ({
    ...c,
    date: new Date(Date.parse(c.date)),
  }))
}

export async function joinPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return post(`/stores/${storeId}/pickups/${date}/${fsId}`)
}

export async function leavePickup (storeId, pickupDate, fsId, message, sendKickMessage = true) {
  const date = pickupDate.toISOString()
  return remove(`/stores/${storeId}/pickups/${date}/${fsId}`, {
    message,
    sendKickMessage,
  })
}

export async function leaveAllPickups (fsId, message, sendKickMessage = false) {
  return remove(
    `/pickups/${fsId}`,
    {
      message,
      sendKickMessage,
    },
  )
}

export async function confirmPickup (storeId, pickupDate, fsId) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/pickups/${date}/${fsId}`, { isConfirmed: true })
}

export async function checkPickupRuleStore (fsId, storeId, pickupDate) {
  const date = pickupDate.toISOString()
  const res = await get(`/stores/${storeId}/pickupRuleCheck/${date}/${fsId}`)
  return res.result
}

export async function setPickupSlots (storeId, pickupDate, totalSlots) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/pickups/${date}`, { totalSlots: totalSlots })
}

export async function listPickupHistory (storeId, fromDate, toDate) {
  const from = fromDate.toISOString()
  const to = toDate.toISOString()
  const res = await get(`/stores/${storeId}/history/${from}/${to}`)
  let slots = res.pickups[0].occupiedSlots
  slots = slots.map(s => ({
    ...s,
    storeId,
    isConfirmed: !!s.confirmed,
    date: new Date(Date.parse(s.date)),
  }))

  // https://github.com/you-dont-need/You-Dont-Need-Lodash-Underscore#_groupby
  return slots.reduce((r, v, i, a, k = v.date_ts) => {
    (r[k] || (r[k] = [])).push(v)
    return r
  }, {})
}

export async function listPastPickupsForUser (fsId, fromDate, toDate) {
  const from = fromDate.toISOString()
  const to = toDate.toISOString()
  const res = await get(`/foodsaver/${fsId}/pickups/${from}/${to}`)
  let slots = res.pickups[0].occupiedSlots
  slots = slots.map(s => ({
    ...s,
    isConfirmed: true,
    date: new Date(Date.parse(s.date)),
  }))

  // https://github.com/you-dont-need/You-Dont-Need-Lodash-Underscore#_groupby
  return slots.reduce((r, v, i, a, k = v.storeId + '-' + v.date_ts) => {
    (r[k] || (r[k] = [])).push(v)
    return r
  }, {})
}

export async function listSameDayPickupsForUser (fsId, onDate) {
  const day = onDate.toISOString()
  const res = await get(`/foodsaver/${fsId}/pickups/${day}`)

  return res.map(p => ({
    ...p,
    date: new Date(Date.parse(p.date)),
  }))
}

export async function listRegisteredPickups (fsId) {
  if (fsId) {
    return await get(`/pickup/registered?fsId=${fsId}`)
  }
  return await get('/pickup/registered')
}

export async function listPickupOptions (page) {
  return await get(`/pickup/options?pageSize=${page}`)
}

export async function listPastPickups (fsId, page) {
  return await get(`/pickup/history?fsId=${fsId}&page=${page}`)
}
