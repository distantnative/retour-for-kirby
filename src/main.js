
// import './assets/icons'
import "chartist/dist/chartist.min.css";

import View from "./components/View.vue";
import StatsField from "./components/Fields/StatsField.vue";
import StatusField from "./components/Fields/StatusField.vue";
import StatusInput from "./components/Fields/Inputs/StatusInput.vue";
import CountPreview from "./components/Fields/Previews/CountPreview.vue";
import StatusPreview from "./components/Fields/Previews/StatusPreview.vue";

panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: View,
      icon: "undo"
    }
  },
  fields: {
    "retour-stats": StatsField,
    "retour-status": StatusField
  },
  components: {
    "k-retour-status-input": StatusInput,
    "k-retour-count-field-preview": CountPreview,
    "k-retour-status-field-preview": StatusPreview
  }
});
