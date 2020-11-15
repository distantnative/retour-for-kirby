// Vue components
import View from "./components/View.vue";

import DestinationField from "./components/Fields/DestinationField.vue";
import StatusField from "./components/Fields/StatusField.vue";

import TableCountCell from "./components/Table/Cells/TableCountCell.vue";
import TableLinkCell from "./components/Table/Cells/TableLinkCell.vue";
import TablePriorityCell from "./components/Table/Cells/TablePriorityCell.vue";
import TableStatusCell from "./components/Table/Cells/TableStatusCell.vue";

// Vuex store
import Store from "./store.js";

import { canAccess } from "./mixins/permissions.js";

// Register everything
panel.plugin("distantnative/retour", {
  components: {
    "k-table-count-cell": TableCountCell,
    "k-table-link-cell": TableLinkCell,
    "k-table-priority-cell": TablePriorityCell,
    "k-table-status-cell": TableStatusCell
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
