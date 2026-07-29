<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr('project', 'key_294') }}
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
            :label="$tr('project', 'key_295')"
            variant="outlined"
            density="comfortable"
            prefix="."
            v-model="name"
          ></v-text-field>
        </v-col>
      </v-row>
    </v-container>
    <v-divider/>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="primary" :loading="loading" @click="createDiv">
        {{ $tr('project', 'key_173') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import projectsService from "@/services/projects/projects.service";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'AddStyleClass',
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
    loading: false
  }),
  watch: {

  },
  methods: {
    createDiv() {
      this.loading = true;
      if (this.isAdmin) {
        adminProjectsService.createDivForHide(this.uid, this.name).then((res) => {
          const data = res.data;
          this.$emit('add-style', {
            id: data.id,
            name: this.name
          });
          this.loading = false;
          this.$emit('close');
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.createDivForHide(this.uid, this.name).then((res) => {
          const data = res.data;
          this.$emit('add-style', {
            id: data.id,
            name: this.name
          });
          this.loading = false;
          this.$emit('close');
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
