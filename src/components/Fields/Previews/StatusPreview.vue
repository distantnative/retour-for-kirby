<template>
  <p class="k-structure-table-text k-retour-status-field-preview">
    <span>
      <k-icon
        type="circle"
        :data-disabled="value === 'disabled'"
        :data-error="! (value >= 300 && value < 400)"
      />
      <code>{{ value }}</code>
    </span>
  </p>
</template>

<script>
export default {
  props: {
    value: String,
    column: Object,
    field: Object
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

  .k-icon {
    color: #7ea328;

    &[data-error] {
      color: #f0c674;
    }

    &[data-disabled] {
      color: #c82829;
    }
  }
}
</style>
