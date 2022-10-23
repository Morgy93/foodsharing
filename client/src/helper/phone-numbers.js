import { parsePhoneNumberFromString } from 'libphonenumber-js'

export default {
  callableNumber (number, allowInvalid = false) {
    const phoneNumber = parsePhoneNumberFromString(number || '')
    if (phoneNumber?.isValid()) {
      return phoneNumber.number
    }

    if (/\d/.test(number) && allowInvalid && number.length > 6) {
      return number
    }
    return null
  },
}

export function isValidPhoneNumber (number) {
  const phoneNumber = parsePhoneNumberFromString(number || '')
  return phoneNumber?.isValid() === true // this condition check is required of libphonenumber-js because it sends undefined for invalid numbers
}
