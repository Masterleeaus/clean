import api from "../api";

class ProjectsService {
  getProjects(offset) {
    return api.get(`private/projects/list?offset=${offset}`, {});
  }

  getProject(uid) {
    return api.get(`private/projects/detail?uid=${uid}`, {});
  }

  createProject(data) {
    return api.post(`private/projects/create`, {
      link: data.link,
      name: data.name,
      template: data.template,
      color: data.color,
      theme: data.theme
    });
  }

  removeProject(uid) {
    return api.post(`private/projects/remove?uid=${uid}`, {});
  }

  getMainInfo(uid) {
    return api.get(`private/projects/settings/main?uid=${uid}`, {});
  }

  updateMainSettings(uid, data) {
    return api.post(`private/projects/settings/update_main?uid=${uid}`, {
      link: data.link,
      name: data.name,
      app_id: data.app_id,
      user_agent: data.user_agent,
      orientation: data.orientation,
      language: data.language,
      email: data.email
    });
  }

  getDesignSettings(uid) {
    return api.get(`private/projects/settings/template?uid=${uid}`, {});
  }

  updateTemplateSettings(uid, data) {
    return api.post(`private/projects/settings/template_update?uid=${uid}`, {
      color_theme: data.color_theme,
      color_title: data.color_title,
      template: data.template,
      loader: data.loader,
      pull_to_refresh: data.pull_to_refresh,
      loader_color: data.loader_color,
      display_title: data.display_title,
      icon_color: data.icon_color,
      active_color: data.active_color
    });
  }

  getDrawerSettings(uid) {
    return api.get(`private/projects/settings/drawer?uid=${uid}`, {});
  }

