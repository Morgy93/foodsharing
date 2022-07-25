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
