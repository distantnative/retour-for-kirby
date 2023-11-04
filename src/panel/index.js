// Views
import View from "./components/Views/View.vue";
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

    "k-retour-view": View,
    "k-retour-redirects-view": RedirectsView,
    "k-retour-failures-view": FailuresView,
    "k-retour-system-view": SystemView,
  },
  fields: {
    "retour-status": StatusField,
  },
  icons: {
    "circle-focus":
      '<path d="M12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20ZM12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 14C10.8954 14 10 13.1046 10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12C14 13.1046 13.1046 14 12 14Z"></path>',
    "check-double":
      '<path d="M11.602 13.7599L13.014 15.1719L21.4795 6.7063L22.8938 8.12051L13.014 18.0003L6.65 11.6363L8.06421 10.2221L10.189 12.3469L11.6025 13.7594L11.602 13.7599ZM11.6037 10.9322L16.5563 5.97949L17.9666 7.38977L13.014 12.3424L11.6037 10.9322ZM8.77698 16.5873L7.36396 18.0003L1 11.6363L2.41421 10.2221L3.82723 11.6352L3.82604 11.6363L8.77698 16.5873Z"></path>',
    "cloud-off":
      '<path d="M3.51472 2.10051L22.6066 21.1924L21.1924 22.6066L19.1782 20.5924C18.503 20.8556 17.7684 21 17 21H7C3.68629 21 1 18.3137 1 15C1 12.3846 2.67346 10.16 5.00804 9.33857C5.0027 9.22639 5 9.11351 5 9C5 8.22228 5.12683 7.47418 5.36094 6.77527L2.10051 3.51472L3.51472 2.10051ZM7 9C7 9.08147 7.00193 9.16263 7.00578 9.24344L7.07662 10.7309L5.67183 11.2252C4.0844 11.7837 3 13.2889 3 15C3 17.2091 4.79086 19 7 19H17C17.1858 19 17.3687 18.9873 17.5478 18.9628L7.03043 8.44519C7.01032 8.62736 7 8.81247 7 9ZM12 2C15.866 2 19 5.13401 19 9C19 9.11351 18.9973 9.22639 18.992 9.33857C21.3265 10.16 23 12.3846 23 15C23 16.0883 22.7103 17.1089 22.2037 17.9889L20.7111 16.4955C20.8974 16.0335 21 15.5287 21 15C21 12.79 19.21 11 17 11C16.4711 11 15.9661 11.1027 15.5039 11.2892L14.0111 9.7964C14.8912 9.28978 15.9118 9 17 9C17 6.23858 14.7614 4 12 4C10.9295 4 9.93766 4.33639 9.12428 4.90922L7.69418 3.48056C8.88169 2.55284 10.3763 2 12 2Z"></path>',
  },
});