  uploadDrawerBackground(uid, image) {
    const formData = new FormData();
    formData.append("background", image);
    return api.post(`private/projects/settings/update_drawer_background?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  uploadDrawerLogo(uid, image) {
    const formData = new FormData();
    formData.append("logo", image);
    return api.post(`private/projects/settings/update_drawer_logo?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  updateDrawerSettings(uid, data) {
    return api.post(`private/projects/settings/update_drawer?uid=${uid}`, {
      mode: data.mode,
      color: data.color,
      theme: data.theme,
      logo_enabled: data.logo_enabled,
      title: data.title,
      subtitle: data.subtitle
    });
  }

  getStylesList(uid) {
    return api.get(`private/projects/settings/styles?uid=${uid}`, {});
  }

  createDivForHide(uid, name) {
    return api.post(`private/projects/settings/create_div?uid=${uid}`, {
      name: name,
    });
  }

  removeDivForHide(div_id) {
    return api.post(`private/projects/settings/remove_div`, {
      div_id: div_id,
    });
  }

  getPermissions(uid) {
    return api.get(`private/projects/settings/permissions?uid=${uid}`, {});
  }

  updatePermissions(uid, data) {
    return api.post(`private/projects/settings/update_permissions?uid=${uid}`, {
      gps: data.gps,
      gps_description: data.gps_description,
      camera: data.camera,
      camera_description: data.camera_description,
      microphone: data.microphone,
      microphone_description: data.microphone_description
    });
  }

  getLocalizations(uid) {
    return api.get(`private/projects/settings/localization?uid=${uid}`, {});
  }

  updateLocalization(uid, data) {
    return api.post(`private/projects/settings/update_localization?uid=${uid}`, {
      id: data.id,
      name: data.name,
    });
  }

  refreshLocalization(uid, id) {
    return api.post(`private/projects/settings/refresh_localization?uid=${uid}`, {
      id: id
    });
  }

  uploadOfflineImage(uid, image) {
    const formData = new FormData();
    formData.append("offline_image", image);
    return api.post(`private/projects/settings/update_offline_img?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  uploadErrorImage(uid, image) {
    const formData = new FormData();
    formData.append("error_image", image);
    return api.post(`private/projects/settings/update_error_img?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  getSplashscreenSettings(uid) {
    return api.get(`private/projects/settings/splashscreen?uid=${uid}`, {});
  }

  updateSplashScreenSettings(uid, data) {
    return api.post(`private/projects/settings/update_splashscreen?uid=${uid}`, {
      background_mode: data.background_mode,
      color: data.color,
      tagline: data.tagline,
      delay: data.delay,
      theme: data.theme,
      use_logo: data.use_logo
    });
  }

  uploadSplashScreenBackground(uid, image) {
    const formData = new FormData();
    formData.append("screen", image);
    return api.post(`private/projects/settings/upload_splash_background?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  uploadSplashScreenLogo(uid, image) {
    const formData = new FormData();
    formData.append("logo", image);
    return api.post(`private/projects/settings/upload_splash_logo?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  getLaunchIcons(uid) {
    return api.get(`private/projects/settings/launch_icon?uid=${uid}`, {});
  }

  downloadIcon(uid, name, type) {
    return api.post(`private/projects/settings/download_icon?uid=${uid}`, {
      type: type,
      name: name
    }, {responseType: "blob"});
  }

  uploadIcon(uid, image) {
    const formData = new FormData();
    formData.append("icon", image);
    return api.post(`private/projects/settings/upload_icon?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  getMainNavigation(uid) {
    return api.get(`private/projects/settings/main_navigation?uid=${uid}`, {});
  }

  createNavigationItem(uid, target, data) {
    let link = `private/projects/settings/create_main_nav?uid=${uid}`;
    if (target === 'bar') {
      link = `private/projects/settings/create_bar_nav?uid=${uid}`;
    }
    return api.post(link, {
      name: data.name,
      action_type: data.action_type,
      icon: data.icon,
      link: data.link
    });
  }

  updateNavigationItem(id, target, data) {
    let link = `private/projects/settings/update_main_nav?id=${id}`;
    if (target === 'bar') {
      link = `private/projects/settings/update_bar_nav?id=${id}`;
    }
    return api.post(link, {
      name: data.name,
      action_type: data.action_type,
      icon: data.icon,
      link: data.link
    });
  }

  removeNavigationItem(id, target) {
    let link = `private/projects/settings/remove_main_nav?id=${id}`;
    if (target === 'bar') {
      link = `private/projects/settings/remove_bar_nav?id=${id}`;
    }
    return api.post(link, {});
  }

  getBarNavigation(uid) {
    return api.get(`private/projects/settings/bar_navigation?uid=${uid}`, {});
  }

  getSigningShort(uid, target) {
    return api.get(`private/projects/signatures/short_list?uid=${uid}&target=${target}`, {});
  }

  getBuildsList(uid) {
    return api.get(`private/projects/builds/list?uid=${uid}`, {});
  }

  createBuild(uid, data) {
    return api.post(`private/projects/builds/create?uid=${uid}`, {
      version: data.version,
      platform: data.platform,
      format: data.format,
      android_key_id: data.android_key_id,
      ios_key_id: data.ios_key_id,
      publish: data.publish
    });
  }

  getSignList(uid) {
    return api.get(`private/projects/signatures/list?uid=${uid}`, {});
  }

  uploadAndroidSign(uid, data, file) {
    const formData = new FormData();
    formData.append("keystore", file);
    formData.append("name", data.name);
    formData.append("alias", data.alias);
    formData.append("keystore_password", data.keystore_password);
    formData.append("key_password", data.key_password);
    return api.post(`private/projects/signatures/upload_android?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  createAndroidSign(uid, name) {
    return api.post(`private/projects/signatures/create_android?uid=${uid}`, {
      name: name
    });
  }

  removeAndroidSign(uid) {
    return api.post(`private/projects/signatures/remove_android?uid=${uid}`, {});
  }

  removeIosSign(uid) {
    return api.post(`private/projects/signatures/remove_ios?uid=${uid}`, {});
  }
  uploadIosSign(uid, data, file) {
    const formData = new FormData();
    formData.append("api_key", file);
    formData.append("name", data.name);
    formData.append("issuer_id", data.issuer_id);
    formData.append("key_id", data.key_id);
    return api.post(`private/projects/signatures/upload_ios?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  downloadArtefact(uid) {
    return api.post(`private/projects/builds/download?uid=${uid}`, {});
  }

  getTotalSubscribes(uid) {
    return api.get(`private/projects/newsletter/subscribes?uid=${uid}`, {});
  }

  getPushHistory(uid, offset) {
    return api.get(`private/projects/newsletter/notifications?uid=${uid}&offset=${offset}`, {});
  }

  sendPushNotification(uid, data, file) {
    const formData = new FormData();
    if (file) {
      formData.append("image", file);
    }
    formData.append("title", data.title);
    formData.append("message", data.message);
    return api.post(`private/projects/newsletter/create?uid=${uid}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }

  launchPreview(uid, mode) {
    return api.post(`private/projects/preview/launch?uid=${uid}&mode=${mode}`, {});
  }

  getPreviewConfig(uid, mode) {
    return api.post(`private/projects/preview/config?uid=${uid}&mode=${mode}`, {});
  }
}

export default new ProjectsService();
