<template>
  <v-navigation-drawer permanent :width="280">
    <v-toolbar color="transparent" :title="$tr('menu', 'key_4')"/>
    <v-divider/>
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr('menu', 'key_29') }}
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
          {{ $tr('menu', item.name) }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import InboxIcon from "@/components/icons/InboxIcon.vue";
import ArchiveIcon from "@/components/icons/ArchiveIcon.vue";

export default {
  name: 'SupportSidebar',
  data: () => ({
    navigation: [
      {
        name: "key_27",
        to: "/private/support",
        icon: InboxIcon
      },
      {
        name: "key_28",
        to: "/private/support/archive",
        icon: ArchiveIcon
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
