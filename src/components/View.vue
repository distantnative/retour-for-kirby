<template>
  <div class="k-retour-view">
    <stats v-if="hasLogs" />
    <tables />
    <settings v-if="hasLogs" />
  </div>
</template>

<script>
import {permissions} from "./helpers.js";

import Stats from "./Sections/Stats.vue";
import Tables from "./Sections/Tables.vue";
import Settings from "./Sections/Settings.vue";

export default {
  mixins: [permissions],
  components: {
    Stats,
    Tables,
    Settings,
  },
  created() {
    const locale = this.$store.state.translation.current;
    this.loadScript(
      "https://unpkg.com/dayjs/locale/" + locale,
      "dayjs-" + locale,
      () => {
        window.dayjs = this.$library.dayjs;
      },
      () => {
        this.$library.dayjs.locale(locale);
      }
    );
    this.loadScript(
      "https://unpkg.com/dayjs/plugin/localizedFormat",
      "dayjs-localizedFormat",
      () => {},
      () => {
        this.$library.dayjs.extend(dayjs_plugin_localizedFormat);
      }
    );
  },
  mounted() {
    if (this.canAccess === false) {
      this.$router.push("/");
    }

    this.$store.dispatch("retour/init");
    this.$store.dispatch("retour/load");
  },
  computed: {
    hasLogs() {
      return this.$store.state.retour.options.logs;
    }
  },
  methods: {
    loadScript(url, id, before, after) {
      before();
      var js, fjs = document.getElementsByTagName("script")[0];
      if (document.getElementById(id)){ return; }
      js = document.createElement("script"); js.id = id;
      js.onload = after;
      js.src = url;
      fjs.parentNode.insertBefore(js, fjs);
    }
  }
}
</script>

<style>
.k-retour-view > .k-view {
  padding-top: 1.5rem;
  padding-bottom: 1.5rem;
}
</style>

