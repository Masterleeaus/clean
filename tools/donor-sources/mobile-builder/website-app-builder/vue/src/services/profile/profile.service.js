import api from "../api";

class ProfileService {
  getMainSettings() {
    return api.get(`private/profile/main`, {});
  }

  getSubscribes(offset) {
    return api.get(`private/profile/subscribes?offset=${offset}`, {});
  }

  getTransactions(offset, uid) {
    return api.get(`private/profile/transactions?offset=${offset}&uid=${uid}`, {});
  }

  updatePassword(password, newPassword) {
    return api.post(`private/profile/update_password`, {
      password: password,
      new_password: newPassword
    });
  }
}

export default new ProfileService();
