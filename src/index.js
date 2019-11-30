// Vue components
import View from "./components/View.vue";
import RedirectField from "./components/Fields/Redirect.vue";
import StatusField from "./components/Fields/Status.vue";

const view = {
  component: View,
  icon: "retour"
};

const icon = '<use xlink:href="#icon-undo" transform="translate(7.5,7.5) rotate(170) translate(-7.5,-7.5)"></use>';

// Vuex store
import store from "./store.js";

// 3rd party assets
import "tbl-for-kirby/index.css";
import "./assets/css/chart.css";

// Register everything
panel.plugin("distantnative/retour", {
  views: { retour: view },
  icons: { retour: icon },
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
