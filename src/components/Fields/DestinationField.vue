<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-text-field"
  >
    <template #options>
      <k-button
        icon="circle-nested"
        class="k-field-options-button"
        @click="open"
      >
        {{ $t('select') }}
      </k-button>
    </template>

    <k-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      type="text"
      theme="field"
      v-on="$listeners"
    />

    <k-pages-dialog ref="selector" @submit="onSelect" />
  </k-field>
</template>

<script>
export default {
  extends: "k-text-field",
  methods: {
    open() {
      this.$refs.selector.open({
        endpoint: "retour/pagepicker",
        max: 1,
        multiple: false,
        selected: [],
        search: true
      });
    },
    onSelect(items) {
      this.$emit("input", items[0].id);
    }
  }
}
</script>
