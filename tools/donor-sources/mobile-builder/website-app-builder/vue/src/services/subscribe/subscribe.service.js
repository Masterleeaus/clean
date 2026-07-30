import api from "../api";

class SubscribeService {
  getPlans() {
    return api.get(`private/plans/list`, {});
  }

  getPaymentRoute() {
    return api.get(`private/payment/method`, {});
  }

  createPayment(url, plan_id) {
    return api.post(url, { plan_id });
  }

  getCancelRoute() {
    return api.get(`private/payment/cancel`, {});
  }

  cancelSubscribe(url) {
    return api.post(url);
  }
}

export default new SubscribeService();
