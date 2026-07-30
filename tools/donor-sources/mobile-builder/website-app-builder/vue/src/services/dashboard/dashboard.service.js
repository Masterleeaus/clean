import api from "../api";

class DashboardService {

  getTotalData() {
    return api.get(`admin/dashboard/total`, {});
  }

  getChart(period) {
    return api.get(`admin/dashboard/chart?period=${period}`, {});
  }
}

export default new DashboardService();
