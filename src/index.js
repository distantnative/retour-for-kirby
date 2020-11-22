// Vue components
import View from "./components/View.vue";

import DestinationField from "./components/Fields/DestinationField.vue";
import StatusField from "./components/Fields/StatusField.vue";

import Table from "./polyfills/Table.vue";

// Vuex store
import Store from "./store.js";

import { canAccess } from "./mixins/permissions.js";

// Register everything
panel.plugin("distantnative/retour", {
  components: {
    "k-table": Table,
  },
  fields: {
    "rt-status": StatusField,
    "rt-destination": DestinationField
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
