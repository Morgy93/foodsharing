import serverData from '@/helper/server-data'
import de from '@translations/messages.de.yml'
import en from '@translations/messages.en.yml'
import es from '@translations/messages.es.yml'
import fr from '@translations/messages.fr.yml'
import it from '@translations/messages.it.yml'
import nbNo from '@translations/messages.nb_NO.yml'
import tr from '@translations/messages.tr.yml'

export const { locale } = serverData

export default function (path, variables = {}) {
  // find the selected language, use German as fallback
  const language = { en: en, es: es, fr: fr, it: it, nb_NO: nbNo, tr: tr }
  const selected = Object.keys(language).find(l => l.localeCompare(locale || l) === 0)
  const src = selected ? language[selected] : de

  // https://youmightnotneed.com/lodash#get
  const pathArray = Array.isArray(path) ? path : path.match(/([^[.\]])+/g)
  let result = pathArray.reduce((prevObj, key) => prevObj && prevObj[key], src)

  // https://youmightnotneed.com/lodash#get
  if (!result) {
    result = pathArray.reduce((prevObj, key) => prevObj && prevObj[key], de)
  }
  if (!result) {
    console.error(new Error(`Missing translation for [${path}]`))
    return path
  }
  return result.replace(/{([^}]+)}/g, (_, name) => {
    if (Object.prototype.hasOwnProperty.call(variables, name)) {
      return variables[name]
    } else {
      throw new Error(`Variable [${name}] was not provided for [${path}]`)
    }
  })
}
