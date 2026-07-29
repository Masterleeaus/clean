<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr("admin", "key_171") }}
      </v-toolbar-title>
      <v-spacer />
      <v-btn
        icon="mdi-window-close"
        color="red-accent-3"
        density="default"
        @click="$emit('close')"
      />
    </v-toolbar>
    <v-divider />
    <v-container fluid>
      <v-row class="mt-3">
        <v-col md="12" sm="12" cols="12" class="py-0">
          <div v-if="loading" class="d-flex justify-center">
            <v-progress-circular indeterminate color="primary" />
          </div>
          <template v-else>
            <div class="text-body-1 font-weight-medium mb-3">
              {{ $tr("project", "key_364") }}
            </div>
            <OneTimeListItem
              v-for="plan in plans"
              :key="plan.id"
              :plan="plan"
              :is-selected="plan.id === selectedPlanId"
              @select="selectedPlanId = $event"
            />
          </template>
        </v-col>
      </v-row>
    </v-container>
    <v-divider />
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn
        flat
        color="primary"
        :loading="isSubmitting"
        :disabled="!selectedPlanId || loading"
        @click="makeGift"
      >
        {{ $tr("project", "key_312") }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import subscribeService from "@/services/subscribe/subscribe.service";
import adminSubscribeService from "@/services/subscribe/admin.subscribe.service";
import OneTimeListItem from "@/components/blocks/plans/OneTimeListItem.vue";

export default {
  name: "MakeGift",
  components: {
    OneTimeListItem,
  },
  props: {
    appUid: {
      type: String,
      required: true,
    },
    userId: {
      type: String,
      required: true,
    },
  },
  data: () => ({
    loading: true,
    isSubmitting: false,
    plans: [],
    selectedPlanId: null,
  }),
  methods: {
    getPlans() {
      this.loading = true;
      subscribeService
        .getPlans()
        .then((res) => {
          // Filter only per_build plans
          this.plans = res.data.filter(
            (plan) => plan.type === "per_build",
          );
          this.loading = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
          this.loading = false;
        });
    },
    makeGift() {
      if (!this.selectedPlanId) return;

      this.isSubmitting = true;

      adminSubscribeService
        .makeGift({
          plan_id: this.selectedPlanId,
          user_id: this.userId,
          app_uid: this.appUid,
        })
        .then(() => {
          this.$emit("success");
          this.isSubmitting = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
          this.isSubmitting = false;
        });
    },
  },
  mounted() {
    this.getPlans();
  },
};
</script>
