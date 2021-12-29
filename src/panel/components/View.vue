<template>
  <k-inside class="retour">
    <template v-if="stats">
      <stats :data="stats" :timespan="timespan" />
      <navigation :tab="tab" :tabs="tabs" :timespan="timespan" />
    </template>

    <component :is="'' + tab + '-tab'" :data="data" :has-log="!!stats" />
  </k-inside>
</template>

<script>
import Navigation from "./Interaction/Navigation.vue";
import Stats from "./Interaction/Stats.vue";

import RedirectsTab from "./Tabs/RedirectsTab.vue";
import FailuresTab from "./Tabs/FailuresTab.vue";
import SystemTab from "./Tabs/SystemTab.vue";

export default {
  components: {
    Stats,
    Navigation,
    FailuresTab,
    RedirectsTab,
    SystemTab,
  },
  props: {
    data: [Object, Array],
    stats: [Boolean, Array],
    tab: String,
    tabs: Array,
    timespan: Array,
  },
};
</script>
