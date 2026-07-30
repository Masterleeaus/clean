<template>
  <svg :width="size" :height="size" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M13 4C8.02944 4 4 8.02944 4 13C4 17.9706 8.02944 22 13 22C15.125 22 17.078 21.2635 18.6177 20.0319L23.2929 24.7071C23.6834 25.0976 24.3166 25.0976 24.7071 24.7071C25.0976 24.3166 25.0976 23.6834 24.7071 23.2929L20.0319 18.6177C21.2635 17.078 22 15.125 22 13C22 8.02944 17.9706 4 13 4ZM13 6C9.13401 6 6 9.13401 6 13C6 16.866 9.13401 20 13 20C16.866 20 20 16.866 20 13C20 9.13401 16.866 6 13 6Z" fill="currentColor"/>
  </svg>

</template>

<script>
export default {
  name: 'SearchIcon',
  props: {
    size: {
      type: Number,
      default: 28
    }
  }
}
</script>