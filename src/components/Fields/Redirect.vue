<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-text-field"
  >
    <template slot="options">
      <k-button
        icon="page"
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
      theme="field"
      v-on="$listeners"
    />

    <k-pages-dialog ref="selector" @submit="select" />
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
    select(items) {
      if (items.length > 0) {
        this.$emit("input", items[0].id);
      }
    }
  }
}
</script>
