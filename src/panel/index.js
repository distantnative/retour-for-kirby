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
  icons: {
    retour:
      '<path d="M13 8V16C13 17.6569 11.6569 19 10 19H7.82929C7.41746 20.1652 6.30622 21 5 21C3.34315 21 2 19.6569 2 18C2 16.3431 3.34315 15 5 15C6.30622 15 7.41746 15.8348 7.82929 17H10C10.5523 17 11 16.5523 11 16V8C11 6.34315 12.3431 5 14 5H17V2L22 6L17 10V7H14C13.4477 7 13 7.44772 13 8ZM5 19C5.55228 19 6 18.5523 6 18C6 17.4477 5.55228 17 5 17C4.44772 17 4 17.4477 4 18C4 18.5523 4.44772 19 5 19Z"></path>',
  },
});
