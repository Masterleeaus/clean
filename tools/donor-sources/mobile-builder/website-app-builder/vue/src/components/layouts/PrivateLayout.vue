<template>
  <ScreenLock v-if="loading"/>
  <v-app v-else>
    <v-navigation-drawer
      app
      :width="66"
      :permanent="!sidebar ? false : true"
    >
      <AppSidebar/>
    </v-navigation-drawer>
    <v-navigation-drawer v-if="sidebar === 'editor'" location="right" :width="380">
      <Preview :seqno="seqno" :key="previewKey"/>
    </v-navigation-drawer>
    <EditorSidebar ref="editor_side" v-if="sidebar === 'editor'" @change-premium="changePremium"/>
    <ProfileSidebar v-if="sidebar === 'profile'"/>
    <SupportSidebar v-if="sidebar === 'support'"/>
    <v-main>
      <router-view
        @force_update_name="$refs.editor_side.getAppDetail(true)"
        @preview-update="updateSeqno"
        :premium="premium"
      />
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
    <v-dialog
      v-model="$store.state.is_open_payment_modal"
      fullscreen
      :scrim="false"
      :close-on-content-click="false"
      :persistent="true"
      :no-click-animation="true"
      transition="dialog-bottom-transition"
    >
      <SelectPlane/>
    </v-dialog>
  </v-app>
</template>

<script>
import AppSidebar from "@/components/sidebars/AppSidebar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import profileService from "@/services/profile/profile.service";
import CreateProject from "@/components/modals/CreateProject.vue";
import EditorSidebar from "@/components/sidebars/EditorSidebar.vue";
import Preview from "@/components/sidebars/Preview.vue";
import ProfileSidebar from "@/components/sidebars/ProfileSidebar.vue";
import SelectPlane from "@/components/modals/SelectPlane.vue";
import SupportSidebar from "@/components/sidebars/SupportSidebar.vue";

export default {
  name: 'PrivateLayout',
  components: {
    SupportSidebar,
    SelectPlane, ProfileSidebar, Preview, EditorSidebar, CreateProject, ScreenLock, AppSidebar},
  data: () => ({
    loading: true,
    sidebar: null,
    seqno: 1,
    premium: undefined,
  }),
  computed: {
    previewKey: function () {
      return !this.$route.params.uid ? "start" : this.$route.params.uid
    }
  },
  watch: {
    '$store.state.user.token': function() {
      this.checkAccess();
    },
    '$route.meta': function() {
      this.sidebarChecking();
    },
  },
  methods: {
    changePremium(val) {
      this.premium = val;
    },
    checkAccess() {
      if (!this.$store.state.user.token.access.length) {
        this.$router.push({ path: '/', replace: true });
      }
    },
    getProfileData() {
      if (this.$store.state.user.email.length) {
        this.loading = false;
        return;
      }
      profileService.getMainSettings().then((res) => {
        const data = res.data;
        this.$store.commit('setUserEmail', data);
        this.$store.commit('setUserPlan', data.plan);
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
    updateSeqno() {
      this.seqno = this.seqno + 1;
    }
  },
  beforeMount() {
    this.sidebarChecking();
    this.checkAccess();
    this.getProfileData();
  },
};
</script>
