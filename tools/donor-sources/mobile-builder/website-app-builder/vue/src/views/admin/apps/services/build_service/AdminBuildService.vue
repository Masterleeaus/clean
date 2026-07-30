<template>
  <PageBar :title="$tr('menu', 'key_15')"/>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <template v-else>
    <Placeholder v-if="!list.length">
      <template v-slot:icon>
        <DevicesIcon :size="56"/>
      </template>
      <template v-slot:title>
        {{ $tr('project', 'key_162') }}
      </template>
      <template v-slot:subtitle>
        {{ $tr('project', 'key_161') }}
      </template>
      <template v-slot:action>
        <v-btn variant="tonal" color="primary" @click="openModal">
          {{ $tr('project', 'key_160') }}
        </v-btn>
      </template>
    </Placeholder>
    <v-container fluid v-else>
      <v-row class="mt-3">
        <v-col md="12" sm="12" cols="12" class="py-0">
          <div class="w-100 d-flex justify-space-between align-center mb-3">
            <div class="font-weight-medium text-subtitle-2">
              {{ $tr('project', 'key_304') }}
            </div>
            <v-btn variant="flat" color="primary" @click="openModal">
              {{ $tr('project', 'key_160') }}
            </v-btn>
          </div>
          <v-list item-props density="comfortable">
            <template v-for="(build, index) in list">
              <v-list-item lines="one" density="default" class="pa-3">
                <template v-slot:prepend>
                  <BuildAvatar :platform="build.platform" :file="build.format"/>
                </template>
                <v-list-item-title>{{ build.version }}</v-list-item-title>
                <v-list-item-subtitle>
                  <span>{{ $tr('project', 'key_22') }} {{ build.created }}</span>&nbsp;
                  <span v-if="build.publish"> {{ $tr('project', 'key_197') }}</span>
                </v-list-item-subtitle>
                <v-list-item-subtitle v-if="build.message" class="text-red-accent-3">
                  {{ build.message }}
                </v-list-item-subtitle>
                <template v-slot:append>
                  <v-list-item-action>
                    <BuildStatusBadge class="mr-2" :status="build.status" :is-fail="build.fail"/>
                    <v-btn
                      variant="text"
                      icon=""
                      color="primary"
                      density="comfortable"
                      :disabled="!build.status || build.fail"
                      :loading="loadingIndex === index"
                      @click="download(index)"
                    >
                      <DownloadIcon :size="22"/>
                    </v-btn>
                  </v-list-item-action>
                </template>
              </v-list-item>
              <v-divider/>
            </template>
          </v-list>
        </v-col>
      </v-row>
    </v-container>
  </template>
  <v-dialog
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="isOpenModal"
  >
    <CreateBuild
      :uid="$route.params.uid"
      is-admin
      @close="isOpenModal = false"
      @on-create="addToList"
    />
  </v-dialog>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import Placeholder from "@/components/blocks/Placeholder.vue";
import DevicesIcon from "@/components/icons/DevicesIcon.vue";
import BuildAvatar from "@/components/blocks/BuildAvatar.vue";
import BuildStatusBadge from "@/components/blocks/BuildStatusBadge.vue";
import DownloadIcon from "@/components/icons/DownloadIcon.vue";
import CreateBuild from "@/components/modals/CreateBuild.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'AdminBuildService',
  components: {CreateBuild, DownloadIcon, BuildStatusBadge, BuildAvatar, DevicesIcon, Placeholder, ScreenLock, PageBar},
  props: {
    premium: {
      type: String
    }
  },
  data: () => ({
    loading: true,
    list: [],
    isOpenModal: false,
    loadingIndex: undefined
  }),
  watch: {

  },
  methods: {
    openModal() {
      this.isOpenModal = true;
    },
    addToList(val) {
      this.list.unshift(val);
      this.isOpenModal = false;
    },
    getBuilds() {
      adminProjectsService.getBuildsList(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.list = data.list;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    download(index) {
      this.loadingIndex = index;
      const uid = this.list[index].uid;
      adminProjectsService.downloadArtefact(uid).then((res) => {
        const data = res.data;
        window.open(data.url, "_self");
        this.loadingIndex = undefined;
      }).catch(e => {
        this.loadingIndex = undefined;
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getBuilds();
  }
};
</script>
