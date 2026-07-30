import api from "../api";

class AdminSubscribeService {

  getTransactions(offset) {
    return api.get(`admin/transactions/list?offset=${offset}`, {});
  }

  getUserSubscribes(offset, user_id) {
    return api.get(`admin/transactions/user_subscribe?offset=${offset}&user_id=${user_id}`, {});
  }

  getSubscribe(subscription_uid, offset) {
    return api.get(`admin/transactions/subscribe?subscription_uid=${subscription_uid}&offset=${offset}`, {});
  }
  
  makeGift(data) {
    return api.post(`admin/transactions/gift`, {
      plan_id: data.plan_id,
      user_id: data.user_id,
      app_uid: data.app_uid,
    });
  }

}

export default new AdminSubscribeService();
