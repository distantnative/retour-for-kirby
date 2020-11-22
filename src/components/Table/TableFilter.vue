<template>
  <div
    class="rt-tbl-filter"
    :data-focus="isFocused"
  >
    <k-button
      :icon="isFocused ? 'cancel' : 'search'"
      class="mr-2"
      @click="onToggle"
    >
      {{ isFocused ? null : label }}
    </k-button>
    <input
      ref="input"
      v-show="isFocused"
      :placeholder="$t('retour.table.filter')"
      :value="value"
      @input="onInput($event.target.value)"
      @blur="onBlur"
      @keydown.esc="onToggle"
    />
  </div>
</template>

<script>
export default {
  props: {
    label: String,
    value: String
  },
  data() {
    return {
      isFocused: false
    }
  },
  methods: {
    onBlur() {
      if (!this.value) {
        this.isFocused = false;
      }
    },
    onInput(value) {
      this.$emit("input", value);
    },
    onToggle() {
      if (this.isFocused) {
        this.isFocused = false;
        this.onInput(null);
      } else {
        this.isFocused = true;
        this.$nextTick(() => {
          this.$refs.input.focus();
        })
      }
    }
  }
}
</script>

<style lang="scss">
.rt-tbl-filter {
  height: 1.1rem;
  display: flex;
  align-items: center;
}
.rt-tbl-filter > * {
  border-bottom: 1px solid transparent;
}
.rt-tbl-filter .k-button-text {
  font-size: 1rem;
  font-weight: 600;
}
.rt-tbl-filter input {
  background: transparent;
  font-size: var(--font-size-medium);
  outline: none;
  border: 0;
}
.rt-tbl-filter[data-focus] > input {
  border-bottom-color: #ccc;
  font-size: .875rem;
  padding: .1rem;
}
</style>
