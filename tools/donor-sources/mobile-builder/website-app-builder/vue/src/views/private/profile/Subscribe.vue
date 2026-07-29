<template>
  <PageBar :title="$tr('menu', 'key_44')" />
  <PageLoading v-if="loading" />
  <template v-else>
    <Placeholder v-if="!list.length">
      <template v-slot:icon>
        <CrownIcon :size="56" />
      </template>
      <template v-slot:title>
        {{ $tr("project", "key_375") }}
      </template>
      <template v-slot:subtitle>
        {{ $tr("project", "key_376") }}
      </template>
    </Placeholder>
    <SubscriptionsList
      v-else
      :list="list"
      :total="total"
      :loading-more="loadingMore"
      @load-more="getSubscribesList"
      detail-route="/private/profile/transactions"
    />
  </template>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import profileService from "@/services/profile/profile.service";
import Placeholder from "@/components/blocks/Placeholder.vue";
import BillIcon from "@/components/icons/BillIcon.vue";
import CrownIcon from "@/components/icons/CrownIcon.vue";
import SubscriptionsList from "@/components/blocks/subscriptions/SubscriptionsList.vue";

export default {
  name: "Subscribe",
  components: {
    SubscriptionsList,
    CrownIcon,
    BillIcon,
    Placeholder,
    PageLoading,
    PageBar,
  },
  data: () => ({
    loading: true,
    offset: 0,
    total: 0,
    list: [],
    loadingMore: false,
  }),
  watch: {},
  methods: {
    getSubscribesList() {
      this.loadingMore = true;
      profileService
        .getSubscribes(this.offset)
        .then((res) => {
          const data = res.data;
          this.list = this.list.concat(data.list);
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
  },
  beforeMount() {
    this.getSubscribesList();
  },
};
</script>
