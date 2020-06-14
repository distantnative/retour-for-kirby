// Vue components
import View from "./components/View.vue";

import Calendar from "./components/Calendar.vue";
import StatusField from "./components/StatusField.vue";
import TargetField from "./components/TargetField.vue";

import TableCountCell from "./components/TableCountCell.vue";
import TableLinkCell from "./components/TableLinkCell.vue";
import TablePriorityCell from "./components/TablePriorityCell.vue";
import TableStatusCell from "./components/TableStatusCell.vue";

// Vuex store
import Store from "./store.js";

import { canAccess } from "./mixins/permissions.js";

// Register everything
panel.plugin("distantnative/retour", {
  components: {
    "retour-calendar": Calendar,
    "k-table-count-cell": TableCountCell,
    "k-table-link-cell": TableLinkCell,
    "k-table-priority-cell": TablePriorityCell,
    "k-table-status-cell": TableStatusCell
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
