import api from "../api";

class ProvidersService {
  getPaymentProviders() {
    return api.get(`admin/providers/list`, {});
  }

  updatePaymentProvider(id, data) {
    return api.post(`admin/providers/update?id=${id}`, {
      name: data.name,
      status: data.status,
      api_value_1: data.api_value_1,
      api_value_2: data.api_value_2,
      api_value_3: data.api_value_3,
      config_json: data.config_json,
    });
  }
}

export default new ProvidersService();
