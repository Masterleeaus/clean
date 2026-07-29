import store from "@/store/index";
import axios from "axios";
import Jwt from "@/services/jwt";

const instance = axios.create({baseURL: "", headers: {}});

instance.interceptors.request.use(
  (config) => {
    config.baseURL = store.state.api_url;
    const originalContentType = config.headers['Content-Type'];
    config.headers["Accept-Language"] = "en";
    config.headers['Content-Type'] = originalContentType;
    const token = Jwt.getAccessToken();
    if (token) {
      config.headers["Authorization"] = token;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

instance.interceptors.response.use(
  (res) => {
    return res;
  },
  async (err) => {
    const originalConfig = err.config;
    const originalContentType = originalConfig.headers['Content-Type'];
    if ((err.response.status === 401) && !originalConfig._retry) {
      try {
        const rs = await instance.post("public/auth/refresh", {
          token: Jwt.getRefreshToken(),
        });
        const tokens = rs.data.token;
        Jwt.updateTokens(tokens);
        originalConfig._retry = true;
        originalConfig.headers['Content-Type'] = originalContentType;
        return instance(originalConfig);
      } catch (_error) {
        Jwt.cleanTokens();
        return Promise.reject(_error);
      }
    }
    return Promise.reject(err);
  }
);

export default instance;
