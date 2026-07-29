<template>
  <PageBar :title="$tr('menu', 'key_23')"/>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-alert
          type="info"
          :text="$tr('project', 'key_341')"
          variant="tonal"
        ></v-alert>
        <v-list
          item-props
          density="comfortable"
        >
          <template v-for="(local, index) in locals">
            <v-list-item
              lines="one"
              density="default"
            >
              <v-list-item-title>{{ local.name }}</v-list-item-title>
              <template v-slot:append>
                <v-list-item-action>
                  <v-btn
                    icon=""
                    color="primary"
                    density="comfortable"
                    flat
                    variant="text"
                    @click="openDialog(index)"
                  >
                    <EditMiniIcon :size="16"/>
                  </v-btn>
                  <v-btn
                    icon=""
                    color="red-accent-3"
                    density="comfortable"
                    flat
                    variant="text"
                    :loading="index === refreshIndex"
                    @click="refreshLocal(index)"
                  >
                    <RefreshMiniIcon :size="16"/>
                  </v-btn>
                </v-list-item-action>
              </template>
            </v-list-item>
            <v-divider/>
          </template>
        </v-list>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0 mt-5">
        <div class="font-weight-medium text-subtitle-2 mb-5">
          {{ $tr('project', 'key_150') }}
        </div>
        <ImageUpload
          class="mb-3"
          :src="offline_img"
          :title="$tr('project', 'key_154')"
          :subtitle="$tr('project', 'key_139')"
          :loading="isUploadOffline"
          @change-image="uploadOfflineImage"
        />
        <ImageUpload
          class="mb-3"
          :src="error_img"
          :title="$tr('project', 'key_155')"
          :subtitle="$tr('project', 'key_139')"
          :loading="isUploadError"
          @change-image="uploadErrorImage"
        />
      </v-col>
    </v-row>
  </v-container>
  <v-dialog
    scrollable
    max-width="560"
    v-model="dialog.status"
  >
    <ChangeLocalization
      :loading="dialog.loading"
      @close="dialog.status = false"
      @save="updateLocal"
    >
      <v-text-field
        :label="$tr('project', 'key_295')"
        variant="outlined"
        density="comfortable"
        v-model="dialog.name"
      ></v-text-field>
    </ChangeLocalization>
  </v-dialog>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import projectsService from "@/services/projects/projects.service";
import EditMiniIcon from "@/components/icons/EditMiniIcon.vue";
import RefreshMiniIcon from "@/components/icons/RefreshMiniIcon.vue";
import ChangeLocalization from "@/components/modals/ChangeLocalization.vue";
import ImageUpload from "@/components/forms/ImageUpload.vue";

export default {
  name: 'LocalizationAppSettings',
  components: {ImageUpload, ChangeLocalization, RefreshMiniIcon, EditMiniIcon, ScreenLock, PageBar},
  data: () => ({
    loading: true,
    locals: [],
    offline_img: null,
    error_img: null,
    dialog: {
      name: "",
      loading: false,
      status: false,
      id: 0,
      index: 0
    },
    refreshIndex: undefined,
    isUploadOffline: false,
    isUploadError: false,
  }),
  watch: {

  },
  methods: {
    getLocalizationList() {
      this.loading = true;
      projectsService.getLocalizations(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.locals = data.locals;
        this.offline_img = data.images.offline;
        this.error_img = data.images.error;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    updateLocal() {
      this.dialog.loading = true;
      projectsService.updateLocalization(this.$route.params.uid, {
        id: this.dialog.id,
        name: this.dialog.name,
      }).then(() => {
        this.locals[this.dialog.id-1].name = this.dialog.name;
        this.dialog.status = false;
        this.dialog.loading = false;
      }).catch(e => {
        this.dialog.loading = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    refreshLocal(index) {
      this.refreshIndex = index;
      projectsService.refreshLocalization(this.$route.params.uid, index + 1).then((res) => {
        const data = res.data;
        this.locals[index].name = data.value;
        this.refreshIndex = undefined;
      }).catch(e => {
        this.refreshIndex = undefined;
        this.$store.commit('openSnackbar', e);
      });
    },
    uploadOfflineImage(image) {
      if (!image) {
        return;
      }
      this.isUploadOffline = true;
      projectsService.uploadOfflineImage(this.$route.params.uid, image).then((res) => {
        const data = res.data;
        this.offline_img = data.uri;
        this.isUploadOffline = false;
      }).catch(e => {
        this.isUploadOffline = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    uploadErrorImage(image) {
      if (!image) {
        return;
      }
      this.isUploadError = true;
      projectsService.uploadErrorImage(this.$route.params.uid, image).then((res) => {
        const data = res.data;
        this.error_img = data.uri;
        this.isUploadError = false;
      }).catch(e => {
        this.isUploadError = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    openDialog(index) {
      const item = this.locals[index];
      this.dialog = {
        name: item.name,
        loading: false,
        status: true,
        id: index + 1
      };
    }
  },
  beforeMount() {
    this.getLocalizationList();
  }
};
</script>
