<template>
  <v-row>
    <v-col
      v-for="(item, index) in templates"
      cols="3"
      md="3"
      sm="3"
    >
      <div class="d-flex justify-center align-center flex-column">
        <img
          :src="item.image"
          alt=""
          class="border rounded-lg w-100 mb-3 cursor-pointer"
          :style="index === template && `border-color: rgb(var(--v-theme-primary))!important`"
          @click="this.$emit('change', index)"
        />
        <div class="text-caption font-weight-medium mb-3">
          {{ item.name }}
        </div>
      </div>
    </v-col>
  </v-row>
</template>

<script>
import TabsTemplate from "@/assets/images/wrappers/tabs.svg";
import DrawerTemplate from "@/assets/images/wrappers/drawer.svg";
import BlankTemplate from "@/assets/images/wrappers/blank.svg";
import BarTemplate from "@/assets/images/wrappers/bar.svg";
export default {
  name: 'TemplatePicker',
  components: {
    TabsTemplate,
    DrawerTemplate,
    BlankTemplate,
    BarTemplate
  },
  props: {
    template: {
      type: Number,
      required: true
    }
  },
  data: () => ({
    templates: [
      {
        image: DrawerTemplate,
        name: "Drawer + Bar"
      },
      {
        image: TabsTemplate,
        name: "Tabs + Bar"
      },
      {
        image: BarTemplate,
        name: "Bar"
      },
      {
        image: BlankTemplate,
        name: "Blank"
      }
    ],
  }),
  watch: {

  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
