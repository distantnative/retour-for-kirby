<template>
  <k-field :input="_uid" v-bind="$props" class="k-select-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      type="select"
      theme="field"
      v-on="$listeners"
      @input="onInput"
    >
      <div slot="before" :data-status="status">
        <k-icon type="circle" />
      </div>
    </k-input>
  </k-field>
</template>

<script>
import status from "../../helpers/status.js";

export default {
  extends: "k-select-field",
  computed: {
    status() {
      return status(this.value);
    }
  },
  methods: {
    onInput(value) {
      this.value = value;
    }
  }
}
</script>

<style lang="scss">
.k-retour-view [data-status="no"] {
  color: #c82829;
}
.k-retour-view [data-status="yes"] {
  color: #7ea328;
}
.k-retour-view [data-status="mmm"] {
  color: #f0c674;
}
</style>
