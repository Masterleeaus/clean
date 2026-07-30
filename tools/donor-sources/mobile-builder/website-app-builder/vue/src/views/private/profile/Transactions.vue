<template>
  <PageBar :title="$tr('project', 'key_358')" is-back />
  <PageLoading v-if="loading" />
  <SubscriptionDetails
    v-else
    :subscribe="subscribe"
    :list="list"
    :total="total"
    :loading-more="loadingMore"
    :show-load-more="true"
    app-route="/private/apps"
    @load-more="getTransactionList"
    @cancel-subscription="isOpenModel = true"
  />
  <v-dialog
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="isOpenModel"
  >
    <CancelSubscribe
      :uid="$route.params.subscribe_uid"
      @close="isOpenModel = false"
      @success-cancel="successCancel"
    />
  </v-dialog>
</template>

<script>
import PageLoading from "@/components/blocks/PageLoading.vue";
import PageBar from "@/components/blocks/PageBar.vue";
import profileService from "@/services/profile/profile.service";
import CancelSubscribe from "@/components/modals/CancelSubscribe.vue";
import SubscriptionDetails from "@/components/blocks/subscriptions/SubscriptionDetails.vue";

export default {
  name: "Transactions",
  components: { CancelSubscribe, SubscriptionDetails, PageBar, PageLoading },
  data: () => ({
    loading: true,
    offset: 0,
    total: 0,
    list: [],
    loadingMore: false,
    subscribe: {
      uid: "",
      created_at: "",
      expires_at: "",
      price: "",
      app: {
        name: "",
        uid: "",
        icon: "",
        link: "",
      },
      is_active: false,
      remaining_count: 0,
      type: "subscription",
    },
    isOpenModel: false,
  }),
  watch: {},
  methods: {
    getTransactionList() {
      this.loadingMore = true;
      profileService
        .getTransactions(this.offset, this.$route.params.subscribe_uid)
        .then((res) => {
          const data = res.data;
          this.list = this.list.concat(data.list);
          this.subscribe = data.subscribe;
          this.total = data.total;
          this.offset = this.offset + this.$store.state.offset;
          this.loading = false;
          this.loadingMore = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
          this.loadingMore = false;
        });
    },
    successCancel() {
      this.subscribe.is_active = false;
      this.isOpenModel = false;
    },
  },
  beforeMount() {
    this.getTransactionList();
  },
};
</script>
