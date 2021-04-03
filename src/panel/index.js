
import Store from "./store.js";
import View from "./components/View.vue";

import DestinationField from "./components/Fields/DestinationField.vue";
import StatusField from "./components/Fields/StatusField.vue";

panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: View,
      icon: "road-sign"
    }
  },
  fields: {
    "rt-status": StatusField,
    "rt-destination": DestinationField
  },
  created(app) {
    app.$store.registerModule("retour", Store(app));
  }
});