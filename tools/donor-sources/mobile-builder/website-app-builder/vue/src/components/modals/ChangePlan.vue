<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ !this.id ? $tr("admin", "key_47") : $tr("admin", "key_48") }}
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
          <v-radio-group
            v-model="new_type"
            :disabled="is_updating"
            :label="$tr('admin', 'key_167')"
          >
            <v-radio :label="$tr('admin', 'key_168')" value="subscription"></v-radio>
            <v-radio :label="$tr('admin', 'key_169')" value="per_build"></v-radio>
          </v-radio-group>
        </v-col>
        <v-col
          v-if="new_type === 'subscription'"
          md="12"
          sm="12"
          cols="12"
          class="py-0"
        >
          <v-text-field
            :label="$tr('admin', 'key_39')"
            variant="outlined"
            density="comfortable"
            v-model="new_count"
          ></v-text-field>
        </v-col>
        <v-col md="6" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="`${$tr('admin', 'key_40')}, ${$store.state.config.currency}`"
            variant="outlined"
            density="comfortable"
            v-model="new_price"
          ></v-text-field>
        </v-col>
        <v-col md="6" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="`${$tr('admin', 'key_41')}, ${$store.state.config.currency}`"
            variant="outlined"
            density="comfortable"
            v-model="new_save"
          ></v-text-field>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('admin', 'key_121')"
            variant="outlined"
            density="comfortable"
            v-model="new_api_id"
          ></v-text-field>
        </v-col>
        <v-col v-if="isSubscription" md="12" sm="12" cols="12" class="py-0">
          <v-checkbox
            v-model="new_unlimited"
            :disabled="is_updating"
            :label="$tr('admin', 'key_166')"
          ></v-checkbox>
        </v-col>
        <v-col
          v-if="isBuildCountRequired"
          md="12"
          sm="12"
          cols="12"
          class="py-0"
        >
          <v-text-field
            :label="$tr('admin', 'key_144')"
            variant="outlined"
            density="comfortable"
            v-model="new_build_count"
            type="number"
          ></v-text-field>
        </v-col>
      </v-row>
    </v-container>
    <v-divider />
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="primary" :loading="loading" @click="saveAction">
        {{ $tr("project", "key_173") }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import plansService from "@/services/plans/plans.service";

export default {
  name: "ChangePlan",
  props: {
    id: {
      type: Number,
    },
    price: {
      type: String,
      default: "",
    },
    save: {
      type: String,
      default: "",
    },
    count: {
      type: Number,
      default: 1,
    },
    api_id: {
      type: String,
      default: "",
    },
    build_count: {
      type: Number,
      default: 0,
    },
    type: {
      type: String,
      default: "subscription",
    },
  },
  computed: {
    isSubscription() {
      return this.new_type === "subscription";
    },
    isBuildCountRequired() {
      return (
        (this.new_type === "subscription" && !this.new_unlimited) ||
        this.new_type !== "subscription"
      );
    },
  },
  data: () => ({
    new_price: "",
    new_save: "",
    new_count: 1,
    loading: false,
    new_api_id: "",
    new_build_count: 0,
    new_type: "subscription",
    is_updating: false,
    new_unlimited: true,
  }),
  methods: {
    saveAction() {
      this.loading = true;

      const data = {
        count: this.new_type === "subscription" ? this.new_count : 0,
        price: this.new_price,
        save: this.new_save,
        api_id: this.new_api_id,
        build_count: this.new_build_count,
        type: this.new_type,
      };

      if (this.id) {
        // update
        plansService
          .updatePlan(this.id, data)
          .then(() => {
            this.$emit("on-success");
            this.loading = false;
          })
          .catch((e) => {
            this.$store.commit("openSnackbar", e);
            this.loading = false;
          });
      } else {
        // create
        plansService
          .createPlan(data)
          .then(() => {
            this.$emit("on-success");
            this.loading = false;
          })
          .catch((e) => {
            this.$store.commit("openSnackbar", e);
            this.loading = false;
          });
      }
    },
  },
  beforeMount() {
    if (this.id) {
      this.new_price = this.price;
      this.new_save = this.save;
      this.new_count = this.count;
      this.new_api_id = this.api_id;
      this.new_build_count = this.build_count;
      this.new_type = this.type;
      this.is_updating = true;
      this.new_unlimited = this.build_count === 0;
    }

    if (!this.id) {
      this.is_updating = false;
      this.new_unlimited = true;
    }
  },
};
</script>
