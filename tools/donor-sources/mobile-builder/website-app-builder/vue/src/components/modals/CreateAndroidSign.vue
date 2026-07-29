<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr('project', 'key_276') }}
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
      <v-row class="mt-3">
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('project', 'key_166')"
            variant="outlined"
            density="comfortable"
            v-model="name"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <div class="d-flex justify-space-between align-center w-100">
            <div>
              {{ $tr('project', 'key_332') }}
            </div>
            <div>
              <v-switch
                color="primary"
                hide-details
                v-model="is_custom_key"
              ></v-switch>
            </div>
          </div>
        </v-col>
        <template v-if="is_custom_key">
          <v-col md="12" sm="12" cols="12" class="py-0">
            <v-text-field
              :label="$tr('project', 'key_167')"
              variant="outlined"
              density="comfortable"
              v-model="alias"
            ></v-text-field>
          </v-col>
          <v-col md="12" sm="12" cols="12" class="py-0">
            <v-text-field
              :label="$tr('project', 'key_168')"
              variant="outlined"
              density="comfortable"
              type="password"
              v-model="keystorePassword"
            ></v-text-field>
          </v-col>
          <v-col md="12" sm="12" cols="12" class="py-0">
            <v-text-field
              :label="$tr('project', 'key_169')"
              variant="outlined"
              density="comfortable"
              type="password"
              v-model="keyPassword"
            ></v-text-field>
          </v-col>
          <v-col md="12" sm="12" cols="12" class="py-0">
            <Dropzone :label="$tr('project', 'key_170')" @on-upload="setFile"/>
          </v-col>
        </template>
      </v-row>
    </v-container>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="primary" :loading="loading" @click="startAction">
        {{ $tr('project', 'key_277') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import Dropzone from "@/components/forms/DropZone.vue";
import projectsService from "@/services/projects/projects.service";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'CreateAndroidSign',
  components: {Dropzone},
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
    name: "",
    loading: false,
    is_custom_key: false,
    alias: "",
    keystorePassword: "",
    keyPassword: "",
    jks: null
  }),
  watch: {

  },
  methods: {
    setFile(val) {
      this.jks = val;
    },
    startAction() {
      if (this.is_custom_key) {
        this.uploadSign();
      } else {
        this.createSign();
      }
    },
    createSign() {
      this.loading = true;
      if (this.isAdmin) {
        adminProjectsService.createAndroidSign(this.uid, this.name).then((res) => {
          this.loading = false;
          this.$emit("on-create", res.data);
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.createAndroidSign(this.uid, this.name).then((res) => {
          this.loading = false;
          this.$emit("on-create", res.data);
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      }
    },
    uploadSign() {
      this.loading = true;
      if (this.isAdmin) {
        adminProjectsService.uploadAndroidSign(this.uid, {
          name: this.name,
          alias: this.alias,
          keystore_password: this.keystorePassword,
          key_password: this.keyPassword
        }, this.jks).then((res) => {
          this.loading = false;
          this.$emit("on-create", res.data);
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.uploadAndroidSign(this.uid, {
          name: this.name,
          alias: this.alias,
          keystore_password: this.keystorePassword,
          key_password: this.keyPassword
        }, this.jks).then((res) => {
          this.loading = false;
          this.$emit("on-create", res.data);
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      }
    }
  },
  beforeMount() {

  }
};
</script>
