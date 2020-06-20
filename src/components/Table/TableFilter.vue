<template>
  <div
    class="retour-table-filter flex items-center"
    :data-focus="isFocused"
  >
    <k-button
      :icon="{
        type: isFocused ? 'cancel' : 'search',
        size: 'small',
      }"
      :text="isFocused ? null : label"
      class="mr-2"
      @click="onToggle"
    />
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
.retour-table-filter {
  height: 1.1rem;
}
.retour-table-filter > * {
  border-bottom: 1px solid transparent;
}
.retour-table-filter .k-button-text {
  font-size: 1rem;
  font-weight: 600;
}
.retour-table-filter input {
  background: transparent;
  font-size: var(--font-size-medium);
  outline: none;
}
.retour-table-filter[data-focus] > input {
  border-bottom-color: #ccc;
  font-size: .875rem;
  padding: .1rem;
}
</style>
