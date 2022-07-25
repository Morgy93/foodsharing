import { get, patch, post, remove } from './base'

export function login (email, password, rememberMe) {
  return post('/user/login', { email, password, remember_me: rememberMe })
}

export function getUser () {
  return get('/user/current')
}

export function getDetails () {
  return get('/user/current/details')
}

export function deleteUser (id, reason) {
  return remove(`/user/${id}`, {
    reason: reason,
  })
}

export function registerUser (firstName, lastName, email, password, gender, birthdate, mobilePhone, subscribeNewsletter) {
  return post('/user', {
    firstname: firstName,
    lastname: lastName,
    email: email,
    password: password,
    gender: gender,
    birthdate: birthdate,
    mobilePhone: mobilePhone,
    subscribeNewsletter: subscribeNewsletter,
  })
}

export function testRegisterEmail (email) {
  return post('/user/isvalidemail', { email: email })
}

export function setSleepStatus (mode, from, to, message) {
  return patch('/user/sleepmode', {
    mode: mode,
    from: from,
    to: to,
    message: message,
  })
}
