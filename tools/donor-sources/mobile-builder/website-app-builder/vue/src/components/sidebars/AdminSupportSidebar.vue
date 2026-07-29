<template>
  <v-navigation-drawer permanent :width="280">
    <v-toolbar color="transparent" :title="$tr('admin', 'key_62')"/>
    <v-divider/>
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr('admin', 'key_63') }}
      </v-list-subheader>
      <v-list-item
        nav
        v-for="(item) in navigation"
        exact
        color="primary"
        density="comfortable"
        :to="item.to"
      >
        <v-list-item-title class="text-body-1 font-weight-regular line" style="line-height: 1.2rem;">
          {{ $tr('admin', item.name) }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import LockIcon from "@/components/icons/LockIcon.vue";
import MoneyTransferIcon from "@/components/icons/MoneyTransferIcon.vue";
import CrownOutlineIcon from "@/components/icons/CrownOutlineIcon.vue";

export default {
  name: 'AdminSupportSidebar',
  data: () => ({
    navigation: [
      {
        name: "key_57",
        to: "/admin/support",
      },
      {
        name: "key_58",
        to: "/admin/support/pending",
      },
      {
        name: "key_59",
        to: "/admin/support/archive",
      },
    ]
  }),
  watch: {

  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
