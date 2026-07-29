<template>
  <v-navigation-drawer permanent :width="280" v-model="$store.state.left_drawer">
    <v-toolbar color="transparent" :title="$tr('menu', 'key_32')"/>
    <v-divider/>
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr('menu', 'key_33') }}
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
    <template v-slot:append>
      <div class="pa-4">
        <v-btn block variant="tonal" color="red-accent-3" @click="$store.dispatch('logout')">
          {{ $tr("menu", "key_5") }}
        </v-btn>
      </div>
    </template>
  </v-navigation-drawer>
</template>

<script>
import LockIcon from "@/components/icons/LockIcon.vue";
import MoneyTransferIcon from "@/components/icons/MoneyTransferIcon.vue";
import CrownOutlineIcon from "@/components/icons/CrownOutlineIcon.vue";

export default {
  name: 'ProfileSidebar',
  data: () => ({
    navigation: [
      {
        name: "key_24",
        to: "/private/profile",
        icon: LockIcon
      },
      {
        name: "key_44",
        to: "/private/profile/subscribe",
        icon: CrownOutlineIcon
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
