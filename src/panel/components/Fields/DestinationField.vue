<template>
  <k-field
    :input="_uid"
    :counter="counterOptions"
    v-bind="$props"
    class="k-text-field"
  >
    <!-- @todo: bring back once https://github.com/getkirby/kirby/issues/3840 is solved -->
    <!-- <template #options>
      <k-button
        icon="circle-nested"
        class="k-field-options-button"
        @click="open"
      >
        {{ $t('select') }}
      </k-button>
    </template> -->

    <k-input
      :id="_uid"
      ref="input"
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
