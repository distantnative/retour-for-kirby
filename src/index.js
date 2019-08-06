

import View from "./components/View.vue";
import RedirectField from "./components/Fields/Redirect.vue";
import StatusField from "./components/Fields/Status.vue";

import store from "./store/retour.js";

import "tbl-for-kirby/index.css";
import "./assets/chart.css";
import "./assets/icons.js";

panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: View,
      icon: "retour"
    }
  },
  fields: {
    "rt-redirect": RedirectField,
    "rt-status": StatusField
  },
  components: {
    "k-rt-redirect-input": { extends: "k-text-input" },
    "k-rt-status-input": { extends: "k-select-input" },
  },
  created(app) {
    app.$store.registerModule("retour", store);
  }
});
