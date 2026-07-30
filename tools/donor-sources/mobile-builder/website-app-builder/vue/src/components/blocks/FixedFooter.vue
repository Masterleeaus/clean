<template>
  <div class="fixed-footer" :style="`right: ${rightPadding}px; left: ${leftPadding}px`">
    <v-divider/>
    <v-toolbar color="transparent">
      <div class="w-100 d-flex justify-space-between px-3 align-center">
        <div v-if="success_alert" class="d-flex justify-space-between align-center fill-height">
          <v-icon color="success" icon="mdi-check-circle"/>
          <div class="text-body-2 ml-3">
            {{ $tr('project', 'key_34') }}
          </div>
        </div>
        <div v-else/>
        <slot></slot>
      </div>
    </v-toolbar>
  </div>
</template>

<script>
export default {
  name: 'FixedFooter',
  props: {
    right: {
      type: Number
    }
  },
  data: () => ({
    success_alert: false
  }),
  computed: {
    leftPadding() {
      return this.$store.state.left_drawer ? 346 : 0
    },
    rightPadding:function () {
      if (this.right === undefined) {
        return 380;
      } else {
        return this.right;
      }
    }
  },
  watch: {

  },
  methods: {
    showSuccessAlert() {
      this.success_alert = true;
      setTimeout(() => this.success_alert = false, 3000);
    }
  },
  beforeMount() {

  }
};
</script>
