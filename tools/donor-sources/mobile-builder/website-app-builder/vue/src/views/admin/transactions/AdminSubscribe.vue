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
    :is-admin="true"
    app-route="/admin/apps"
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
      :uid="subscribe.uid"
      @close="isOpenModel = false"
      @success-cancel="successCancel"
    />
  </v-dialog>
</template>

<script>
import PageLoading from "@/components/blocks/PageLoading.vue";
import PageBar from "@/components/blocks/PageBar.vue";
import SquircleImage from "@/components/blocks/SquircleImage.vue";
import CancelSubscribe from "@/components/modals/CancelSubscribe.vue";
import adminSubscribeService from "@/services/subscribe/admin.subscribe.service";

export default {
  name: "Transactions",
  components: { CancelSubscribe, SquircleImage, PageBar, PageLoading },
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
    },
    isOpenModel: false,
  }),
  watch: {},
  methods: {
    getTransactionList() {
      this.loadingMore = true;
      adminSubscribeService
        .getSubscribe(this.$route.params.subscription_uid, this.offset)
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
