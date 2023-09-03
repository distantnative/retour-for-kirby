(function() {
  "use strict";
  const CollectionView_vue_vue_type_style_index_0_lang = "";
  function normalizeComponent(scriptExports, render, staticRenderFns, functionalTemplate, injectStyles, scopeId, moduleIdentifier, shadowMode) {
    var options = typeof scriptExports === "function" ? scriptExports.options : scriptExports;
    if (render) {
      options.render = render;
      options.staticRenderFns = staticRenderFns;
      options._compiled = true;
    }
    if (functionalTemplate) {
      options.functional = true;
    }
    if (scopeId) {
      options._scopeId = "data-v-" + scopeId;
    }
    var hook;
    if (moduleIdentifier) {
      hook = function(context) {
        context = context || // cached call
        this.$vnode && this.$vnode.ssrContext || // stateful
        this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext;
        if (!context && typeof __VUE_SSR_CONTEXT__ !== "undefined") {
          context = __VUE_SSR_CONTEXT__;
        }
        if (injectStyles) {
          injectStyles.call(this, context);
        }
        if (context && context._registeredComponents) {
          context._registeredComponents.add(moduleIdentifier);
        }
      };
      options._ssrRegister = hook;
    } else if (injectStyles) {
      hook = shadowMode ? function() {
        injectStyles.call(
          this,
          (options.functional ? this.parent : this).$root.$options.shadowRoot
        );
      } : injectStyles;
    }
    if (hook) {
      if (options.functional) {
        options._injectStyles = hook;
        var originalRender = options.render;
        options.render = function renderWithStyleInjection(h, context) {
          hook.call(context);
          return originalRender(h, context);
        };
      } else {
        var existing = options.beforeCreate;
        options.beforeCreate = existing ? [].concat(existing, hook) : [hook];
      }
    }
    return {
      exports: scriptExports,
      options
    };
  }
  const _sfc_main$d = {
    props: {
      data: [Object, Array],
      stats: [Boolean, Array],
      tab: String,
      tabs: Array,
      timespan: Object
    },
    data() {
      return {
        searching: false,
        q: null,
        pagination: {
          page: 1,
          limit: 20
        }
      };
    },
    computed: {
      buttons() {
        return [];
      },
      columns() {
        return {};
      },
      empty() {
        return {};
      },
      filteredItems() {
        let items = this.data;
        if (this.q) {
          items = items.filter(
            (item) => JSON.stringify(item).toLowerCase().includes(this.q.toLowerCase())
          );
        }
        return items;
      },
      help() {
        if (this.stats) {
          return;
        }
        return `${this.$t(
          "retour.system.support"
        )}: 💛 <a href='https://paypal.me/distantnative'><strong> ${this.$t(
          "retour.system.support.donate"
        )}</strong></a>`;
      },
      paginatedItems() {
        return this.filteredItems.slice(
          this.pagination.limit * (this.pagination.page - 1),
          this.pagination.limit * this.pagination.page
        );
      }
    },
    methods: {
      id(path) {
        return encodeURIComponent(path.replace(/\//g, ""));
      },
      onCell({ row, columnIndex }) {
      },
      options(item) {
        return [];
      },
      toggleSearch() {
        this.searching = !this.searching;
        this.q = null;
      }
    }
  };
  var _sfc_render$d = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("k-inside", { staticClass: "k-retour-view k-retour-collection-view", scopedSlots: _vm._u([_vm.stats ? { key: "topbar", fn: function() {
      return [_c("k-retour-timespan", { attrs: { "timespan": _vm.timespan } })];
    }, proxy: true } : null], null, true) }, [_vm.stats ? _c("k-retour-stats", { attrs: { "data": _vm.stats, "timespan": _vm.timespan } }) : _vm._e(), _c("k-retour-tabs", { attrs: { "tab": _vm.tab, "tabs": _vm.tabs }, scopedSlots: _vm._u([{ key: "buttons", fn: function() {
      return [_c("k-button-group", { attrs: { "buttons": [{ icon: "search", click: _vm.toggleSearch }, ..._vm.buttons], "size": "sm", "variant": "filled" } })];
    }, proxy: true }]) }), _vm.searching ? _c("k-input", { staticClass: "k-models-section-search", attrs: { "autofocus": true, "placeholder": _vm.$t("search") + " …", "value": _vm.q, "type": "text" }, on: { "input": function($event) {
      _vm.q = $event;
      _vm.pagination.page = 1;
    }, "keydown": function($event) {
      if (!$event.type.indexOf("key") && _vm._k($event.keyCode, "esc", 27, $event.key, ["Esc", "Escape"]))
        return null;
      return _vm.toggleSearch.apply(null, arguments);
    } } }) : _vm._e(), _vm.filteredItems.length === 0 ? _c("k-empty", _vm._b({ attrs: { "layout": "table" } }, "k-empty", _vm.empty, false)) : _c("k-table", { attrs: { "columns": _vm.columns, "rows": _vm.paginatedItems }, on: { "cell": _vm.onCell }, scopedSlots: _vm._u([{ key: "options", fn: function({ row }) {
      return [_c("k-options-dropdown", { attrs: { "options": _vm.options(row) } })];
    } }]) }), _c("footer", { staticClass: "k-bar k-collection-footer" }, [_c("k-text", { staticClass: "k-help", domProps: { "innerHTML": _vm._s(_vm.help) } }), _c("k-pagination", _vm._b({ attrs: { "details": true, "total": _vm.filteredItems.length }, on: { "paginate": function($event) {
      _vm.pagination.page = $event.page;
    } } }, "k-pagination", _vm.pagination, false))], 1)], 1);
  };
  var _sfc_staticRenderFns$d = [];
  _sfc_render$d._withStripped = true;
  var __component__$d = /* @__PURE__ */ normalizeComponent(
    _sfc_main$d,
    _sfc_render$d,
    _sfc_staticRenderFns$d,
    false,
    null,
    null,
    null,
    null
  );
  __component__$d.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Views/CollectionView.vue";
  const CollectionView = __component__$d.exports;
  const _sfc_main$c = {
    extends: CollectionView,
    computed: {
      buttons() {
        return [
          {
            icon: "add",
            text: this.$t("add"),
            click: () => this.$drawer("retour/redirects/create")
          }
        ];
      },
      columns() {
        let columns = {
          from: {
            label: this.$t("retour.redirects.from"),
            type: "path",
            width: "7/20"
          },
          to: {
            label: this.$t("retour.redirects.to"),
            type: "link",
            width: "7/20"
          },
          status: {
            label: this.$t("retour.redirects.status"),
            type: "status",
            width: "1/10"
          },
          priority: {
            label: this.$t("retour.redirects.priority.abbr"),
            type: "priority",
            width: "1/10"
          }
        };
        if (!!this.stats) {
          columns.hits = {
            label: this.$t("retour.hits"),
            width: "1/10",
            type: "count"
          };
        }
        return columns;
      },
      empty() {
        return {
          icon: "shuffle",
          text: this.$t("retour.redirects.empty")
        };
      }
    },
    methods: {
      onCell({ row, columnIndex }) {
        this.$drawer(`retour/redirects/${this.id(row.from)}/edit`, {
          query: {
            column: columnIndex
          }
        });
      },
      options(redirect) {
        return [
          {
            text: this.$t("edit"),
            icon: "edit",
            click: () => this.$drawer(`retour/redirects/${this.id(redirect.from)}/edit`)
          },
          {
            text: this.$t("remove"),
            icon: "trash",
            click: () => this.$dialog(`retour/redirects/${this.id(redirect.from)}/delete`)
          }
        ];
      }
    }
  };
  const _sfc_render$c = null;
  const _sfc_staticRenderFns$c = null;
  var __component__$c = /* @__PURE__ */ normalizeComponent(
    _sfc_main$c,
    _sfc_render$c,
    _sfc_staticRenderFns$c,
    false,
    null,
    null,
    null,
    null
  );
  __component__$c.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Views/RedirectsView.vue";
  const RedirectsView = __component__$c.exports;
  const _sfc_main$b = {
    extends: CollectionView,
    computed: {
      buttons() {
        return [
          {
            icon: "trash",
            text: this.$t("retour.failures.clear"),
            click: () => this.$dialog("retour/failures/flush")
          }
        ];
      },
      columns() {
        return {
          path: {
            label: this.$t("retour.failures.path"),
            type: "path",
            width: "1/2"
          },
          referrer: {
            label: this.$t("retour.failures.referrer"),
            type: "path",
            width: "1/2"
          },
          hits: {
            label: this.$t("retour.hits"),
            type: "count",
            width: "1/12",
            align: "right"
          }
        };
      },
      empty() {
        return {
          icon: "alert",
          text: this.$t("retour.failures.empty")
        };
      }
    },
    methods: {
      options(failure) {
        return [
          {
            text: this.$t("retour.failures.resolve"),
            icon: "add",
            click: () => this.$drawer(`retour/failures/${this.id(failure.path)}/resolve`)
          },
          {
            text: this.$t("remove"),
            icon: "trash",
            click: () => this.$dialog(`retour/failures/${this.id(failure.path)}/delete`)
          }
        ];
      }
    }
  };
  const _sfc_render$b = null;
  const _sfc_staticRenderFns$b = null;
  var __component__$b = /* @__PURE__ */ normalizeComponent(
    _sfc_main$b,
    _sfc_render$b,
    _sfc_staticRenderFns$b,
    false,
    null,
    null,
    null,
    null
  );
  __component__$b.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Views/FailuresView.vue";
  const FailuresView = __component__$b.exports;
  const SystemView_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$a = {
    props: {
      data: [Object, Array],
      stats: [Boolean, Array],
      tabs: Array,
      timespan: Object
    },
    computed: {
      reports() {
        return [
          {
            label: this.$t("retour.system.redirects"),
            value: this.data.redirects
          },
          {
            label: this.$t("retour.system.failures"),
            value: this.data.failures
          },
          {
            label: this.$t("retour.system.deleteAfter"),
            value: this.$t("retour.system.deleteAfter.months", {
              count: this.data.deleteAfter
            })
          },
          {
            label: this.$t("retour.system.support"),
            value: `💛 ${this.$t("retour.system.support.donate")}`,
            link: "https://paypal.me/distantnative",
            theme: "positive"
          }
        ];
      }
    }
  };
  var _sfc_render$a = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("k-inside", { staticClass: "k-retour-view k-retour-system-view", scopedSlots: _vm._u([{ key: "topbar", fn: function() {
      return [_c("k-retour-timespan", { attrs: { "timespan": _vm.timespan } })];
    }, proxy: true }]) }, [_vm.stats ? _c("k-retour-stats", { attrs: { "data": _vm.stats, "timespan": _vm.timespan } }) : _vm._e(), _c("k-retour-tabs", { attrs: { "tabs": _vm.tabs, "tab": "system" } }), _c("k-stats", { attrs: { "reports": _vm.reports } }), _c("k-text", { staticClass: "k-help", domProps: { "innerHTML": _vm._s(
      _vm.$t("retour.system.docs", {
        docs: "https://github.com/distantnative/retour-for-kirby"
      })
    ) } })], 1);
  };
  var _sfc_staticRenderFns$a = [];
  _sfc_render$a._withStripped = true;
  var __component__$a = /* @__PURE__ */ normalizeComponent(
    _sfc_main$a,
    _sfc_render$a,
    _sfc_staticRenderFns$a,
    false,
    null,
    null,
    null,
    null
  );
  __component__$a.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Views/SystemView.vue";
  const SystemView = __component__$a.exports;
  const Pie_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$9 = {
    props: {
      data: Array
    },
    computed: {
      gradient() {
        let gradient = "";
        let size = 0;
        const deg = this.total / 180;
        for (let i = 0; i < this.data.length; i++) {
          gradient += `${this.data[i].color} ${size}deg,`;
          size += this.data[i].data / deg;
          gradient += `${this.data[i].color} ${size}deg,`;
        }
        gradient += "transparent 180deg";
        return gradient;
      },
      total() {
        return this.data.reduce((i, x) => i += x.data, 0);
      }
    }
  };
  var _sfc_render$9 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("figure", { staticClass: "chart-pie" }, [_c("div", { staticClass: "graph", style: "--gradient: " + _vm.gradient }), _c("figcaption", [_vm._l(_vm.data, function(segment) {
      return [_c("k-icon", { key: segment.label + "-icon", style: "--color:" + segment.color, attrs: { "type": "circle" } }), _c("div", { key: segment.label + "-no" }, [_vm._v(" " + _vm._s(new Intl.NumberFormat().format(segment.data)) + " ")]), _c("div", { key: segment.label + "-label" }, [_vm._v(" " + _vm._s(segment.label) + " ")])];
    })], 2)]);
  };
  var _sfc_staticRenderFns$9 = [];
  _sfc_render$9._withStripped = true;
  var __component__$9 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$9,
    _sfc_render$9,
    _sfc_staticRenderFns$9,
    false,
    null,
    null,
    null,
    null
  );
  __component__$9.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Stats/Pie.vue";
  const Pie = __component__$9.exports;
  const Timeline_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$8 = {
    props: {
      data: Array,
      timespan: Object
    },
    computed: {
      axisY() {
        const span = this.max / 5;
        return [1, 2, 3, 4, 5].map((x) => {
          let tick = x * span;
          if (tick >= 1e3) {
            tick = Math.floor(tick / 100) / 10 + "k";
          }
          return tick;
        });
      },
      format() {
        return {
          day: "HH",
          week: "ddd",
          month: "D",
          year: "MMM",
          months: "MMM YY"
        }[this.timespan.unit] ?? "D MMM";
      },
      subunit() {
        return {
          day: "hour",
          year: "month"
        }[this.timespan.unit] ?? "day";
      },
      max() {
        let max = Math.max(
          ...this.data.map((segment) => {
            return segment.areas.reduce((i, x) => i += x.data, 0);
          })
        );
        if (max > 0) {
          const digits = max.toString().length;
          const next = Math.pow(10, digits) / 4;
          return Math.ceil(max * 1.1 / next) * next;
        }
        return 5;
      }
    },
    methods: {
      bounds(segment, area) {
        const stack = segment.areas.slice(0, area).reduce((i, x) => i += x.data, 0);
        return {
          path: stack + segment.areas[area].data,
          mask: stack
        };
      },
      clip(segment, area) {
        let current = this.bounds(this.data[segment], area);
        let next = { path: 0, mask: 0 };
        if (this.data[segment + 1]) {
          next = this.bounds(this.data[segment + 1], area);
        }
        return `--p0: ${current.path / this.max}; --p1: ${next.path / this.max}; --m0: ${current.mask / this.max}; --m1: ${next.mask / this.max};`;
      },
      color(segment, area) {
        const next = this.data[segment + 1];
        if (next) {
          const date = this.$library.dayjs(next.label);
          const today = this.$library.dayjs();
          if (date.isAfter(today, this.subunit)) {
            return "transparent";
          }
        }
        return area.color;
      },
      isCurrent(segment) {
        const today = this.$library.dayjs();
        return this.$library.dayjs(segment.label).isSame(today, this.subunit);
      },
      label(segment) {
        return this.$library.dayjs(segment.label).format(this.format);
      },
      onZoom(segment) {
        const date = this.$library.dayjs(segment.label);
        this.$reload({
          query: {
            from: date.startOf(this.subunit).format("YYYY-MM-DD"),
            to: date.endOf(this.subunit).format("YYYY-MM-DD")
          }
        });
      }
    }
  };
  var _sfc_render$8 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("table", { staticClass: "chart-areas" }, [_c("thead", [_c("tr", _vm._l(_vm.axisY, function(tick) {
      return _c("th", { key: tick }, [_vm._v(" " + _vm._s(tick) + " ")]);
    }), 0)]), _c("tbody", _vm._l(_vm.data, function(segment, segmentIndex) {
      return _c("tr", { key: segmentIndex, on: { "dblclick": function($event) {
        return _vm.onZoom(segment);
      } } }, _vm._l(segment.areas, function(area, areaIndex) {
        return _c("td", { key: segmentIndex + "_" + areaIndex, style: `--color: ${_vm.color(segmentIndex, area)}; ${_vm.clip(
          segmentIndex,
          areaIndex
        )}` });
      }), 0);
    }), 0), _c("tfoot", { attrs: { "data-less": _vm.data.length > 30 } }, _vm._l(_vm.data, function(segment) {
      return _c("tr", { key: segment.label, attrs: { "data-current": _vm.isCurrent(segment) }, on: { "dblclick": function($event) {
        return _vm.onZoom(segment);
      } } }, [_c("td", [_vm._v(_vm._s(_vm.label(segment)))])]);
    }), 0)]);
  };
  var _sfc_staticRenderFns$8 = [];
  _sfc_render$8._withStripped = true;
  var __component__$8 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$8,
    _sfc_render$8,
    _sfc_staticRenderFns$8,
    false,
    null,
    null,
    null,
    null
  );
  __component__$8.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Stats/Timeline.vue";
  const Timeline = __component__$8.exports;
  const Stats_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$7 = {
    components: {
      Pie,
      Timeline
    },
    props: {
      data: Array,
      timespan: Object
    },
    computed: {
      areas() {
        return this.data.map((entry) => ({
          label: entry.date,
          areas: [
            {
              data: entry.redirected,
              color: "var(--color-blue-600)"
            },
            {
              data: entry.resolved,
              color: "var(--color-gray-300)"
            },
            {
              data: entry.failed,
              color: "var(--color-red-600)"
            }
          ]
        }));
      },
      pie() {
        return [
          {
            data: this.data.reduce((i, x) => i += x.redirected, 0),
            color: "var(--color-blue-600)",
            label: this.$t("retour.stats.redirected")
          },
          {
            data: this.data.reduce((i, x) => i += x.resolved, 0),
            color: "var(--color-gray-300)",
            label: this.$t("retour.stats.resolved")
          },
          {
            data: this.data.reduce((i, x) => i += x.failed, 0),
            color: "var(--color-red-600)",
            label: this.$t("retour.stats.failed")
          }
        ];
      }
    }
  };
  var _sfc_render$7 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("section", { staticClass: "k-retour-stats" }, [_c("pie", { attrs: { "data": _vm.pie } }), _c("timeline", { attrs: { "data": _vm.areas, "timespan": _vm.timespan } })], 1);
  };
  var _sfc_staticRenderFns$7 = [];
  _sfc_render$7._withStripped = true;
  var __component__$7 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$7,
    _sfc_render$7,
    _sfc_staticRenderFns$7,
    false,
    null,
    null,
    null,
    null
  );
  __component__$7.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Stats/Stats.vue";
  const Stats = __component__$7.exports;
  const Tabs_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$6 = {
    props: {
      tab: String,
      tabs: Array
    }
  };
  var _sfc_render$6 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("div", { staticClass: "k-retour-tabs", attrs: { "data-end": _vm.tabs.length < 2 } }, [_c("k-tabs", { attrs: { "tab": _vm.tab, "tabs": _vm.tabs } }), _vm._t("buttons")], 2);
  };
  var _sfc_staticRenderFns$6 = [];
  _sfc_render$6._withStripped = true;
  var __component__$6 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$6,
    _sfc_render$6,
    _sfc_staticRenderFns$6,
    false,
    null,
    null,
    null,
    null
  );
  __component__$6.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Navigation/Tabs.vue";
  const Tabs = __component__$6.exports;
  const _sfc_main$5 = {
    props: {
      timespan: Object
    },
    methods: {
      icon(unit) {
        if (this.isCurrentUnit(unit) === true) {
          return this.timespan.isCurrent ? "retour-circle-focus" : "circle-nested";
        }
        return "circle";
      },
      isCurrentUnit(unit) {
        return unit === this.timespan.unit || unit === "all" && this.timespan.isAll;
      },
      navigate(method) {
        const unit = this.timespan.unit;
        const from = this.$library.dayjs(this.timespan.from);
        const to = this.$library.dayjs(this.timespan.to);
        this.update({
          from: from[method](1, unit).startOf(unit),
          to: to[method](1, unit).endOf(unit)
        });
      },
      set(unit) {
        if (unit === "all") {
          return this.update({
            from: this.$library.dayjs(this.timespan.first),
            to: this.$library.dayjs(this.timespan.last)
          });
        }
        let date = this.$library.dayjs(this.timespan.from);
        if (unit === this.timespan.unit || unit === "today") {
          unit = this.timespan.unit;
          date = this.$library.dayjs();
        }
        this.update({
          from: date.startOf(unit),
          to: date.endOf(unit)
        });
      },
      update({ from, to }) {
        this.$reload({
          query: {
            from: from.format("YYYY-MM-DD"),
            to: to.format("YYYY-MM-DD")
          }
        });
      }
    }
  };
  var _sfc_render$5 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("k-button-group", { staticClass: "k-retour-timespan", attrs: { "layout": "collapsed" } }, [_c("k-dropdown", [_c("k-button", { attrs: { "dropdown": true, "text": _vm.timespan.label, "icon": "calendar", "size": "xs", "variant": "filled" }, on: { "click": function($event) {
      return _vm.$refs.units.toggle();
    } } }), _c("k-dropdown-content", { ref: "units", attrs: { "x-align": "end" } }, [_vm._l(["all", "year", "month", "day"], function(unit) {
      return unit !== "all" || _vm.timespan.hasAll ? _c("k-dropdown-item", { key: unit, attrs: { "current": _vm.isCurrentUnit(unit), "icon": _vm.icon(unit), "size": "xs", "variant": "filled" }, on: { "click": function($event) {
        return _vm.set(unit);
      } } }, [_vm._v(" " + _vm._s(_vm.$t("retour.stats.mode." + unit)) + " ")]) : _vm._e();
    }), _c("hr"), _c("k-dropdown-item", { attrs: { "size": "xs", "icon": "merge", "variant": "filled" }, on: { "click": () => _vm.set("today") } }, [_vm._v(" " + _vm._s(_vm.$t("retour.timespan.today.label")) + " ")]), _c("k-dropdown-item", { attrs: { "size": "xs", "icon": "calendar", "variant": "filled" }, on: { "click": () => _vm.$dialog("retour/timespan") } }, [_vm._v(" " + _vm._s(_vm.$t("retour.timespan.set.label")) + " ")])], 2)], 1), _c("k-button", { attrs: { "icon": "angle-left", "size": "xs", "variant": "filled", "disabled": !_vm.timespan.hasPrev || _vm.timespan.isAll }, on: { "click": function($event) {
      return _vm.navigate("subtract");
    } } }), _c("k-button", { attrs: { "disabled": !_vm.timespan.hasNext || _vm.timespan.isAll, "icon": "angle-right", "size": "xs", "variant": "filled" }, on: { "click": function($event) {
      return _vm.navigate("add");
    } } })], 1);
  };
  var _sfc_staticRenderFns$5 = [];
  _sfc_render$5._withStripped = true;
  var __component__$5 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$5,
    _sfc_render$5,
    _sfc_staticRenderFns$5,
    false,
    null,
    null,
    null,
    null
  );
  __component__$5.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Navigation/Timespan.vue";
  const Timespan = __component__$5.exports;
  const CountFieldPreview_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$4 = {
    props: {
      row: Object
    },
    computed: {
      title() {
        return this.$t("retour.hits.last") + ": " + this.row.last;
      }
    }
  };
  var _sfc_render$4 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("div", { staticClass: "k-count-preview-field", attrs: { "title": _vm.title } }, [_vm.row.hits > 0 ? [_vm._v(" " + _vm._s(_vm.row.hits) + " "), _c("k-icon", { attrs: { "title": _vm.title, "type": "clock" } })] : _c("p", [_vm._v("–")])], 2);
  };
  var _sfc_staticRenderFns$4 = [];
  _sfc_render$4._withStripped = true;
  var __component__$4 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$4,
    _sfc_render$4,
    _sfc_staticRenderFns$4,
    false,
    null,
    null,
    null,
    null
  );
  __component__$4.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Table/CountFieldPreview.vue";
  const CountFieldPreview = __component__$4.exports;
  const _sfc_main$3 = {
    props: {
      column: {
        type: Object,
        default: () => ({})
      },
      field: Object,
      value: [String, Object]
    },
    computed: {
      isExternal() {
        return this.value && this.value.startsWith("http");
      },
      link() {
        return this.isExternal ? this.value : window.panel.$urls.site + "/" + this.value;
      }
    }
  };
  var _sfc_render$3 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("p", { staticClass: "k-url-field-preview k-path-field-preview" }, [_c("k-link", { attrs: { "to": _vm.link, "title": `${_vm.column.label}: ${_vm.value}`, "target": "_blank" }, nativeOn: { "click": function($event) {
      $event.stopPropagation();
    } } }, [_vm._v(" " + _vm._s(_vm.value) + " ")])], 1);
  };
  var _sfc_staticRenderFns$3 = [];
  _sfc_render$3._withStripped = true;
  var __component__$3 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$3,
    _sfc_render$3,
    _sfc_staticRenderFns$3,
    false,
    null,
    null,
    null,
    null
  );
  __component__$3.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Table/PathFieldPreview.vue";
  const PathFieldPreview = __component__$3.exports;
  const PriorityFieldPreview_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$2 = {
    props: {
      value: String,
      column: Object
    }
  };
  var _sfc_render$2 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("div", { staticClass: "k-priority-field-preview" }, [_vm.value ? _c("k-icon", { attrs: { "type": "bolt", "title": _vm.column.label } }) : _c("p", [_vm._v("–")])], 1);
  };
  var _sfc_staticRenderFns$2 = [];
  _sfc_render$2._withStripped = true;
  var __component__$2 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$2,
    _sfc_render$2,
    _sfc_staticRenderFns$2,
    false,
    null,
    null,
    null,
    null
  );
  __component__$2.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Table/PriorityFieldPreview.vue";
  const PriorityFieldPreview = __component__$2.exports;
  const color = {
    computed: {
      color() {
        if (!this.value) {
          return "var(--color-border)";
        }
        if (parseInt(this.value) >= 400) {
          return "var(--color-negative-light)";
        }
        if (parseInt(this.value) >= 300) {
          return "var(--color-positive-light)";
        }
        return "var(--color-focus-light)";
      }
    }
  };
  const StatusFieldPreview_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$1 = {
    mixins: [color],
    props: {
      value: String,
      row: Object,
      column: Object
    }
  };
  var _sfc_render$1 = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("div", { staticClass: "k-status-field-preview", attrs: { "title": `${_vm.column.label}: ${_vm.value}` } }, [_c("k-icon", { style: "color: " + _vm.color, attrs: { "type": "circle-filled" } }), _c("kbd", [_vm._v(_vm._s(_vm.value))])], 1);
  };
  var _sfc_staticRenderFns$1 = [];
  _sfc_render$1._withStripped = true;
  var __component__$1 = /* @__PURE__ */ normalizeComponent(
    _sfc_main$1,
    _sfc_render$1,
    _sfc_staticRenderFns$1,
    false,
    null,
    null,
    null,
    null
  );
  __component__$1.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Table/StatusFieldPreview.vue";
  const StatusFieldPreview = __component__$1.exports;
  const _sfc_main = {
    extends: "k-select-field",
    mixins: [color],
    methods: {
      onInput(value) {
        this.value = value;
      }
    }
  };
  var _sfc_render = function render() {
    var _vm = this, _c = _vm._self._c;
    return _c("k-field", _vm._b({ staticClass: "k-select-field", attrs: { "input": _vm._uid } }, "k-field", _vm.$props, false), [_c("k-input", _vm._g(_vm._b({ ref: "input", attrs: { "id": _vm._uid, "type": "select", "theme": "field" }, on: { "input": _vm.onInput }, scopedSlots: _vm._u([{ key: "before", fn: function() {
      return [_c("k-icon", { attrs: { "type": "circle-filled", "color": _vm.color } })];
    }, proxy: true }]) }, "k-input", _vm.$props, false), _vm.$listeners))], 1);
  };
  var _sfc_staticRenderFns = [];
  _sfc_render._withStripped = true;
  var __component__ = /* @__PURE__ */ normalizeComponent(
    _sfc_main,
    _sfc_render,
    _sfc_staticRenderFns,
    false,
    null,
    null,
    null,
    null
  );
  __component__.options.__file = "/Users/nhoffmann/Sites/kirby/plugins/site/plugins/retour/src/panel/components/Fields/StatusField.vue";
  const StatusField = __component__.exports;
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
      "k-retour-system-view": SystemView
    },
    fields: {
      "retour-status": StatusField
    },
    icons: {
      "retour-circle-focus": '<path d="M12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20ZM12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 14C10.8954 14 10 13.1046 10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12C14 13.1046 13.1046 14 12 14Z"></path>'
    }
  });
})();
