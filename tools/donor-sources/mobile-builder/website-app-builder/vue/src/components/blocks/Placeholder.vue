<template>
  <div
    class="d-flex justify-center align-center text-center flex-column"
    style="height: calc(100vh - 65px)"
  >
    <div class="mb-6 text-blue-grey-lighten-1">
      <slot name="icon"></slot>
    </div>
    <div class="text-h5 mb-3">
      <slot name="title"></slot>
    </div>
    <div class="text-body-1 text-blue-grey-lighten-1 mb-10" style="max-width: 400px">
      <slot name="subtitle"></slot>
    </div>
    <slot name="action"></slot>
  </div>
</template>

<script>
export default {
  name: 'Placeholder',
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
