import api from "../api";

class PlansService {
  getPlans() {
    return api.get(`admin/plans/list`, {});
  }

  createPlan(data) {
    return api.post(`admin/plans/create`, {
      count: data.count,
      price: data.price,
      save: data.save,
      api_id: data.api_id,
      build_count: data.build_count,
      type: data.type,
    });
  }

  updatePlan(id, data) {
    return api.post(`admin/plans/update?id=${id}`, {
      count: data.count,
      price: data.price,
      save: data.save,
      api_id: data.api_id,
      build_count: data.build_count,
      type: data.type,
    });
  }

  removePlan(id, data) {
    return api.post(`admin/plans/remove?id=${id}`, {});
  }
}

export default new PlansService();
