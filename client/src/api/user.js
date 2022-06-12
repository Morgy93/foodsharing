import { post, remove } from './base'

export function login (email, password, rememberMe) {
  return post('/user/login', { email, password, remember_me: rememberMe })
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
