<template>
  <p
    :data-status="status(value)"
    class="k-structure-table-text rt-status-preview"
  >
      <k-icon type="circle" />
      <code>{{ value }}</code>
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
  methods: {
    status: (v) => status(v)
  }
}
</script>

<style>
.rt-status-preview {
  display: flex;
}

.rt-status-preview code {
  background: rgba(0,0,0,.1);
  color: #16171a;
  border-radius: 3px;
  box-decoration-break: clone;
  font-family: SFMono-Regular,Consolas,Liberation Mono,Menlo,Courier,monospace;
  font-size: .875em;
  padding: .05em .5em;
  margin-left: .5em;
}
</style>
