import View from "./components/View.vue";

// polyfills
import Table from "./ployfills/Table.vue";

// table cells
import TableCountCell from "./components/List/Cells/TableCountCell.vue";
import TableLinkCell from "./components/List/Cells/TableLinkCell.vue";
import TablePriorityCell from "./components/List/Cells/TablePriorityCell.vue";
import TableStatusCell from "./components/List/Cells/TableStatusCell.vue";

// fields
import StatusField from "./components/Fields/StatusField.vue";

panel.plugin("distantnative/retour", {
  components: {
    "k-table": Table,

    "k-table-count-cell": TableCountCell,
    "k-table-link-cell": TableLinkCell,
    "k-table-priority-cell": TablePriorityCell,
    "k-table-status-cell": TableStatusCell,

    "k-retour-view": View,
  },
  fields: {
    "rt-status": StatusField,
  },
});
