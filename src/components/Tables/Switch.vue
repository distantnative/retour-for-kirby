<template>
  <k-headline class="rt-table-switch">
    <button
      v-for="table in ['redirects', 'fails']"
      :key="table"
      :data-current="current === table"
      @click="onSwitch"
    >
      {{ $t('rt.' + table) }}
    </button>
  </k-headline>
</template>

<script>
export default {
  computed: {
    current() {
      return this.$store.state.retour.view.table;
    }
  },
  methods: {
    onSwitch() {
      this.$store.dispatch("retour/table");
    }
  }
}
</script>

<style lang="scss">
.rt-table-switch > button {
  font-weight: 600;
  text-transform: uppercase;
  padding: .15rem .35rem;
  outline: none;

  &:not(:first-child) {
    margin-left: .5rem;
  }

  &:not(:last-child) {
    margin-right: .5rem;
  }

  &[data-current] {
    border-bottom: 3px solid #4271ae;
  }
}
</style>

