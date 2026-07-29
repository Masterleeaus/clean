<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr("admin", "key_100") }}
      </v-toolbar-title>
      <v-spacer />
      <v-btn
        icon="mdi-window-close"
        color="red-accent-3"
        density="default"
        @click="this.$emit('close')"
      />
    </v-toolbar>
    <v-divider />
    <v-container fluid>
      <v-row class="mt-3">
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('admin', 'key_95')"
            variant="outlined"
            density="comfortable"
            v-model="new_name"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-select
            :label="$tr('admin', 'key_96')"
            variant="outlined"
            density="comfortable"
            :items="statuses"
            v-model="new_status"
          ></v-select>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('admin', 'key_97')"
            variant="outlined"
            density="comfortable"
            v-model="new_api_value_1"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('admin', 'key_98')"
            variant="outlined"
            density="comfortable"
            v-model="new_api_value_2"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('admin', 'key_99')"
            variant="outlined"
            density="comfortable"
            v-model="new_api_value_3"
          ></v-text-field>
        </v-col>
      </v-row>
    </v-container>
    <v-divider />
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="primary" :loading="loading" @click="updateMethod">
        {{ $tr("project", "key_173") }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import providersService from "@/services/providers/providers.service";

export default {
  name: "UpdateProvider",
  props: {
    name: {
      type: String,
      required: true,
    },
    id: {
      type: Number,
      required: true,
    },
    status: {
      type: Number,
      required: true,
    },
    api_value_1: {
      type: String,
      required: true,
    },
    api_value_2: {
      type: String,
      required: true,
    },
    api_value_3: {
      type: String,
      required: true,
    },
  },
  data: () => ({
    new_name: "",
    new_api_value_1: "",
    new_api_value_2: "",
    new_api_value_3: "",
    loading: false,
    new_status: 0,
  }),
  computed: {
    statuses: function () {
      return [
        {
          title: this.$tr("project", "key_44"),
          value: 0,
        },
        {
          title: this.$tr("project", "key_43"),
          value: 1,
        },
      ];
    },
  },
  methods: {
    updateMethod() {
      this.loading = true;
      providersService
        .updatePaymentProvider(this.id, {
          name: this.new_name,
          status: this.new_status,
          api_value_1: this.new_api_value_1,
          api_value_2: this.new_api_value_2,
          api_value_3: this.new_api_value_3,
        })
        .then(() => {
          this.$emit("on-success");
          this.loading = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
          this.loading = false;
        });
    },
  },
  beforeMount() {
    this.new_name = this.name;
    this.new_api_value_1 = this.api_value_1;
    this.new_api_value_2 = this.api_value_2;
    this.new_api_value_3 = this.api_value_3;
    this.new_status = this.status;
  },
};
</script>
