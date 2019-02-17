import "./assets/chart.css";
import "./assets/icons.js";


import View from "./components/View.vue";
import StatusField from "./components/Fields/Status.vue";
import CountPreview from "./components/Fields/Previews/Count.vue";

import "tbl-for-kirby/index.css";

panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: View,
      icon: "retour"
    }
  },
  fields: {
    "rt-status": StatusField
  },
  components: {
    "k-rt-status-input": {
      extends: "k-select-input",
    },
    "k-rt-count-field-preview": CountPreview
  }
});
