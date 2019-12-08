// Vue components
import View from "./components/View.vue";
import RedirectField from "./components/Fields/Redirect.vue";
import StatusField from "./components/Fields/Status.vue";

// Vuex store
import store from "./store.js";

// 3rd party assets
import "tbl-for-kirby/index.css";
import "./assets/chart.css";

// Register everything
panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: View,
      icon: "retour"
    }
  },
  icons: {
    retour: '<use xlink:href="#icon-undo" transform="translate(7.5,7.5) rotate(170) translate(-7.5,-7.5)"></use>'
  },
  fields: {
    "rt-redirect": RedirectField,
    "rt-status": StatusField
  },
  components: {
    "k-rt-redirect-input": { extends: "k-text-input" },
    "k-rt-status-input":   { extends: "k-select-input" },
  },
  created(app) {
    app.$store.registerModule("retour", store);
  }
});
