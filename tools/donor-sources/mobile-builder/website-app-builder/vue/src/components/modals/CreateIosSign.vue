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
      <v-row>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-alert
            type="info"
            variant="tonal"
            class="mb-3 mt-3"
          >
            {{ $tr('project', 'key_347') }}
            <a
              href="https://appstoreconnect.apple.com/access/integrations/api"
              target="_blank"
              style="color: inherit"
            >
              {{ $tr('project', 'key_348') }}
            </a>
          </v-alert>
          <v-text-field
            :label="$tr('project', 'key_166')"
            variant="outlined"
            density="comfortable"
            v-model="name"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('project', 'key_176')"
            variant="outlined"
            density="comfortable"
            v-model="issuerId"
          >
            <template v-slot:append-inner>
              <v-tooltip :text="$tr('project', 'key_179')" max-width="300" content-class="px-4" location="left">
                <template v-slot:activator="{ props }">
                  <v-icon v-bind="props" icon="mdi-help-circle-outline" class="cursor-pointer"/>
                </template>
              </v-tooltip>
            </template>
          </v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('project', 'key_177')"
            variant="outlined"
            density="comfortable"
            v-model="keyId"
          >
            <template v-slot:append-inner>
              <v-tooltip :text="$tr('project', 'key_180')" max-width="300" content-class="px-4" location="left">
                <template v-slot:activator="{ props }">
                  <v-icon v-bind="props" icon="mdi-help-circle-outline" class="cursor-pointer"/>
                </template>
              </v-tooltip>
            </template>
          </v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <Dropzone :label="$tr('project', 'key_178')" @on-upload="setFile"/>
        </v-col>
      </v-row>
    </v-container>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="primary" :loading="loading" @click="uploadSign">
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
  name: 'CreateIosSign',
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
    issuerId: "",
    keyId: "",
    file: null
  }),
  watch: {

  },
  methods: {
    setFile(val) {
      this.file = val;
    },
    uploadSign() {
      this.loading = true;
      if (this.isAdmin) {
        adminProjectsService.uploadIosSign(this.uid, {
          name: this.name,
          issuer_id: this.issuerId,
          key_id: this.keyId
        }, this.file).then((res) => {
          this.loading = false;
          this.$emit("on-create", res.data);
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.uploadIosSign(this.uid, {
          name: this.name,
          issuer_id: this.issuerId,
          key_id: this.keyId
        }, this.file).then((res) => {
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
