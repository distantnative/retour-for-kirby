
// import './assets/icons'
import "chartist/dist/chartist.min.css";

import View from "./components/View.vue";
import StatsField from "./components/Fields/StatsField.vue";
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
    "retour-stats": StatsField
  },
  components: {
    "k-retour-count-field-preview": CountPreview,
    "k-retour-status-field-preview": StatusPreview
  }
});
