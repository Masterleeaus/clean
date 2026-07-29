import api from "../api";

class AdminUpdatesService {
  getRepositoryUpdateStatus() {
    return api.get('admin/settings/updates/repository/update_status', {});
  }

  installRepositoryUpdates() {
    return api.post('admin/settings/updates/repository/install', {});
  }

  getVersion() {
    return api.get('admin/settings/updates/version', {});
  }
}

export default new AdminUpdatesService();
