<template>
  <PageBar :title="$tr('admin', 'key_2')" :is-menu="false"/>
  <v-container fluid>
    <v-text-field
      :label="$tr('admin', 'key_68')"
      variant="outlined"
      density="comfortable"
      v-model="search"
    ></v-text-field>
    <PageLoading v-if="loading"/>
    <v-list
      v-else
      item-props
      density="comfortable"
    >
      <div v-for="(customer) in list">
        <v-list-item
          lines="two"
          density="default"
          :to="`/admin/customers/${customer.id}/profile`"
        >
          <template v-slot:prepend>
            <v-avatar>
              <UserIcon :size="24"/>
            </v-avatar>
          </template>
          <v-list-item-title>{{ customer.email }}</v-list-item-title>
          <v-list-item-subtitle>{{ customer.created }}</v-list-item-subtitle>
          <template v-slot:append>
            <v-list-item-action>
              <v-icon icon="mdi-chevron-right"/>
            </v-list-item-action>
          </template>
        </v-list-item>
        <v-divider/>
      </div>
    </v-list>
    <div class="text-center mt-5" v-if="list.length !== total">
      <v-btn variant="text" color="primary" :loading="loadingMore" @click="getCustomers">
        {{ $tr('project', 'key_26') }}
      </v-btn>
    </div>
  </v-container>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import adminCustomersService from "@/services/customers/admin.customers.service";
import UserIcon from "@/components/icons/UserIcon.vue";

export default {
  name: 'Customers',
  components: {UserIcon, PageLoading, PageBar},
  data: () => ({
    loading: true,
    list: [],
    offset: 0,
    total: 0,
    search: "",
    loadingMore: false
  }),
  watch: {
    search: function () {
      this.loading = true;
      this.offset = 0;
      this.list = [];
      this.loadingMore = false;
      this.total = 0;
      this.getCustomers();
    }
  },
  methods:{
    getCustomers() {
      this.loadingMore = true;
      adminCustomersService.getCustomers(this.offset, this.search).then((res) => {
        const data = res.data;
        this.offset = this.offset + this.$store.state.offset;
        this.total = data.total;
        this.list = this.list.concat(data.list)
        this.loading = false;
        this.loadingMore = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.loading = false;
        this.loadingMore = false;
      });
    }
  },
  beforeMount() {
    this.getCustomers();
  }
};
</script>
