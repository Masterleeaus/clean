<template>
  <div class="w-100 d-flex justify-center align-center align-self-center" style="height: 100vh;">
    <v-progress-circular
      indeterminate
      :color="isSecondary ? 'blue-grey-lighten-1' : 'primary'"
      :bg-color="isSecondary ? 'blue-grey-lighten-5' : null"
      size="36"
      width="3"
    ></v-progress-circular>
  </div>
</template>

<script>
export default {
  name: 'ScreenLock',
  props: {
    isSecondary: {
      type: Boolean,
    }
  },
  data: () => ({

  })
};
</script>
