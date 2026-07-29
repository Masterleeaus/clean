<template>
  <ScreenLock v-if="loading"/>
  <v-app v-else>
    <v-navigation-drawer
      app
      :width="66"
      permanent
    >
      <AppSidebar/>
    </v-navigation-drawer>
    <AdminSidebar/>
    <AdminEditorSidebar ref="editor_side" v-if="sidebar === 'editor'" location="right" :width="380"/>
    <AdminSupportSidebar v-if="sidebar === 'support'" location="right" :width="380"/>
    <CustomerSidebar v-if="sidebar === 'profile'" location="right" :width="380"/>
    <SettingsSidebar v-if="sidebar === 'settings'" location="right" :width="380"/>
    <v-main>
      <router-view @force_update_name="$refs.editor_side.getAppDetail(true)"/>
    </v-main>
    <v-dialog
      v-model="$store.state.is_open_create_modal"
      fullscreen
      :scrim="false"
      :close-on-content-click="false"
      :persistent="true"
      :no-click-animation="true"
      transition="dialog-bottom-transition"
    >
      <CreateProject/>
    </v-dialog>
  </v-app>
</template>

<script>
import AppSidebar from "@/components/sidebars/AppSidebar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import profileService from "@/services/profile/profile.service";
import CreateProject from "@/components/modals/CreateProject.vue";
import AdminSidebar from "@/components/sidebars/AdminSidebar.vue";
import AdminEditorSidebar from "@/components/sidebars/AdminEditorSidebar.vue";
import AdminSupportSidebar from "@/components/sidebars/AdminSupportSidebar.vue";
import CustomerSidebar from "@/components/sidebars/CustomerSidebar.vue";
import SettingsSidebar from "@/components/sidebars/SettingsSidebar.vue";

export default {
  name: 'AdminLayout',
  components: {
    SettingsSidebar,
    CustomerSidebar,
    AdminSupportSidebar,
    AdminEditorSidebar,
    ScreenLock,
    AdminSidebar,
    AppSidebar,
    CreateProject
  },
  data: () => ({
    loading: true,
    sidebar: null,
  }),
  watch: {
    '$store.state.user.token': function() {
      this.checkAccess();
    },
    '$route.meta': function() {
      this.sidebarChecking();
    },
  },
  methods: {
    checkAccess() {
      if (!this.$store.state.user.token.access.length) {
        this.$router.push({ path: '/', replace: true });
      }
      if (!this.$store.state.user.admin) {
        this.$router.push({ path: '/private/apps', replace: true });
      }
    },
    getProfileData() {
      if (this.$store.state.user.email.length) {
        this.checkAccess();
        this.loading = false;
        return;
      }
      profileService.getMainSettings().then((res) => {
        const data = res.data;
        this.$store.commit('setUserEmail', data);
        this.$store.commit('setUserPlan', data.plan);
        this.checkAccess();
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    sidebarChecking() {
      if (Object.keys(this.$route.meta).length) {
        this.sidebar = this.$route.meta["sidebar"];
      } else {
        this.sidebar = null;
      }
    },
  },
  beforeMount() {
    this.sidebarChecking();
    this.getProfileData();
  },
};
</script>
