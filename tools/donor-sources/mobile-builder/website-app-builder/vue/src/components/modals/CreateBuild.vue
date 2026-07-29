<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr("project", "key_160") }}
      </v-toolbar-title>
      <v-spacer/>
      <v-btn
        icon="mdi-window-close"
        color="red-accent-3"
        density="default"
        @click="this.$emit('close')"
      />
    </v-toolbar>
    <v-divider/>
    <v-container fluid>
      <v-row>
        <PlatformPicker
          :platform="platform"
          @change_platform="changePlatform"
        />
        <v-col md="12" sm="12" cols="12" class="py-0 mt-2">
          <v-text-field
            :label="$tr('project', 'key_183')"
            variant="outlined"
            density="comfortable"
            v-model="version"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-select
            v-if="platform === 'android'"
            :label="$tr('project', 'key_186')"
            variant="outlined"
            density="comfortable"
            :disabled="signing.android.loading"
            :loading="signing.android.loading"
            :items="signing.android.list"
            :no-data-text="$tr('project', 'key_189')"
            v-model="android_sign_id"
          >
            <template v-slot:append>
              <v-btn variant="text" icon="mdi-plus" density="comfortable" to="signing"/>
            </template>
          </v-select>
          <v-select
            v-else
            :label="$tr('project', 'key_186')"
            variant="outlined"
            density="comfortable"
            :disabled="signing.ios.loading"
            :loading="signing.ios.loading"
            :items="signing.ios.list"
            :no-data-text="$tr('project', 'key_189')"
            v-model="ios_sign_id"
          ></v-select>
        </v-col>
        <v-col v-if="platform === 'android'" md="12" sm="12" cols="12" class="py-0">
          <div class="font-weight-medium text-subtitle-2 mb-5">
            {{ $tr('project', 'key_190') }}
          </div>
          <BundlePicker
            title="AAB"
            :subtitle="$tr('project', 'key_192')"
            :is-active="file === 'aab'"
            class="mb-3"
            @change-state="changeFile"
          />
          <BundlePicker
            title="APK"
            :subtitle="$tr('project', 'key_191')"
            :is-active="file === 'apk'"
            class="mb-3"
            @change-state="changeFile"
          />
        </v-col>
        <v-col v-else md="12" sm="12" cols="12" class="py-0">
          <div class="d-flex justify-space-between align-center w-100">
            <div>
              {{ $tr('project', 'key_188') }}
            </div>
            <div>
              <v-switch
                color="primary"
                hide-details
                v-model="publish_in_test_flight"
              ></v-switch>
            </div>
          </div>
          <v-alert
            v-if="publish_in_test_flight"
            type="info"
            variant="tonal"
            class="mb-3 mt-3"
          >
            {{ $tr('project', 'key_349') }}
          </v-alert>
        </v-col>
      </v-row>
    </v-container>
    <v-divider/>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn  color="primary" variant="flat" :loading="isUpdate" @click="createNewBuild">
        {{ $tr('project', 'key_187') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import PlatformPicker from "@/components/forms/PlatformPicker.vue";
import projectsService from "@/services/projects/projects.service";
import BundlePicker from "@/components/forms/BundlePicker.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'CreateBuild',
  components: {BundlePicker, PlatformPicker},
  props: {
    uid: {
      type: String,
      required: true
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  data: () => ({
    platform: "android",
    version: "",
    android_sign_id: null,
    ios_sign_id: null,
    publish_in_test_flight: false,
    signing: {
      android: {
        loading: true,
        list: [],
      },
      ios: {
        loading: true,
        list: [],
      }
    },
    file: "aab",
    isUpdate: false
  }),
  watch: {
    platform: function (val) {
      if (val === "android") {
        this.signing.android = {
          loading: true,
          list: [],
        }
        this.getAndroidSigning();
      } else {
        this.signing.ios = {
          loading: true,
          list: [],
        }
        this.getAppleSigning();
      }
    }
  },
  methods: {
    changePlatform(val) {
      this.platform = val;
    },
    changeFile() {
      this.file = this.file === "aab" ? "apk" : "aab";
    },
    getAndroidSigning() {
      if (this.isAdmin) {
        adminProjectsService.getSigningShort(this.uid, "android").then((res) => {
          const data = res.data;
          this.signing.android = {
            list: data.list,
            loading: false
          }
        }).catch(e => {
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.getSigningShort(this.uid, "android").then((res) => {
          const data = res.data;
          this.signing.android = {
            list: data.list,
            loading: false
          }
        }).catch(e => {
          this.$store.commit('openSnackbar', e);
        });
      }
    },
    getAppleSigning() {
      if (this.isAdmin) {
        adminProjectsService.getSigningShort(this.uid, "ios").then((res) => {
          const data = res.data;
          this.signing.ios = {
            list: data.list,
            loading: false
          }
        }).catch(e => {
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.getSigningShort(this.uid, "ios").then((res) => {
          const data = res.data;
          this.signing.ios = {
            list: data.list,
            loading: false
          }
        }).catch(e => {
          this.$store.commit('openSnackbar', e);
        });
      }
    },
    createNewBuild() {
      this.isUpdate = true;
      if (this.isAdmin) {
        adminProjectsService.createBuild(this.uid, {
          version: this.version,
          platform: this.platform,
          format: this.file,
          android_key_id: this.platform === 'android' ? this.android_sign_id : "",
          ios_key_id: this.platform === 'ios' ? this.ios_sign_id : "",
          publish: !this.publish_in_test_flight ? 0 : 1
        }).then((res) => {
          const data = res.data;
          this.isUpdate = false;
          this.$emit("on-create", data);
        }).catch(e => {
          this.isUpdate = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.createBuild(this.uid, {
          version: this.version,
          platform: this.platform,
          format: this.file,
          android_key_id: this.platform === 'android' ? this.android_sign_id : "",
          ios_key_id: this.platform === 'ios' ? this.ios_sign_id : "",
          publish: !this.publish_in_test_flight ? 0 : 1
        }).then((res) => {
          const data = res.data;
          this.isUpdate = false;
          this.$emit("on-create", data);
        }).catch(e => {
          this.isUpdate = false;
          this.$store.commit('openSnackbar', e);
        });
      }
    }
  },
  beforeMount() {
    this.getAndroidSigning();
  }
};
</script>
