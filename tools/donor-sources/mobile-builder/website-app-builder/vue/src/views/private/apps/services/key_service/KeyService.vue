<template>
  <PageBar :title="$tr('menu', 'key_25')"/>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <template v-else>
    <Placeholder v-if="!list.length">
      <template v-slot:icon>
        <DataLockIcon :size="56"/>
      </template>
      <template v-slot:title>
        {{ $tr('project', 'key_228') }}
      </template>
      <template v-slot:subtitle>
        {{ $tr('project', 'key_342') }}
      </template>
      <template v-slot:action>
        <div class="d-flex align-center">
          <v-btn variant="tonal" color="primary" @click="isOpenModalAndroid = true">
            {{ $tr('project', 'key_343') }}
          </v-btn>
          <v-btn variant="tonal" color="primary" class="ml-3" @click="isOpenModalIos = true">
            {{ $tr('project', 'key_344') }}
          </v-btn>
        </div>
      </template>
    </Placeholder>
    <v-container fluid v-else>
      <v-row class="mt-3">
        <v-col md="12" sm="12" cols="12" class="py-0">
          <div class="w-100 d-flex justify-space-between align-center mb-3">
            <div class="font-weight-medium text-subtitle-2">
              {{ $tr('project', 'key_346') }}
            </div>
            <v-menu location="center">
              <template v-slot:activator="{ props }">
                <v-btn variant="flat" color="primary" v-bind="props">
                  {{ $tr('project', 'key_345') }}
                </v-btn>
              </template>
              <v-list>
                <v-list-item @click="isOpenModalAndroid = true">
                  <v-list-item-title>{{ $tr('project', 'key_343') }}</v-list-item-title>
                </v-list-item>
                <v-list-item @click="isOpenModalIos = true">
                  <v-list-item-title>{{ $tr('project', 'key_344') }}</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
          <v-list item-props density="comfortable">
            <template v-for="(sign, index) in list">
              <v-list-item lines="one" density="default" class="pa-3">
                <template v-slot:prepend>
                  <BuildAvatar :platform="sign.type" :file="sign.type === 'android' ? 'jks' : 'p8'"/>
                </template>
                <v-list-item-title>{{ sign.name }}</v-list-item-title>
                <v-list-item-subtitle>{{ sign.info }}</v-list-item-subtitle>
                <template v-slot:append>
                  <v-list-item-action>
                    <v-btn
                      variant="text"
                      icon=""
                      color="red-accent-3"
                      density="comfortable"
                      :loading="loadingIndex === index"
                      @click="removeSign(index)"
                    >
                      <DeleteMiniIcon :size="16"/>
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
    v-model="isOpenModalAndroid"
  >
    <CreateAndroidSign
      :uid="$route.params.uid"
      @close="isOpenModalAndroid = false"
      @on-create="createSign"
    />
  </v-dialog>
  <v-dialog
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="isOpenModalIos"
  >
    <CreateIosSign
      :uid="$route.params.uid"
      @close="isOpenModalIos = false"
      @on-create="createSign"
    />
  </v-dialog>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import projectsService from "@/services/projects/projects.service";

export default {
  name: 'KeyService',
  components: {ScreenLock, PageBar},
  data: () => ({
    loading: true,
    list: [],
    isOpenModalAndroid: false,
    isOpenModalIos: false,
    loadingIndex: undefined
  }),
  watch: {

  },
  methods: {
    getSigningList() {
      projectsService.getSignList(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.list = data.list;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    createSign(val) {
      this.list.unshift(val);
      this.isOpenModalAndroid = false;
      this.isOpenModalIos = false;
    },
    removeSign(index) {
      if (this.list[index].type === "android") {
        this.removeAndroidSign(index);
      } else {
        this.removeIosSign(index);
      }
    },
    removeAndroidSign(index) {
      this.loadingIndex = index;
      const uid = this.list[index].uid;
      projectsService.removeAndroidSign(uid).then(() => {
        this.list.splice(index, 1);
        this.loadingIndex = undefined;
      }).catch(e => {
        this.loadingIndex = undefined;
        this.$store.commit('openSnackbar', e);
      });
    },
    removeIosSign(index) {
      this.loadingIndex = index;
      const uid = this.list[index].uid;
      projectsService.removeIosSign(uid).then(() => {
        this.list.splice(index, 1);
        this.loadingIndex = undefined;
      }).catch(e => {
        this.loadingIndex = undefined;
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getSigningList();
  }
};
</script>
<script setup>
import Placeholder from "@/components/blocks/Placeholder.vue";
import DataLockIcon from "@/components/icons/DataLockIcon.vue";
import BuildAvatar from "@/components/blocks/BuildAvatar.vue";
import DeleteMiniIcon from "@/components/icons/DeleteMiniIcon.vue";
import CreateAndroidSign from "@/components/modals/CreateAndroidSign.vue";
import CreateIosSign from "@/components/modals/CreateIosSign.vue";
</script>
