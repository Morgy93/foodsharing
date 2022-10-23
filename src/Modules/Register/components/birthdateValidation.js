import dateFormatter from '@/helper/date-formatter'

export function ageCheck (value) {
  const age = dateFormatter.getDifferenceToNowInYears(value)
  return age >= 18 && age < 125
}

export function dateValid (value) {
  return !isNaN((value instanceof Date ? value : new Date(value)).getTime())
}
