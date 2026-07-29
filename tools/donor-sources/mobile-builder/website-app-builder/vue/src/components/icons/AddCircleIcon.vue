<template>
  <svg :width="size" :height="size" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g clip-path="url(#clip0_13320_18)">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M17.0711 2.92893C13.1658 -0.97631 6.83418 -0.976311 2.92893 2.92893C-0.976311 6.83418 -0.976311 13.1658 2.92893 17.0711C6.83418 20.9763 13.1658 20.9763 17.0711 17.0711C20.9763 13.1658 20.9763 6.83418 17.0711 2.92893ZM5.75736 9C5.20507 9 4.75736 9.44772 4.75736 10C4.75736 10.5523 5.20507 11 5.75736 11H9V14.2426C9 14.7949 9.44772 15.2426 10 15.2426C10.5523 15.2426 11 14.7949 11 14.2426V11H14.2426C14.7949 11 15.2426 10.5523 15.2426 10C15.2426 9.44771 14.7949 9 14.2426 9H11V5.75736C11 5.20507 10.5523 4.75736 10 4.75736C9.44772 4.75736 9 5.20507 9 5.75736V9H5.75736Z" fill="currentColor"/>
    </g>
    <defs>
      <clipPath id="clip0_13320_18">
        <rect :width="size" :height="size" fill="white"/>
      </clipPath>
    </defs>
  </svg>
</template>

<script>
export default {
  name: 'AddIcon',
  props: {
    size: {
      type: Number,
      default: 28
    }
  }
}
</script>