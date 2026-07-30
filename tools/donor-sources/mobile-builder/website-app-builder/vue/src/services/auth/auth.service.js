import api from "../api";

class AuthService {
  loginWithEmail(email, password) {
    return api.post(`public/auth/login`, {
      email: email,
      password: password
    });
  }

  createAccount(email, password, re_password) {
    return api.post(`public/auth/sign_up`, {
      email: email,
      password: password,
      re_password: re_password
    });
  }

  createForgotRequest(email) {
    return api.post(`public/auth/forgot`, {
      email: email
    });
  }

  resetPassword(token) {
    return api.post(`public/auth/reset?token=${token}`, {});
  }

  loginWithGoogle(token) {
    return api.post(`public/auth/google`, {
      token: token
    });
  }
}

export default new AuthService();
