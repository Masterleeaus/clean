<template>
  <v-navigation-drawer permanent :width="280">
    <v-toolbar color="transparent" :title="$tr('admin', 'key_6')"/>
    <v-divider/>
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr('admin', 'key_126') }}
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

export default {
  name: 'SettingsSidebar',
  data: () => ({
    navigation: [
      {
        name: "key_122",
        to: "/admin/settings"
      },
      {
        name: "key_123",
        to: "/admin/settings/api"
      },
      {
        name: "key_124",
        to: "/admin/settings/push"
      },
      {
        name: "key_127",
        to: "/admin/settings/email"
      },
      {
        name: "key_125",
        to: "/admin/settings/license"
      },
      {
        name: "key_148",
        to: "/admin/settings/updates"
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
