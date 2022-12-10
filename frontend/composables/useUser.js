import Foodsharing from '@/api/foodsharing';
const foodsharing = new Foodsharing();

const STATE = 'user';
const EMPTY = null;

useState(STATE, () => null);

export default {
  get: function () {
    return useState(STATE).value;
  },

  set: async function () {
    if(this.get() === null) {
      const response = await foodsharing.get('user/current/details');
      useState(STATE).value = response;
      writeToStorage(response);
    }
  },

  remove: async function () {
    useState(STATE).value = EMPTY;
    localStorage.removeItem(STATE);
  },

  read: function () {
    const data = loadFromStorage();
    useState(STATE).value = data;
  },
}

function writeToStorage(value) {
  localStorage.setItem(STATE, JSON.stringify(value));
}

function loadFromStorage() {
  return JSON.parse(localStorage.getItem(STATE));
}
