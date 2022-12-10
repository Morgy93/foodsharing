import Foodsharing from '@/api/foodsharing';
import useUser from './useUser';
const foodsharing = new Foodsharing();

const STATE = 'auth';
const EMPTY = null;

useState(STATE, () => null);

export default {
  isLoggedIn: function () {
    useAuth.read();
    return this.get() !== EMPTY;
  },

  isSecretUser: function () {
    useAuth.read();
    return this.get().secret !== EMPTY;
  },

  get: function () {
    return useState(STATE).value;
  },

  login: async function (data) {
    try {
      const response = await foodsharing.login(data);
      useState(STATE).value = response;
      writeToStorage(response);
      useUser.set();
    } catch (e) {
      throw createError(e)
    }
  },

  logout: async function () {
    try {
      await foodsharing.logout()
      useState(STATE).value = EMPTY;
      localStorage.removeItem(STATE);
      useUser.remove();
    } catch (e) {
      throw createError(e);
    }
  },

  read: async function () {
    useUser.read();
    const data = loadFromStorage();
    useState(STATE).value = data;
  },

  validate: async function () {
    if (!foodsharing.getToken()) {
      useState(STATE).value = EMPTY;
      localStorage.removeItem(STATE);
    }
  }
}

function writeToStorage(value) {
  localStorage.setItem(STATE, JSON.stringify(value));
}

function loadFromStorage() {
  return JSON.parse(localStorage.getItem(STATE));
}
