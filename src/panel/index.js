// Views
import RedirectsView from "./components/Views/RedirectsView.vue";
import FailuresView from "./components/Views/FailuresView.vue";
import SystemView from "./components/Views/SystemView.vue";

// Components
import Stats from "./components/Stats/Stats.vue";
import Tabs from "./components/Navigation/Tabs.vue";
import Timespan from "./components/Navigation/Timespan.vue";

// Table previews
import CountFieldPreview from "./components/Table/CountFieldPreview.vue";
import PathFieldPreview from "./components/Table/PathFieldPreview.vue";
import PriorityFieldPreview from "./components/Table/PriorityFieldPreview.vue";
import StatusFieldPreview from "./components/Table/StatusFieldPreview.vue";

// Fields
import StatusField from "./components/Fields/StatusField.vue";

panel.plugin("distantnative/retour", {
  components: {
    "k-count-field-preview": CountFieldPreview,
    "k-path-field-preview": PathFieldPreview,
    "k-priority-field-preview": PriorityFieldPreview,
    "k-status-field-preview": StatusFieldPreview,

    "k-retour-stats": Stats,
    "k-retour-tabs": Tabs,
    "k-retour-timespan": Timespan,

    "k-retour-redirects-view": RedirectsView,
    "k-retour-failures-view": FailuresView,
    "k-retour-system-view": SystemView,
  },
  fields: {
    "retour-status": StatusField,
  },
});
