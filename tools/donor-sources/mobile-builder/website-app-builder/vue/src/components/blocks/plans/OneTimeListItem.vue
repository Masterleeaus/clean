<template>
  <div
    class="border d-flex pa-4 rounded justify-space-between align-center mb-3 cursor-pointer"
    :style="
      isSelected &&
      `border-color: rgb(var(--v-theme-primary))!important`
    "
    @click="$emit('select', plan.id)"
  >
    <div class="d-flex flex-column">
      <div class="text-body-1">
        {{ $tr("project", "key_392") }} -
        {{ countText(plan.build_count) }}
      </div>
      <div
        v-if="!plan.save"
        class="text-caption text-blue-grey-darken-1"
      >
        {{ $tr("project", "key_309") }}
      </div>
      <div v-else class="text-caption text-success">
        {{ $tr("project", "key_310") }} {{ plan.save }}
        {{ $store.state.config.currency }}
      </div>
    </div>
    <div class="text-body-1 font-weight-bold">
      {{ plan.price }} {{ $store.state.config.currency }}
    </div>
  </div>
</template>

<script>
export default {
  name: "OneTimeListItem",
  props: {
    plan: {
      type: Object,
      required: true
    },
    isSelected: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select'],
  methods: {
    countText(count) {
      return count === 0 ? this.$tr("project", "key_398") : count;
    }
  }
};
</script>