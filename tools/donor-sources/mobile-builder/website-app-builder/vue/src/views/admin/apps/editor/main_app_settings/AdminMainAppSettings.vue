<template>
  <PageBar :title="$tr('menu', 'key_11')" is-back>
    <template v-if="!loading" v-slot:action>
      <v-menu location="center">
        <template v-slot:activator="{ props }">
          <v-btn icon="mdi-dots-vertical" v-bind="props"/>
        </template>
        <v-list>
          <v-list-item @click="modalRemoveIsOpen = true">
            <v-list-item-title class="text-red-accent-3">
              {{ $tr('project', 'key_388') }}
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
  </PageBar>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_7')"
          variant="outlined"
          density="comfortable"
          v-model="name"
        ></v-text-field>
        <v-text-field
          :label="$tr('project', 'key_8')"
          variant="outlined"
          density="comfortable"
          v-model="link"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_27')"
          variant="outlined"
          density="comfortable"
          v-model="appId"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_29')"
          variant="outlined"
          density="comfortable"
          :items="orientItems"
          v-model="orientation"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_109')"
          variant="outlined"
          density="comfortable"
          readonly
          append-inner-icon="mdi-unfold-more-horizontal"
          v-model="language"
          @click="modalLangIsOpen = true"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_129')"
          variant="outlined"
          density="comfortable"
          v-model="email"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_109')"
          variant="outlined"
          density="comfortable"
          v-model="oneSignalId"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_110')"
          variant="outlined"
          density="comfortable"
          v-model="oneSignalRest"
        >
          <template v-slot:append-inner>
            <v-btn
              variant="text"
              color="primary"
              icon=""
              density="comfortable"
              :disabled="!oneSignalId"
              @click="openOneSignal"
            >
              <v-icon size="20">mdi-open-in-new</v-icon>
            </v-btn>
          </template>
        </v-text-field>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-textarea
          :label="$tr('project', 'key_28')"
          variant="outlined"
          density="comfortable"
          v-model="user_agent"
        ></v-textarea>
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateMain">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
  <v-dialog
    max-width="680"
    scrollable
    v-model="modalLangIsOpen"
  >
    <SelectLanguage
      @close="modalLangIsOpen = false"
      @change="changeIso"
    />
  </v-dialog>
  <v-dialog
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="modalRemoveIsOpen"
  >
    <RemoveApp
      :uid="$route.params.uid"
      is-admin
      @close="modalRemoveIsOpen = false"
    />
  </v-dialog>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import SelectLanguage from "@/components/modals/SelectLanguage.vue";
import RemoveApp from "@/components/modals/RemoveApp.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'AdminMainAppSettings',
  components: {RemoveApp, SelectLanguage, FixedFooter, ScreenLock, PageBar},
  data: () => ({
    loading: true,
    name: "",
    link: "",
    appId: "",
    email: "",
    orientation: 0,
    user_agent: "",
    language: "",
    modalLangIsOpen: false,
    isUpdate: false,
    modalRemoveIsOpen: false,
    oneSignalId: "",
    oneSignalRest: ""
  }),
  watch: {
    '$route.params.uid': function() {
      this.loading = true;
      this.getSettingsData();
    },
  },
  computed: {
    orientItems: function () {
      return [
        {
          title: this.$tr('project', 'key_30'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_31'),
          value: 1
        },
        {
          title: this.$tr('project', 'key_32'),
          value: 2
        }
      ];
    }
  },
  methods: {
    openOneSignal() {
      window.open(`https://dashboard.onesignal.com/apps/${this.oneSignalId}/settings/keys_and_ids`, '_blank');
    },
    getSettingsData() {
      adminProjectsService.getMainInfo(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.name = data.name;
        this.link = data.link;
        this.appId = data.app_id;
        this.email = data.email;
        this.orientation = data.orientation;
        this.user_agent = data.user_agent;
        this.language = data.language;
        this.oneSignalId = data.one_signal_id;
        this.oneSignalRest = data.one_signal_rest;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    changeIso(val) {
      this.language = val.toUpperCase();
      this.modalLangIsOpen = false;
    },
    updateMain() {
      this.isUpdate = true;
      adminProjectsService.updateMainSettings(this.$route.params.uid, {
        link: this.link,
        name: this.name,
        app_id: this.appId,
        user_agent: this.user_agent,
        orientation: this.orientation,
        language: this.language,
        email: this.email,
        one_signal_id: this.oneSignalId,
        one_signal_rest: this.oneSignalRest
      }).then(() => {
        this.isUpdate = false;
        this.$refs.footer.showSuccessAlert();
        this.$emit("force_update_name");
        this.$emit("preview-update");
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    }
  },
  beforeMount() {
    this.getSettingsData();
  }
};
</script>
