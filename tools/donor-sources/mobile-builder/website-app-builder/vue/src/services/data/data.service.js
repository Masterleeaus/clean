import api from "../api";

class DataService {
  getIso() {
    return api.get(`public/data/iso`, {});
  }

  getIcons() {
    return api.get(`public/data/icons`, {});
  }
}

export default new DataService();
