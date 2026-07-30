<template>
  <PageBar :title="$tr('admin', 'key_8')" :is-menu="false" />
  <PageLoading v-if="loading" />
  <template v-else>
    <Placeholder v-if="!list.length">
      <template v-slot:icon>
        <MoneyTransferIcon :size="56" />
      </template>
      <template v-slot:title>
        {{ $tr("admin", "key_53") }}
      </template>
      <template v-slot:subtitle>
        {{ $tr("admin", "key_53") }}
      </template>
    </Placeholder>
    <v-container fluid v-else>
      <div class="text-body-1 font-weight-medium mb-3">
        {{ `${$tr("admin", "key_52")}: ${total}` }}
      </div>
      <TransactionsList
        :list="list"
        :total="total"
        :loading-more="loadingMore"
        :show-load-more="true"
        :is-admin="true"
        detail-route="/admin/transactions/subscribe"
        @load-more="getTransactions"
      />
    </v-container>
  </template>
</template>

<script>
import PageLoading from "@/components/blocks/PageLoading.vue";
import PageBar from "@/components/blocks/PageBar.vue";
import adminSubscribeService from "@/services/subscribe/admin.subscribe.service";
import TransactionsList from "@/components/blocks/subscriptions/TransactionsList.vue";
import Placeholder from "@/components/blocks/Placeholder.vue";
import MoneyTransferIcon from "@/components/icons/MoneyTransferIcon.vue";

export default {
  name: "AdminTransactions",
  components: {
    PageBar,
    PageLoading,
    TransactionsList,
    Placeholder,
    MoneyTransferIcon,
  },
  data: () => ({
    loading: true,
    list: [],
    offset: 0,
    loadingMore: false,
    total: 0,
  }),
  methods: {
    getTransactions() {
      this.loadingMore = true;
      adminSubscribeService
        .getTransactions(this.offset)
        .then((res) => {
          const data = res.data;
          this.offset = this.offset + this.$store.state.offset;
          this.total = data.total;
          this.list = this.list.concat(data.list);
          this.loading = false;
          this.loadingMore = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
          this.loading = false;
          this.loadingMore = false;
        });
    },
  },
  beforeMount() {
    this.getTransactions();
  },
};
</script>
