<template>
  <PageBar
    :title="$tr('menu', 'key_12')"
    :tabs="tabs"
    @changeTab="changeTab"
  />
  <AdminTemplateDesign v-if="tab === $tr('project', 'key_35')" @preview-update="$emit('preview-update')"/>
  <AdminDrawerSettings v-if="tab === $tr('project', 'key_110')" @preview-update="$emit('preview-update')"/>
  <AdminStylesSettings v-if="tab === $tr('project', 'key_132')" @preview-update="$emit('preview-update')"/>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import AdminTemplateDesign from "@/views/admin/apps/editor/design_app_settings/AdminTemplateDesign.vue";
import AdminStylesSettings from "@/views/admin/apps/editor/design_app_settings/AdminStylesSettings.vue";
import AdminDrawerSettings from "@/views/admin/apps/editor/design_app_settings/AdminDrawerSettings.vue";
export default {
  name: 'AdminDesignAppSettings',
  components: {AdminDrawerSettings, AdminStylesSettings, AdminTemplateDesign, PageBar},
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
