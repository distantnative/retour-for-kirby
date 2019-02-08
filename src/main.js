
import './assets/icons'

import Overview from "./components/Overview.vue";
import StatsField from "./components/Fields/StatsField.vue";
import HitsPreview from "./components/Fields/Previews/HitsPreview.vue";
import StatusPreview from "./components/Fields/Previews/StatusPreview.vue";

panel.plugin("distantnative/retour", {
  views: {
    retour: {
      component: Overview,
      icon: 'undo'
    }
  },
  fields: {
    "retour-stats": StatsField
  },
  components: {
    'k-retour-hits-field-preview': HitsPreview,
    'k-retour-status-field-preview': StatusPreview
  }
});
