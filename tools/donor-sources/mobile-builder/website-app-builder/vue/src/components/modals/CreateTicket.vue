<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr('project', 'key_235') }}
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
            autofocus
            density="comfortable"
            v-model="title"
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
      </v-row>
    </v-container>
    <v-divider/>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="primary" :loading="loading" @click="createTicket">
        {{ $tr('project', 'key_238') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import supportService from "@/services/support/support.service";

export default {
  name: 'CreateTicket',
  data: () => ({
    title: "",
    message: "",
    loading: false
  }),
  watch: {

  },
  methods: {
    createTicket() {
      this.loading = true;
      supportService.createTicket(this.title, this.message).then((res) => {
        const data = res.data;
        this.$router.push({ path: `/private/support/ticket/${data.uid}` });
      }).catch(e => {
        this.loading = false;
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {

  }
};
</script>
