import View from "./components/View.vue";

// table previews
import CountFieldPreview from "./components/List/CountFieldPreview.vue";
import PathFieldPreview from "./components/List/PathFieldPreview.vue";
import PriorityFieldPreview from "./components/List/PriorityFieldPreview.vue";
import StatusFieldPreview from "./components/List/StatusFieldPreview.vue";

// fields
import StatusField from "./components/Fields/StatusField.vue";

panel.plugin("distantnative/retour", {
  components: {
    "k-count-field-preview": CountFieldPreview,
    "k-path-field-preview": PathFieldPreview,
    "k-priority-field-preview": PriorityFieldPreview,
    "k-status-field-preview": StatusFieldPreview,

    "k-retour-view": View,
  },
  fields: {
    "rt-status": StatusField,
  },
});
