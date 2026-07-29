<template>
  <v-card>
    <v-toolbar color="transparent">
      <div
        class="d-flex justify-center align-center align-self-center"
        style="width: 100vw"
      >
        <v-img
          :src="$store.state.config.logo"
          width="34"
          height="34"
          :aspect-ratio="1"
        />
        <v-btn
          icon="mdi-window-close"
          color="red-accent-3"
          style="position: absolute; right: 18px"
          @click="$store.commit('switchOpenPaymentModal')"
        />
      </div>
    </v-toolbar>
    <v-divider />
    <ScreenLock v-if="loading" is-secondary />
    <div v-else class="d-flex justify-center align-center">
      <div class="wizard_container flex-column">
        <div class="text-h5 font-weight-medium mt-8 mb-2">
          {{ $tr("project", "key_364") }}
        </div>
        <div class="text-body-2 mb-12 text-center">
          {{ $tr("project", "key_365") }}
        </div>
        <div class="w-100">
          <div v-if="subscriptionList.length > 0" class="mb-8">
            <div class="text-h6 font-weight-medium mb-2">
              {{ $tr("project", "key_396") }}
            </div>
            <SubscriptionListItem
              v-for="plan in subscriptionList"
              :key="plan.id"
              :plan="plan"
              :is-selected="plan.id === selected"
              @select="selected = $event"
            />
          </div>
          <div v-if="oneTimeList.length > 0" class="mb-8">
            <div class="text-h6 font-weight-medium mb-2">
              {{ $tr("project", "key_397") }}
            </div>
            <OneTimeListItem
              v-for="plan in oneTimeList"
              :key="plan.id"
              :plan="plan"
              :is-selected="plan.id === selected"
              @select="selected = $event"
            />
          </div>
          <v-btn
            variant="flat"
            block
            color="primary"
            size="large"
            :loading="actionLoading"
            @click="getPayment"
          >
            {{ $tr("project", "key_312") }}
          </v-btn>
        </div>
      </div>
    </div>
  </v-card>
</template>

<script>
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import subscribeService from "@/services/subscribe/subscribe.service";
import SubscriptionListItem from "@/components/blocks/plans/SubscriptionListItem.vue";
import OneTimeListItem from "@/components/blocks/plans/OneTimeListItem.vue";

export default {
  name: "SelectPlane",
  components: { ScreenLock, SubscriptionListItem, OneTimeListItem },
  data: () => ({
    loading: true,
    list: [],
    selected: 0,
    actionLoading: false,
  }),
  computed: {
    subscriptionList() {
      return this.list.filter((plan) => plan.type === "subscription");
    },
    oneTimeList() {
      return this.list.filter((plan) => plan.type !== "subscription");
    },
  },
  watch: {},
  methods: {
    getAllPlans() {
      subscribeService
        .getPlans()
        .then((res) => {
          this.list = res.data;
          this.loading = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
        });
    },
    getPayment() {
      this.actionLoading = true;
      subscribeService
        .getPaymentRoute()
        .then((res) => {
          const data = res.data;
          this.startPayment(data.url);
        })
        .catch((e) => {
          this.actionLoading = false;
          this.$store.commit("openSnackbar", e);
        });
    },
    startPayment(url) {
      const plan = this.list.find((plan) => plan.id === this.selected);
      const link = `${url}?uid=${this.$route.params.uid}`;
      subscribeService
        .createPayment(link, plan.id)
        .then((res) => {
          const data = res.data;
          window.location.replace(data.url);
        })
        .catch((e) => {
          this.actionLoading = false;
          this.$store.commit("openSnackbar", e);
        });
    },
    countText(count) {
      return count === 0 ? this.$tr("project", "key_398") : count;
    },
  },
  beforeMount() {
    this.getAllPlans();
  },
};
</script>
