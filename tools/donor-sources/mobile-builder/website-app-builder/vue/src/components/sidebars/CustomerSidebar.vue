<template>
  <v-navigation-drawer permanent :width="280">
    <v-toolbar color="transparent" :title="`${$tr('admin', 'key_70')}: ${$route.params.customer_id}`"/>
    <v-divider/>
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr('admin', 'key_71') }}
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
import InboxIcon from "@/components/icons/InboxIcon.vue";
import ArchiveIcon from "@/components/icons/ArchiveIcon.vue";

export default {
  name: 'CustomerSidebar',
  data: () => ({
    navigation: [
      {
        name: "key_72",
        to: "profile"
      },
      {
        name: "key_3",
        to: "apps"
      },
      {
        name: "key_117",
        to: "subscribes"
      },
      {
        name: "key_4",
        to: "tickets"
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
