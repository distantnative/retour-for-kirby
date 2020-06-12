// Vue components
import View from "./components/View.vue";
import StatusField from "./components/StatusField.vue";
import TargetField from "./components/TargetField.vue";

// Vuex store
import Store from "./store.js";

// 3rd party assets
// import "./assets/chart.css";

// Register everything
panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: View,
      icon: "road-sign"
    }
  },
  fields: {
    "retour-status": StatusField,
    "retour-target": TargetField
  },
  created(app) {
    app.$store.registerModule("retour", Store(app));
  }
});
