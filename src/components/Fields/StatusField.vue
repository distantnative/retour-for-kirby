
<template>
  <k-field :input="_uid" v-bind="$props" class="k-select-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      v-on="$listeners"
    >
      <div slot="before" :data-status="status">
        <k-icon type="circle" />
      </div>
    </k-input>
  </k-field>
</template>


<script>

import status from "../../assets/status.js";

export default {
  extends: "k-select-field",
  computed: {
    status() {
      return status(this.value);
    }
  }
}
</script>

<style>
.k-retour-view [data-status="disabled"] > .k-icon {
  color: #c82829;
}
.k-retour-view [data-status="redirect"] > .k-icon {
  color: #7ea328;
}
.k-retour-view [data-status="notice"] > .k-icon {
  color: #f0c674;
}
</style>

