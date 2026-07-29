<template>
  <v-toolbar color="transparent">
    <v-btn v-if="isBack" icon="" @click="$router.go(-1)">
      <v-icon>mdi-arrow-left</v-icon>
    </v-btn>
    <v-btn v-if="isMenu" icon="" @click="$store.commit('setLeftDrawer', !$store.state.left_drawer)">
      <MenuIcon/>
    </v-btn>
    <v-toolbar-title>{{ title }}</v-toolbar-title>
    <v-spacer/>
    <slot name="action"></slot>
    <template v-if="tabs" v-slot:extension>
      <v-tabs
        align-tabs="title"
        color="primary"
        @update:model-value="(val) => $emit('changeTab', val)"
      >
        <v-tab
          v-for="item in tabs"
          :key="item"
          :value="item"
        >
          {{ item }}
        </v-tab>
      </v-tabs>
    </template>
  </v-toolbar>
  <v-divider/>
</template>

<script>
import MenuIcon from "@/components/icons/MenuIcon.vue";

export default {
  name: 'PageBar',
  components: {MenuIcon},
  props: {
    title: {
      type: String
    },
    tabs: {
      type: Array
    },
    isBack: {
      type: Boolean,
      default: false
    },
    isMenu: {
      type: Boolean,
      default: true
    }
  },
  data: () => ({

  }),
  watch: {

  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
