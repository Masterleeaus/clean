<template>
  <div
    class="d-flex justify-space-between pa-4 w-100 border"
    style="border-radius: 6px"
  >
    <div class="d-flex justify-start flex-1-1 mr-12">
      <v-avatar
        color="blue-grey-lighten-5"
        :size="60"
        class="mr-4"
      >
        <slot name="icon"></slot>
      </v-avatar>
      <div class="flex-1-1" style="margin-top: 6px">
        <div class="text-body-1 font-weight-medium">
          {{ title }}
        </div>
        <div class="text-body-2 text-blue-grey-darken-1">
          {{ subtitle }}
        </div>
        <div>
          <slot name="input"></slot>
        </div>
      </div>
    </div>
    <div>
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FlexAvatarIconBlock',
  props: {
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      required: true
    },
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
