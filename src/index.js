// Vue components
import View from "./components/View.vue";

import Calendar from "./components/Calendar.vue";
import StatusField from "./components/StatusField.vue";
import TargetField from "./components/TargetField.vue";

// Vuex store
import Store from "./store.js";

import { canAccess } from "./mixins/permissions.js";

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
      icon: "road-sign",
      menu(app) {
        return canAccess(app);
      }
    }
  },
  created(app) {
    app.$store.registerModule("retour", Store(app));
  }
});
