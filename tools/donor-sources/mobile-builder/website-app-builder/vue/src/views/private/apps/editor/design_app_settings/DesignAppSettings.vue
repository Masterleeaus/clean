<template>
  <PageBar
    :title="$tr('menu', 'key_12')"
    :tabs="tabs"
    @changeTab="changeTab"
  />
  <TemplateDesign v-if="tab === $tr('project', 'key_35')" @preview-update="$emit('preview-update')"/>
  <DrawerSettings v-if="tab === $tr('project', 'key_110')" @preview-update="$emit('preview-update')"/>
  <StylesSettings v-if="tab === $tr('project', 'key_132')" @preview-update="$emit('preview-update')"/>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import TemplateDesign from "@/views/private/apps/editor/design_app_settings/TemplateDesign.vue";
import DrawerSettings from "@/views/private/apps/editor/design_app_settings/DrawerSettings.vue";
import StylesSettings from "@/views/private/apps/editor/design_app_settings/StylesSettings.vue";
export default {
  name: 'DesignAppSettings',
  components: {StylesSettings, DrawerSettings, TemplateDesign, PageBar},
  data: () => ({
    tab: 0,

  }),
  computed: {
    tabs: function () {
      return [
        this.$tr('project', 'key_35'),
        this.$tr('project', 'key_110'),
        this.$tr('project', 'key_132')
      ];
    },
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
