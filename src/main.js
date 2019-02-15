import "./assets/chart.css";
import "./assets/icons.js";

import View from "./components/View.vue";
import CountField from "./components/Fields/Count.vue";
import StatusField from "./components/Fields/Status.vue";
import CountPreview from "./components/Fields/Previews/Count.vue";
import StatusPreview from "./components/Fields/Previews/Status.vue";

panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: View,
      icon: "retour"
    }
  },
  fields: {
    "rt-count": CountField,
    "rt-status": StatusField
  },
  components: {
    "k-rt-status-input": {
      extends: "k-select-input",
    },
    "k-rt-count-field-preview": CountPreview,
    "k-rt-status-field-preview": StatusPreview
  }
});
