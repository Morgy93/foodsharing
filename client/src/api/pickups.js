import { get, patch, post, remove } from './base'
import dateFnsParseISO from 'date-fns/parseISO'
import _ from 'underscore'

export async function listPickups (storeId) {
  const res = await get(`/stores/${storeId}/pickups`)

  return res.pickups.map(c => ({
    ...c,
    date: dateFnsParseISO(c.date),
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

export async function setPickupSlots (storeId, pickupDate, totalSlots) {
  const date = pickupDate.toISOString()
  return patch(`/stores/${storeId}/pickups/${date}`, { totalSlots: totalSlots })
}

export async function listPickupHistory (storeId, fromDate, toDate) {
  const from = fromDate.toISOString()
  const to = toDate.toISOString()
  const res = await get(`/stores/${storeId}/history/${from}/${to}`)
  const slots = res.pickups[0].occupiedSlots

  return _.groupBy(slots.map(s => ({
    ...s,
    storeId,
    isConfirmed: !!s.confirmed,
    date: dateFnsParseISO(s.date),
  })), 'date_ts')
}

export async function listPastPickupsForUser (fsId, fromDate, toDate) {
  const from = fromDate.toISOString()
  const to = toDate.toISOString()
  const res = await get(`/foodsaver/${fsId}/pickups/${from}/${to}`)
  const slots = res.pickups[0].occupiedSlots

  return _.groupBy(slots.map(s => ({
    ...s,
    isConfirmed: true,
    date: dateFnsParseISO(s.date),
  })), (s) => { return s.storeId + '-' + s.date_ts })
}

export async function listSameDayPickupsForUser (fsId, onDate) {
  const day = onDate.toISOString()
  const res = await get(`/foodsaver/${fsId}/pickups/${day}`)

  return res.map(p => ({
    ...p,
    date: dateFnsParseISO(p.date),
  }))
}

export async function listRegisteredPickups (fsId) {
  return await get(`/pickup/registered?fsId=${fsId}`)
}

export async function listPickupOptions (fsId, page) {
  return await get(`/pickup/options?page=${page}`)
}

export async function listPastPickups (fsId, page) {
  return await get(`/pickup/history?fsId=${fsId}&page=${page}`)
}
