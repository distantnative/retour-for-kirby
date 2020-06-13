// Vue components
import Calendar from "./components/Calendar.vue";
import StatusField from "./components/StatusField.vue";
import TargetField from "./components/TargetField.vue";
import View from "./components/View.vue";

// Vuex store
import Store from "./store.js";

// Register everything
panel.plugin("distantnative/retour", {
  components: {
    "retour-calendar": Calendar
  },
  fields: {
    "retour-status": StatusField,
    "retour-target": TargetField
  },
  views: {
    retour: {
      component: View,
      icon: "road-sign"
    }
  },
  created(app) {
    app.$store.registerModule("retour", Store(app));
  }
});
