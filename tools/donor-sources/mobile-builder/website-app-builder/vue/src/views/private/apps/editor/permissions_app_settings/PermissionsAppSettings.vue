<template>
  <PageBar :title="$tr('menu', 'key_39')"/>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="12" sm="12" cols="12" class="py-0">
        <FlexAvatarIconBlock
          :title="$tr('project', 'key_121')"
          :subtitle="$tr('project', 'key_122')"
          class="mb-3"
        >
          <template v-slot:icon>
            <LocationIcon :size="32" class="text-primary"/>
          </template>
          <template v-if="gps.status" v-slot:input>
            <v-textarea
              :label="$tr('project', 'key_340')"
              variant="outlined"
              density="compact"
              rows="2"
              hide-details
              class="mt-5"
              v-model="gps.description"
            ></v-textarea>
          </template>
          <template v-slot:action>
            <v-switch
              :true-value="1"
              :false-value="0"
              color="primary"
              hide-details
              v-model="gps.status"
            ></v-switch>
          </template>
        </FlexAvatarIconBlock>
        <FlexAvatarIconBlock
          :title="$tr('project', 'key_123')"
          :subtitle="$tr('project', 'key_124')"
          class="mb-3"
        >
          <template v-slot:icon>
            <CameraIcon :size="32" class="text-primary"/>
          </template>
          <template v-if="camera.status" v-slot:input>
            <v-textarea
              :label="$tr('project', 'key_340')"
              variant="outlined"
              density="compact"
              rows="2"
              hide-details
              class="mt-5"
              v-model="camera.description"
            ></v-textarea>
          </template>
          <template v-slot:action>
            <v-switch
              :true-value="1"
              :false-value="0"
              color="primary"
              hide-details
              v-model="camera.status"
            ></v-switch>
          </template>
        </FlexAvatarIconBlock>
        <FlexAvatarIconBlock
          :title="$tr('project', 'key_125')"
          :subtitle="$tr('project', 'key_124')"
          class="mb-3"
        >
          <template v-slot:icon>
            <VoiceIcon :size="32" class="text-primary"/>
          </template>
          <template v-if="microphone.status" v-slot:input>
            <v-textarea
              :label="$tr('project', 'key_340')"
              variant="outlined"
              density="compact"
              rows="2"
              hide-details
              class="mt-5"
              v-model="microphone.description"
            ></v-textarea>
          </template>
          <template v-slot:action>
            <v-switch
              :true-value="1"
              :false-value="0"
              color="primary"
              hide-details
              v-model="microphone.status"
            ></v-switch>
          </template>
        </FlexAvatarIconBlock>
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updatePermissions">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import projectsService from "@/services/projects/projects.service";
import FlexAvatarIconBlock from "@/components/blocks/FlexAvatarIconBlock.vue";
import LocationIcon from "@/components/icons/LocationIcon.vue";
import CameraIcon from "@/components/icons/CameraIcon.vue";
import VoiceIcon from "@/components/icons/VoiceIcon.vue";
import FixedFooter from "@/components/blocks/FixedFooter.vue";

export default {
  name: 'PermissionsAppSettings',
  components: {FixedFooter, VoiceIcon, CameraIcon, LocationIcon, FlexAvatarIconBlock, ScreenLock, PageBar},
  data: () => ({
    loading: true,
    gps: {
      status: 0,
      description: ""
    },
    camera: {
      status: 0,
      description: ""
    },
    microphone: {
      status: 0,
      description: ""
    },
    isUpdate: false
  }),
  watch: {

  },
  methods: {
    getPermissions() {
      this.loading = true;
      projectsService.getPermissions(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.gps = data.gps;
        this.camera = data.camera;
        this.microphone = data.microphone;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    updatePermissions() {
      this.isUpdate = true;
      projectsService.updatePermissions(this.$route.params.uid, {
        gps: this.gps.status,
        gps_description: this.gps.description,
        camera: this.camera.status,
        camera_description: this.camera.description,
        microphone: this.microphone.status,
        microphone_description: this.microphone.description
      }).then(() => {
        this.isUpdate = false;
        this.$refs.footer.showSuccessAlert();
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    }
  },
  beforeMount() {
    this.getPermissions();
  }
};
</script>
