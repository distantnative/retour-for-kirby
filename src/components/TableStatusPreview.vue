<template>
  <span class="retour-table-status-preview">
    <k-icon type="circle" :color="color" />
    <code>{{ value }}</code>
  </span>
</template>

<script>
export default {
  props: {
    value: String
  },
  computed: {
    color() {
      if (this.value === "–") {
        return "gray-light"
      }

      if (parseInt(this.value) >= 300 && parseInt(this.value) < 400) {
        return "green-light";
      }

      return "focus-light";
    }
  }
}
</script>

<style lang="scss">
.retour-table-status-preview {
  display: flex;

  code {
    background: rgba(0, 0, 0, 0.1);
    color: #16171a;
    border-radius: 3px;
    box-decoration-break: clone;
    font-family: var(--font-family-mono);
    font-size: var(--font-size-tiny);
    padding: 0.05em 0.5em;
    margin-left: 0.5em;
  }
}
</style>
