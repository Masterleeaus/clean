import api from "../api";

class AdminConfigService {

  getWebsiteSettings() {
    return api.get(`admin/settings/website`, {});
  }

  updateWebsiteSettings(data) {
    return api.post(`admin/settings/update_website`, {
      site_name: data.site_name,
      site_url: data.site_url,
      currency_code: data.currency_code,
      currency_symbol: data.currency_symbol,
      google_id: data.google_id,
      google_enabled: data.google_enabled
    });
  }

  uploadWebsiteLogo(image) {
    const formData = new FormData();
    formData.append("logo", image);
    return api.post(`admin/settings/upload_logo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  getExternalApiSettings() {
    return api.get(`admin/settings/api`, {});
  }

  updateExternalApiSettings(data) {
    return api.post(`admin/settings/update_api`, {
      github_username: data.github_username,
      github_token: data.github_token,
      github_repo: data.github_repo,
      codemagic_key: data.codemagic_key,
      codemagic_id: data.codemagic_id,
      github_branch: data.github_branch
    });
  }

  getOnesignalSettings() {
    return api.get(`admin/settings/onesignal`, {});
  }

  updateOnesignalSettings(data) {
    return api.post(`admin/settings/update_onesignal`, {
      one_signal_auth_key: data.one_signal_auth_key,
      one_signal_organization_id: data.one_signal_organization_id
    });
  }

  uploadFcm(file) {
    const formData = new FormData();
    formData.append("fcm", file);
    return api.post(`admin/settings/upload_fcm`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  getEmailSettings() {
    return api.get(`admin/settings/email`, {});
  }

  updateEmailSettings(data) {
    return api.post(`admin/settings/update_email`, {
      host: data.host,
      user: data.user,
      port: data.port,
      timeout: data.timeout,
      charset: data.charset,
      sender: data.sender,
      password: data.password,
    });
  }

  getLicense() {
    return api.get(`admin/settings/license`, {});
  }

  activationLicense(code) {
    return api.post(`admin/settings/license_activation`, {
      code: code
    });
  }
}

export default new AdminConfigService();
