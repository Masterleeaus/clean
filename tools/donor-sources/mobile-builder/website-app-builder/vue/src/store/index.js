import adminUpdatesService from "@/services/updates/admin.updates.service";
import Vuex from "vuex";

const store = new Vuex.Store({
  state: {
    language: {
      values: [],
      list: [],
    },
    admin_language: {
      list: [],
      status: false,
    },
    config: {
      logo: "",
      google: {
        enabled: false,
        id: "",
      },
      stripe_key: "",
      ionic_icons: "",
      qr_preview: false,
      currency: "$",
    },
    version: {
      ui_compatibility_date: "",
      compatibility_date: "",
      updates_available: false,
      repo_updates_installed: false,
      db_updates_installed: false,
      release_notes: [],
    },
    user: {
      email: "",
      login: false,
      token: {
        access: "",
        refresh: "",
      },
      admin: false,
    },
    api_url: "",
    offset: 20,
    header: null,
    snackbar: {
      status: false,
      message: undefined,
    },
    is_open_create_modal: false,
    is_open_payment_modal: false,
    preview_agree: false,
    left_drawer: true,
    right_drawer: true,
    theme: "light",
  },
  getters: {
    version(state) {
      return {
        ...state.version,
        ui_updates_installed:
          state.version.ui_compatibility_date ===
          state.version.compatibility_date,
      };
    },
  },
  mutations: {
    setLogo(state, value) {
      state.config.logo = value;
    },
    setLeftDrawer(state, value) {
      state.left_drawer = value;
    },
    setRightDrawer(state, value) {
      state.right_drawer = value;
    },
    setAuthTokens(state, value) {
      state.user.token = value;
    },
    initialiseVars(state) {
      if (localStorage.getItem("token")) {
        state.user.token = JSON.parse(localStorage.token);
      }
      if (localStorage.getItem("preview_agree")) {
        state.preview_agree = localStorage.preview_agree;
      }
      if (localStorage.getItem("theme")) {
        state.theme = localStorage.theme;
      }
    },
    setUser(state, value) {
      state.user = value;
    },
    setUserEmail(state, value) {
      state.user.email = value.email;
      state.user.admin = value.admin;
    },
    setUserPlan(state, value) {
      state.user.plan = value;
    },
    setApiUrl(state, value) {
      state.api_url = value;
    },
    setOffset(state, value) {
      state.offset = value;
    },
    setLanguage(state, value) {
      state.language = value;
    },
    setConfig(state, value) {
      state.config = value;
    },
    setLanguageHeader(state, value) {
      state.header = value;
    },
    openSnackbar(state, value) {
      state.snackbar = {
        status: true,
        message: value,
      };
    },
    hideSnackbar(state) {
      state.snackbar.status = false;
    },
    switchOpenCreateModal(state) {
      state.is_open_create_modal = !state.is_open_create_modal;
    },
    switchOpenPaymentModal(state) {
      state.is_open_payment_modal = !state.is_open_payment_modal;
    },
    setPreviewAgree(state, value) {
      state.preview_agree = value;
    },
    setTheme(state, value) {
      state.theme = value;
    },
    setUiCompatibilityDate(state, value) {
      state.version.ui_compatibility_date = value;
    },
    setVersion(state, value) {
      state.version = {
        ...state.version,
        ...value,
      };
    },
  },
  actions: {
    logout({ commit }) {
      commit("setUser", {
        email: "",
        login: false,
        token: {
          access: "",
          refresh: "",
        },
        admin: false,
      });
    },
    fetchVersion({ commit }) {
      adminUpdatesService.getVersion().then(({ data: version }) => {
        commit("setVersion", version.data);
      });
    },
  },
});

store.subscribe((mutation, state) => {
  if (mutation.type === "setAuthTokens" || mutation.type === "setUser") {
    localStorage.setItem("token", JSON.stringify(state.user.token));
  }
  if (mutation.type === "setPreviewAgree") {
    localStorage.setItem("preview_agree", state.preview_agree);
  }
  if (mutation.type === "setTheme") {
    localStorage.setItem("theme", state.theme);
  }
});

export default store;
