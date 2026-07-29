import store from "../store/index";

class Jwt {

    getRefreshToken() {
        const tokens = store.state.user.token;
        return tokens.refresh;
    }

    getAccessToken() {
      const tokens = store.state.user.token;
      return tokens.access;
    }

    updateTokens(tokens) {
      store.commit('setAuthTokens', tokens);
    }

    cleanTokens() {
      store.commit('setAuthTokens', {
        access: "",
        refresh: ""
      });
    }
}

export default new Jwt();
