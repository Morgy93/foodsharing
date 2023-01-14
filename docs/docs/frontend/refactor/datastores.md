# Datastores

Datastores, short Stores, have nothing to do with the classic stores you go to shop (or save food from). They are an abstraction layer between the API and the the frontend and reduce the amount of API Calls because all responses to the API are stored and accessible among all components.

```plantuml
"Components" --> "Stores": Mutations
"Components" <-- "Stores": Getter

"Stores" --> "Stores": Mutations
"Stores" <-- "Stores": Getter

"Stores" --> "API-Wrappers": Call
"Stores" <-- "API-Wrappers": Response

"API-Wrappers" --> "ENDPOINTs": Request
"API-Wrappers" <-- "ENDPOINTs": Response
```

# Getter / Mutations
Getters and Mutators are conventions in many programming languages to simplyfy the codebase and improve readability. They are also known as Getters and Setters or Accessors and Assigners. The latter modifies or mutates or assigns a (new) value to the attrubute of a class, the first only retrieves or gets the value of a specified attribute.

## Getters
These functions should be used to check, filter or get values like `isFoodsaver()`, `hasID(1337)` or `getConversationByID(1337)`. As a naming convention you should use __get__ + whatever value you want to retrieve. <!-- ToDo hasID isn't a getter? It calls one maybe. -->

## Mutations
These functions should be used when manipulating something, like `fetchUserDetailsData()`, `setAcceptedStatus(id)` or `updateReadStatus(id)`


# Basic Store
<!-- What is being describedin the following code snippets? -->

## Setup
Located at [client/src/stores](https://gitlab.com/foodsharing-dev/foodsharing/-/tree/master/client/src/stores)

```js
// example.js
import Vue from 'vue'

export const store = Vue.observable({
  state: false,
})

export const getters = {
  getState: () => store.state,
}

export const mutations = {
  async toggle() {
    store.state = !store.state
  },
}

export default { store, getters, mutations }
```

## Usage
```js
// example.vue
import DataStore from '@/stores/example.js'

console.log('get', DataStore.getters.getState())
DataStore.mutations.toggle()
console.log('mutations', DataStore.getters.getState())
```
