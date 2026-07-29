<template>
  <PageBar
    :title="$tr('menu', 'key_22')"
    :tabs="tabs"
    @changeTab="changeTab"
  />
  <MainNavigationItems v-if="tab === $tr('project', 'key_118')" @preview-update="$emit('preview-update')"/>
  <BarNavigationItems v-if="tab === $tr('project', 'key_45')" @preview-update="$emit('preview-update')"/>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import MainNavigationItems from "@/views/private/apps/editor/navigation_app_settings/MainNavigationItems.vue";
import BarNavigationItems from "@/views/private/apps/editor/navigation_app_settings/BarNavigationItems.vue";

export default {
  name: 'NavigationAppSettings',
  components: {BarNavigationItems, MainNavigationItems, PageBar},
  data: () => ({
    tab: 0
  }),
  computed: {
    tabs: function () {
      return [
        this.$tr('project', 'key_118'),
        this.$tr('project', 'key_45')
      ];
    },
    actions: function () {
      return [
        {
          title: this.$tr('project', 'key_57'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_58'),
          value: 1
        },
        {
          title: this.$tr('project', 'key_59'),
          value: 2
        },
        {
          title: this.$tr('project', 'key_60'),
          value: 3
        },
        {
          title: this.$tr('project', 'key_298'),
          value: 4
        },
        {
          title: this.$tr('project', 'key_328'),
          value: 5
        },
      ];
    }
  },
  watch: {

  },
  methods: {
    changeTab(val) {
      this.tab = val;
    }
  },
  beforeMount() {

  }
};
</script>
