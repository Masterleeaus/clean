<template>
  <div
    class="d-flex justify-space-between align-center pa-4 w-100 border"
    style="border-radius: 6px"
  >
    <div class="d-flex justify-start flex-column">
      <div class="font-weight-medium text-body-1">
        {{ title }}
      </div>
      <div class="text-blue-grey-darken-1 text-caption">
        {{ subtitle }}
      </div>
    </div>
    <div>
      <v-checkbox
        hide-details
        color="primary"
        :model-value="isActive"
        @change="(val) => $emit('change-state', val)"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'BundlePicker',
  props: {
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      required: true
    },
    isActive: {
      type: Boolean,
      required: true
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
