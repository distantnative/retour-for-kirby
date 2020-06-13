<template>
  <k-icon :type="value ? 'bolt' : 'blank'" size="small" />
</template>

<script>
export default {
  props: {
    value: String
  }
}
</script>
