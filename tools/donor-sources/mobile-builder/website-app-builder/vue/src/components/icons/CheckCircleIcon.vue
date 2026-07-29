<template>
  <svg :width="size" :height="size" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 6C12 9.31371 9.31371 12 6 12C2.68629 12 0 9.31371 0 6C0 2.68629 2.68629 0 6 0C9.31371 0 12 2.68629 12 6ZM8.78033 5.03033C9.07322 4.73744 9.07322 4.26256 8.78033 3.96967C8.48744 3.67678 8.01256 3.67678 7.71967 3.96967L5 6.68934L4.28033 5.96967C3.98744 5.67678 3.51256 5.67678 3.21967 5.96967C2.92678 6.26256 2.92678 6.73744 3.21967 7.03033L4.46967 8.28033C4.76256 8.57322 5.23744 8.57322 5.53033 8.28033L8.78033 5.03033Z" fill="currentColor"/>
  </svg>
</template>

<script>
export default {
  name: 'CheckCircleIcon',
  props: {
    size: {
      type: Number,
      default: 28
    }
  }
}
</script>