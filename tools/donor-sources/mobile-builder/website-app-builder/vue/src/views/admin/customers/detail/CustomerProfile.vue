<template>
  <PageBar :title="$tr('menu', 'key_24')" :is-menu="false" is-back/>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('auth', 'key_5')"
          variant="outlined"
          density="comfortable"
          v-model="email"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('admin', 'key_73')"
          variant="outlined"
          density="comfortable"
          :items="adminItems"
          v-model="admin"
        ></v-select>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('auth', 'key_19')"
          variant="outlined"
          density="comfortable"
          type="password"
          v-model="new_password"
        ></v-text-field>
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateCustomer">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import adminCustomersService from "@/services/customers/admin.customers.service";
import FixedFooter from "@/components/blocks/FixedFooter.vue";

export default {
  name: 'CustomerProfile',
  components: {FixedFooter, PageLoading, PageBar},
  data: () => ({
    loading: true,
    email: "",
    admin: 0,
    new_password: "",
    isUpdate: false
  }),
  computed: {
    adminItems: function () {
      return [
        {
          title: this.$tr('admin', 'key_74'),
          value: 0
        },
        {
          title: this.$tr('admin', 'key_75'),
          value: 1
        },
      ];
    }
  },
  methods: {
    updateCustomer() {
      this.isUpdate = true;
      adminCustomersService.updateCustomer(this.$route.params.customer_id, {
        email: this.email,
        admin: this.admin,
        new_password: this.new_password
      }).then(() => {
        this.isUpdate = false;
        this.$refs.footer.showSuccessAlert();
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    },
    getCustomer() {
      adminCustomersService.getCustomer(this.$route.params.customer_id).then((res) => {
        const data = res.data;
        this.email = data.email;
        this.admin = data.admin;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getCustomer();
  }
};
</script>
