import { get, patch, post, remove } from './base'

export async function getStoreMetaData () {
  return await get('/stores/meta-data')
}

export async function getStoreInformation (storeId) {
  const result = await get(`/stores/${storeId}/information`)
  result.chainId = result.chain ? result.chain.id : null
  result.categoryId = result.category ? result.category.id : null
  return result
}

export async function updateStore (store) {
  const result = await patch(`/stores/${store.id}/information`, store)
  return result
}

function normalizeStoreWallPost (post) {
  post.createdAt = new Date(Date.parse(post.createdAt))
  post.body = post.text
  delete post.text
  return post
}

export async function getStoreWall (storeId) {
  const posts = (await get(`/stores/${storeId}/posts`))
  return posts.map(normalizeStoreWallPost)
}

export async function writeStorePost (storeId, text) {
  const newPost = (await post(`/stores/${storeId}/posts`, { text })).post
  return normalizeStoreWallPost(newPost)
}

export async function setStoreTeamStatus (storeId, status) {
  return patch(`/stores/${storeId}/information`, { teamStatus: status })
}

export async function getStoreDetails (storeId) {
  return get(`/stores/${storeId}`)
}

export async function deleteStorePost (storeId, postId) {
  return remove(`/stores/${storeId}/posts/${postId}`)
}

export async function listStoresForCurrentUser () {
  return get('/user/current/stores')
}

export async function listStoresDetailsForCurrentUser (expand) {
  const { stores } = await get('/user/current/stores/details')
  return formatStoresToMatchColumnKeys(stores)
}

/**
 * This can be removed if we adjust the api endpoint
 */
export function formatStoresToMatchColumnKeys (stores) {
  return stores.map(store => {
    const changeKey = (key, newKey) => {
      store[newKey] = store[key]
      delete store[key]
    }
    changeKey('cooperationStatus', 'status')
    changeKey('street', 'address')
    changeKey('zipCode', 'zipcode')
    changeKey('createdAt', 'added')
    changeKey('memberState', 'memberState')
    return store
  })
}

export async function requestStoreTeamMembership (storeId, userId) {
  return post(`/stores/${storeId}/requests/${userId}`)
}

export async function acceptStoreRequest (storeId, userId, moveToStandby) {
  return patch(`/stores/${storeId}/requests/${userId}`, { moveToStandby })
}

export async function declineStoreRequest (storeId, userId) {
  return remove(`/stores/${storeId}/requests/${userId}`)
}

export async function promoteToStoreManager (storeId, userId) {
  return patch(`/stores/${storeId}/managers/${userId}`)
}

export async function demoteAsStoreManager (storeId, userId) {
  return remove(`/stores/${storeId}/managers/${userId}`)
}

export async function addStoreMember (storeId, userId) {
  return post(`/stores/${storeId}/members/${userId}`)
}

export async function removeStoreMember (storeId, userId) {
  return remove(`/stores/${storeId}/members/${userId}`)
}

export async function moveMemberToStandbyTeam (storeId, userId) {
  return patch(`/stores/${storeId}/members/${userId}/standby`)
}

export async function moveMemberToRegularTeam (storeId, userId) {
  return remove(`/stores/${storeId}/members/${userId}/standby`)
}

export async function getStoreLog (storeId, storeActionLogsAsArray) {
  return get(`/stores/${storeId}/log/${storeActionLogsAsArray.join(',')}`)
}
