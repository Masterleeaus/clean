<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title class="text-red-accent-3">
        {{ $tr('project', 'key_142') }}
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
        <v-col md="12" sm="12" cols="12">
          {{ $tr('project', 'key_389') }}
        </v-col>
      </v-row>
    </v-container>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="red-accent-3" :loading="loading" @click="removeApp">
        {{ $tr('project', 'key_390') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>

import projectsService from "@/services/projects/projects.service";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'RemoveApp',
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
    loading: false
  }),
  watch: {

  },
  methods: {
    removeApp() {
      this.loading = true;
      if (this.isAdmin) {
        adminProjectsService.removeProject(this.uid).then(() => {
          this.$router.push({ path: `/admin/apps`, replace: true });
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.removeProject(this.uid).then(() => {
          this.$router.push({ path: `/private/apps`, replace: true });
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
