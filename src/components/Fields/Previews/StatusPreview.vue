<template>
  <p class="k-structure-table-text k-retour-status-field-preview">
    <span :data-status="status">
      <k-icon type="circle" />
      <code>{{ value }}</code>
    </span>
  </p>
</template>

<script>

import status from "../../../assets/status.js";

export default {
  props: {
    value: String,
    column: Object,
    field: Object
  },
  computed: {
    status() {
      return status(this.value);
    }
  }
}
</script>

<style lang="scss">
.k-retour-status-field-preview {
  > span {
    display: flex;
  }

  code {
    background: rgba(0,0,0,.1);
    border-radius: 3px;
    -webkit-box-decoration-break: clone;
    box-decoration-break: clone;
    font-family: SFMono-Regular,Consolas,Liberation Mono,Menlo,Courier,monospace;
    font-size: .875em;
    line-height: inherit;
    display: inline-block;
    padding: .05em .5em;
    margin-left: .5em;
  }
}
</style>
