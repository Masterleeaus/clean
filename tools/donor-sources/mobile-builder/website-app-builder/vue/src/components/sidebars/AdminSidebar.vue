<template>
  <v-navigation-drawer permanent :width="280" v-model="$store.state.left_drawer">
    <v-toolbar color="transparent" :title="$tr('admin', 'key_10')"/>
    <v-divider/>
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr('admin', 'key_11') }}
      </v-list-subheader>
      <v-list-item
        nav
        v-for="(item) in navigation"
        exact
        color="primary"
        density="comfortable"
        :to="item.to"
      >
        <template v-slot:prepend>
          <component :is="item.icon" size="22" class="mr-3 text-primary"/>
        </template>
        <v-list-item-title class="text-body-1 font-weight-regular line" style="line-height: 1.2rem;">
          {{ $tr('admin', item.name) }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import FeedIcon from "@/components/icons/FeedIcon.vue";
import UsersIcon from "@/components/icons/UsersIcon.vue";
import ServicesIcon from "@/components/icons/ServicesIcon.vue";
import MailStackIcon from "@/components/icons/MailStackIcon.vue";
import MoneyTransferIcon from "@/components/icons/MoneyTransferIcon.vue";
import TagIcon from "@/components/icons/TagIcon.vue";
import CreditCardIcon from "@/components/icons/CreditCardIcon.vue";
import GearIcon from "@/components/icons/GearIcon.vue";

export default {
  name: 'AdminSidebar',
  data: () => ({
    navigation: [
      {
        name: "key_1",
        to: "/admin/dashboard",
        icon: FeedIcon
      },
      {
        name: "key_2",
        to: "/admin/customers",
        icon: UsersIcon
      },
      {
        name: "key_3",
        to: "/admin/apps",
        icon: ServicesIcon
      },
      {
        name: "key_4",
        to: "/admin/support",
        icon: MailStackIcon
      },
      {
        name: "key_8",
        to: "/admin/transactions",
        icon: MoneyTransferIcon
      },
      {
        name: "key_9",
        to: "/admin/plans",
        icon: TagIcon
      },
      {
        name: "key_94",
        to: "/admin/providers",
        icon: CreditCardIcon
      },
      {
        name: "key_6",
        to: "/admin/settings",
        icon: GearIcon
      },
    ]
  }),
  methods: {

  },
  beforeMount() {

  }
};
</script>
