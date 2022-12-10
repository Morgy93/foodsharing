export default class Foodsharing {
  async get(path) {
    return this.fetch(path);
  }

  async post(path, opts = {}) {
    return this.fetch(path, {
      ...opts,
      method: 'POST',
    });
  }

  async patch(path, opts = {}) {
    return this.fetch(path, {
      ...opts,
      method: 'PATCH',
    });
  }

  async delete(path, opts = {}) {
    return this.fetch(path, {
      ...opts,
      method: 'DELETE',
    });
  }

  getToken() {
    return document.cookie.split(';')?.find((item) => item.trim().startsWith('CSRF_TOKEN='))?.replace('CSRF_TOKEN=', '')?.trim();
  }

  async fetch(path, opts = {}) {

    return $fetch(`/api/${path}`, {
      headers: {
        'X-CSRF-Token': this.getToken(),
      }, ...(opts && { ...opts })
    })
  }

  async login(data) {
    await this.fetch(`user/login`, {
      method: 'POST',
      body: JSON.stringify(data)
    })

    return this.getToken();
  }

  async logout() {
    const response = await this.fetch(`user/logout`, {
      method: 'POST',
    })

    return response;
  }
}
