<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr('project', 'key_255') }}
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
            :label="$tr('project', 'key_236')"
            variant="outlined"
            density="comfortable"
            autofocus
            v-model="name"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-textarea
            :label="$tr('project', 'key_237')"
            variant="outlined"
            density="comfortable"
            v-model="message"
          ></v-textarea>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <Dropzone :label="$tr('project', 'key_265')" @on-upload="setFile"/>
        </v-col>
      </v-row>
    </v-container>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="primary" :loading="loading" @click="sendPush">
        {{ $tr('project', 'key_286') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import Dropzone from "@/components/forms/DropZone.vue";
import projectsService from "@/services/projects/projects.service";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'CreateNewsletter',
  components: {Dropzone},
  props: {
    uid: {
      type: String,
      required: true,
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  data: () => ({
    name: "",
    message: "",
    file: null,
    loading: false
  }),
  watch: {

  },
  methods: {
    setFile(val) {
      this.file = val;
    },
    sendPush() {
      this.loading = true;
      if (this.isAdmin) {
        adminProjectsService.sendPushNotification(this.uid, {
          title: this.name,
          message: this.message
        }, this.file).then(() => {
          this.loading = false;
          this.$emit("on-create");
        }).catch(e => {
          this.loading = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.sendPushNotification(this.uid, {
          title: this.name,
          message: this.message
        }, this.file).then(() => {
          this.loading = false;
          this.$emit("on-create");
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
