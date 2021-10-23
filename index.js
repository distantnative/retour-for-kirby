(function() {
  "use strict";
  var render$h = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-dropdown", { staticClass: "dates" }, [_c("k-button-group", [_c("k-button", { attrs: { "icon": "calendar" }, on: { "click": function($event) {
      return _vm.$refs.calendar.open();
    } } }, [_vm._v(" " + _vm._s(_vm.label) + " ")]), _c("k-dropdown-content", { ref: "calendar" }, [_c("k-calendar", { attrs: { "multiple": true, "value": _vm.value }, on: { "input": _vm.onInput } })], 1)], 1)], 1);
  };
  var staticRenderFns$h = [];
  render$h._withStripped = true;
  var Dates_vue_vue_type_style_index_0_lang = "";
  function normalizeComponent(scriptExports, render2, staticRenderFns2, functionalTemplate, injectStyles, scopeId, moduleIdentifier, shadowMode) {
    var options = typeof scriptExports === "function" ? scriptExports.options : scriptExports;
    if (render2) {
      options.render = render2;
      options.staticRenderFns = staticRenderFns2;
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
        context = context || this.$vnode && this.$vnode.ssrContext || this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext;
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
        injectStyles.call(this, (options.functional ? this.parent : this).$root.$options.shadowRoot);
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
  const __vue2_script$h = {
    props: {
      dates: Object,
      timespan: Object
    },
    computed: {
      label() {
        const from = this.dates.from;
        const to = this.dates.to;
        if (this.timespan.unit === "day") {
          return `${from.format("D")} ${this.month(from)} ${from.format("YYYY")}`;
        }
        if (this.timespan.unit === "month") {
          return `${this.month(from)} ${from.format("YYYY")}`;
        }
        if (this.timespan.unit === "year") {
          return `${from.format("YYYY")}`;
        }
        if (from.isSame(to, "month")) {
          return `${from.format("D")} - ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
        }
        if (from.isSame(to, "year")) {
          return `${from.format("D")} ${this.month(from)} - ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
        }
        return `${from.format("D")} ${this.month(from)} ${from.format("YYYY")} - ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
      },
      value() {
        return Object.values(this.dates).map((date) => date.format("YYYY-MM-DD HH:mm:ss"));
      }
    },
    methods: {
      month(date) {
        date = date.format("MMMM");
        date = this.$helper.string.lcfirst(date);
        return this.$t("months." + date);
      },
      onInput(values) {
        if (values.length === 2) {
          this.$emit("navigate", {
            from: this.$library.dayjs(values[0]),
            to: this.$library.dayjs(values[1])
          });
          this.$refs.calendar.close();
        }
      }
    }
  };
  const __cssModules$h = {};
  var __component__$h = /* @__PURE__ */ normalizeComponent(__vue2_script$h, render$h, staticRenderFns$h, false, __vue2_injectStyles$h, null, null, null);
  function __vue2_injectStyles$h(context) {
    for (let o in __cssModules$h) {
      this[o] = __cssModules$h[o];
    }
  }
  __component__$h.options.__file = "src/panel/components/Interaction/Dates.vue";
  var Dates = /* @__PURE__ */ function() {
    return __component__$h.exports;
  }();
  var render$g = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-button-group", { staticClass: "prevnext" }, [_c("k-button", { attrs: { "icon": "angle-left", "disabled": !_vm.hasPrev || _vm.isAll }, on: { "click": function($event) {
      return _vm.onNavigate("subtract");
    } } }), _vm._l(["all", "year", "month", "day"], function(unit) {
      return _c("k-button", { key: unit, attrs: { "current": _vm.isCurrent(unit) }, on: { "click": function($event) {
        return _vm.set(unit);
      } } }, [_vm._v(" " + _vm._s(_vm.$t("retour.stats.mode." + unit)) + " ")]);
    }), _c("k-button", { attrs: { "icon": "angle-right", "disabled": !_vm.hasNext || _vm.isAll }, on: { "click": function($event) {
      return _vm.onNavigate("add");
    } } })], 2);
  };
  var staticRenderFns$g = [];
  render$g._withStripped = true;
  var PrevNext_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$g = {
    props: {
      dates: Object,
      timespan: Object
    },
    computed: {
      hasPrev() {
        return this.dates.from.isAfter(this.$library.dayjs(this.timespan.first));
      },
      hasNext() {
        return this.dates.to.isBefore(this.$library.dayjs(this.timespan.first)) || this.dates.to.isBefore(this.$library.dayjs());
      },
      isAll() {
        return this.dates.from.isSame(this.$library.dayjs(this.timespan.first), "day") && this.dates.to.isSame(this.$library.dayjs(this.timespan.last), "day");
      }
    },
    methods: {
      isCurrent(unit) {
        if (unit === "all") {
          return this.isAll;
        }
        return unit === this.timespan.unit;
      },
      set(unit) {
        let timespan = Object.assign({}, this.dates);
        if (unit === this.timespan.unit) {
          timespan = {
            from: this.$library.dayjs().clone(),
            to: this.$library.dayjs().clone()
          };
        }
        switch (unit) {
          case "all":
            timespan.from = this.$library.dayjs(this.timespan.first);
            timespan.to = this.$library.dayjs(this.timespan.last);
            break;
          default:
            timespan.from = timespan.from.startOf(unit);
            timespan.to = timespan.from.endOf(unit);
            break;
        }
        this.$emit("navigate", timespan);
      },
      onNavigate(method) {
        let unit = this.timespan.unit;
        let factor = 1;
        if (unit === "week") {
          factor = 7;
          unit = "day";
        }
        const timespan = this.dates;
        timespan.from = timespan.from[method](factor, unit).startOf(unit);
        timespan.to = timespan.to[method](factor, unit).endOf(unit);
        this.$emit("navigate", timespan);
      }
    }
  };
  const __cssModules$g = {};
  var __component__$g = /* @__PURE__ */ normalizeComponent(__vue2_script$g, render$g, staticRenderFns$g, false, __vue2_injectStyles$g, null, null, null);
  function __vue2_injectStyles$g(context) {
    for (let o in __cssModules$g) {
      this[o] = __cssModules$g[o];
    }
  }
  __component__$g.options.__file = "src/panel/components/Interaction/PrevNext.vue";
  var PrevNext = /* @__PURE__ */ function() {
    return __component__$g.exports;
  }();
  var render$f = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-view", { staticClass: "retour-navigation" }, [_c("header", { staticClass: "k-header" }, [_c("k-bar", { staticClass: "k-header-buttons", scopedSlots: _vm._u([{ key: "left", fn: function() {
      return [_c("dates", { attrs: { "dates": _vm.dates, "timespan": _vm.timespan }, on: { "navigate": _vm.navigate } })];
    }, proxy: true }, { key: "right", fn: function() {
      return [_c("prev-next", { attrs: { "dates": _vm.dates, "timespan": _vm.timespan }, on: { "navigate": _vm.navigate } })];
    }, proxy: true }]) }), _c("k-tabs", { attrs: { "tab": _vm.tab, "tabs": _vm.tabs } })], 1)]);
  };
  var staticRenderFns$f = [];
  render$f._withStripped = true;
  var Navigation_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$f = {
    components: {
      Dates,
      PrevNext
    },
    props: {
      tab: String,
      tabs: Array,
      timespan: Object
    },
    computed: {
      dates() {
        return {
          from: this.$library.dayjs(this.timespan.from),
          to: this.$library.dayjs(this.timespan.to)
        };
      }
    },
    methods: {
      navigate(timespan) {
        this.$reload({
          query: {
            from: timespan.from.format("YYYY-MM-DD"),
            to: timespan.to.format("YYYY-MM-DD")
          }
        });
      }
    }
  };
  const __cssModules$f = {};
  var __component__$f = /* @__PURE__ */ normalizeComponent(__vue2_script$f, render$f, staticRenderFns$f, false, __vue2_injectStyles$f, null, null, null);
  function __vue2_injectStyles$f(context) {
    for (let o in __cssModules$f) {
      this[o] = __cssModules$f[o];
    }
  }
  __component__$f.options.__file = "src/panel/components/Interaction/Navigation.vue";
  var Navigation = /* @__PURE__ */ function() {
    return __component__$f.exports;
  }();
  var render$e = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("figure", { staticClass: "chart-pie" }, [_c("div", { staticClass: "graph", style: "--gradient: " + _vm.gradient }), _c("figcaption", [_c("ul", _vm._l(_vm.data, function(segment) {
      return _c("li", { key: segment.label }, [_c("k-icon", { style: "--color:" + segment.color, attrs: { "type": "circle" } }), _vm._v(" " + _vm._s(segment.data) + " " + _vm._s(segment.label) + " ")], 1);
    }), 0)])]);
  };
  var staticRenderFns$e = [];
  render$e._withStripped = true;
  var Pie_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$e = {
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
        gradient += `transparent 180deg`;
        return gradient;
      },
      total() {
        return this.data.reduce((i, x) => i += x.data, 0);
      }
    }
  };
  const __cssModules$e = {};
  var __component__$e = /* @__PURE__ */ normalizeComponent(__vue2_script$e, render$e, staticRenderFns$e, false, __vue2_injectStyles$e, null, null, null);
  function __vue2_injectStyles$e(context) {
    for (let o in __cssModules$e) {
      this[o] = __cssModules$e[o];
    }
  }
  __component__$e.options.__file = "src/panel/components/Graphs/Pie.vue";
  var Pie = /* @__PURE__ */ function() {
    return __component__$e.exports;
  }();
  var render$d = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("table", { staticClass: "chart-areas" }, [_c("thead", [_c("tr", _vm._l(_vm.axisY, function(tick) {
      return _c("th", { key: tick }, [_vm._v(_vm._s(tick))]);
    }), 0)]), _c("tbody", _vm._l(_vm.data, function(segment, segmentIndex) {
      return _c("tr", { key: segmentIndex, on: { "dblclick": function($event) {
        return _vm.onZoom(segment);
      } } }, _vm._l(segment.areas, function(area, areaIndex) {
        return _c("td", { key: _vm.segmentInde + "_" + areaIndex, style: "--color: " + _vm.color(segmentIndex, area) + "; " + _vm.clip(segmentIndex, areaIndex) });
      }), 0);
    }), 0), _c("tfoot", { attrs: { "data-less": _vm.data.length > 30 } }, _vm._l(_vm.data, function(segment) {
      return _c("tr", { key: segment.label, attrs: { "data-current": _vm.isCurrent(segment) }, on: { "dblclick": function($event) {
        return _vm.onZoom(segment);
      } } }, [_c("td", [_vm._v(_vm._s(_vm.label(segment)))])]);
    }), 0)]);
  };
  var staticRenderFns$d = [];
  render$d._withStripped = true;
  var Timeline_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$d = {
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
        switch (this.timespan.unit) {
          case "day":
            return "HH";
          case "week":
            return "ddd";
          case "month":
            return "D";
          case "year":
            return "MMM";
          case "months":
            return "MMM YY";
          default:
            return "D MMM";
        }
      },
      subunit() {
        switch (this.timespan.unit) {
          case "day":
            return "hour";
          case "year":
            return "month";
          default:
            return "day";
        }
      },
      max() {
        let max = Math.max(...this.data.map((segment) => {
          return segment.areas.reduce((i, x) => i += x.data, 0);
        }));
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
  const __cssModules$d = {};
  var __component__$d = /* @__PURE__ */ normalizeComponent(__vue2_script$d, render$d, staticRenderFns$d, false, __vue2_injectStyles$d, null, null, null);
  function __vue2_injectStyles$d(context) {
    for (let o in __cssModules$d) {
      this[o] = __cssModules$d[o];
    }
  }
  __component__$d.options.__file = "src/panel/components/Graphs/Timeline.vue";
  var Timeline = /* @__PURE__ */ function() {
    return __component__$d.exports;
  }();
  var render$c = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-view", { staticClass: "retour-stats" }, [_c("pie", { attrs: { "data": _vm.pie } }), _c("timeline", { attrs: { "data": _vm.areas, "timespan": _vm.timespan } })], 1);
  };
  var staticRenderFns$c = [];
  render$c._withStripped = true;
  var Stats_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$c = {
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
        return this.data.map((entry) => {
          return {
            label: entry.date,
            areas: [
              {
                data: entry.redirected,
                color: "var(--color-focus)"
              },
              {
                data: entry.resolved,
                color: "var(--color-border)"
              },
              {
                data: entry.failed,
                color: "var(--color-negative)"
              }
            ]
          };
        });
      },
      pie() {
        return [
          {
            data: this.data.reduce((i, x) => i += x.redirected, 0),
            color: "var(--color-focus)",
            label: this.$t("retour.data.redirected")
          },
          {
            data: this.data.reduce((i, x) => i += x.resolved, 0),
            color: "var(--color-border)",
            label: this.$t("retour.stats.resolved")
          },
          {
            data: this.data.reduce((i, x) => i += x.failed, 0),
            color: "var(--color-negative)",
            label: this.$t("retour.stats.failed")
          }
        ];
      }
    }
  };
  const __cssModules$c = {};
  var __component__$c = /* @__PURE__ */ normalizeComponent(__vue2_script$c, render$c, staticRenderFns$c, false, __vue2_injectStyles$c, null, null, null);
  function __vue2_injectStyles$c(context) {
    for (let o in __cssModules$c) {
      this[o] = __cssModules$c[o];
    }
  }
  __component__$c.options.__file = "src/panel/components/Interaction/Stats.vue";
  var Stats = /* @__PURE__ */ function() {
    return __component__$c.exports;
  }();
  var render$b = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("section", { staticClass: "list" }, [_c("header", [_c("div", { staticClass: "filter" }, [_c("label", { attrs: { "for": "filter" } }, [_c("k-icon", { attrs: { "type": "search" } })], 1), _c("input", { directives: [{ name: "model", rawName: "v-model", value: _vm.filter, expression: "filter" }], attrs: { "data-empty": !_vm.filter, "placeholder": _vm.$t("retour.table.filter"), "id": "filter" }, domProps: { "value": _vm.filter }, on: { "keydown": function($event) {
      if (!$event.type.indexOf("key") && _vm._k($event.keyCode, "esc", 27, $event.key, ["Esc", "Escape"])) {
        return null;
      }
      $event.stopPropagation();
      _vm.filter = null;
      $event.target.blur();
    }, "input": function($event) {
      if ($event.target.composing) {
        return;
      }
      _vm.filter = $event.target.value;
    } } })]), _vm._t("button")], 2), _vm.rows.length ? _c("k-table", { attrs: { "columns": _vm.columns, "index": _vm.limit * (_vm.page - 1) + 1, "options": _vm.options, "rows": _vm.paginatedRows }, on: { "cell": function($event) {
      return _vm.$emit("cell", $event);
    }, "header": function($event) {
      return _vm.$emit("header", $event);
    }, "input": function($event) {
      return _vm.$emit("input", $event);
    }, "option": _vm.onOption } }) : _c("k-empty", { attrs: { "layout": "cards" } }, [_vm._v(" " + _vm._s(_vm.empty) + " ")]), _c("footer", [_c("div", { staticClass: "limit" }, [_c("select", { domProps: { "value": _vm.storedLimit }, on: { "input": function($event) {
      return _vm.onLimit($event.target.value);
    } } }, [_c("option", { domProps: { "value": 10 } }, [_vm._v("10")]), _c("option", { domProps: { "value": 25 } }, [_vm._v("25")]), _c("option", { domProps: { "value": 50 } }, [_vm._v("50")]), _c("option", { attrs: { "value": "all" } }, [_vm._v(_vm._s(_vm.$t("retour.table.perPage.all")))])]), _vm._v("\xA0 " + _vm._s(_vm.$t("retour.table.perPage.after")) + " ")]), _c("k-pagination", { attrs: { "details": true, "limit": _vm.limit, "page": _vm.page, "total": _vm.rows.length }, on: { "paginate": _vm.onPaginate } }), _c("div")], 1)], 1);
  };
  var staticRenderFns$b = [];
  render$b._withStripped = true;
  var List_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$b = {
    props: {
      name: String,
      columns: Object,
      options: Array,
      rows: {
        type: Array,
        default() {
          return [];
        }
      },
      empty: String
    },
    data() {
      return {
        page: 1,
        filter: null,
        storedLimit: sessionStorage.getItem("retour$" + this.name + "$limit") || 10
      };
    },
    computed: {
      filteredRows() {
        if (!this.filter) {
          return this.rows;
        }
        const columns = Object.keys(this.columns).filter((key) => this.columns[key].filter === true);
        return this.rows.filter((row) => {
          let match = false;
          columns.forEach((column) => {
            if (row[column] && row[column].includes(this.filter) === true) {
              match = true;
            }
          });
          return match === true;
        });
      },
      paginatedRows() {
        if (!this.limit || this.limit === "all") {
          return this.filteredRows;
        }
        return this.filteredRows.slice(this.limit * (this.page - 1), this.limit * this.page);
      },
      limit() {
        return this.storedLimit === "all" ? this.rows.length : parseInt(this.storedLimit);
      }
    },
    watch: {
      filteredRows() {
        this.page = 1;
      }
    },
    methods: {
      onLimit(limit) {
        this.page = 1;
        this.storedLimit = limit;
        sessionStorage.setItem("retour$" + this.name + "$limit", limit);
      },
      onOption(option, row, rowIndex) {
        this.$emit("option", option, row, rowIndex);
      },
      onPaginate(pagination) {
        this.page = pagination.page;
      }
    }
  };
  const __cssModules$b = {};
  var __component__$b = /* @__PURE__ */ normalizeComponent(__vue2_script$b, render$b, staticRenderFns$b, false, __vue2_injectStyles$b, null, null, null);
  function __vue2_injectStyles$b(context) {
    for (let o in __cssModules$b) {
      this[o] = __cssModules$b[o];
    }
  }
  __component__$b.options.__file = "src/panel/components/List/List.vue";
  var List = /* @__PURE__ */ function() {
    return __component__$b.exports;
  }();
  var render$a = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-view", [_c("list", { attrs: { "name": "redirects", "columns": _vm.columns, "empty": _vm.$t("retour.redirects.empty"), "options": _vm.options, "rows": _vm.data }, on: { "cell": function($event) {
      return _vm.onOption("edit", $event.row, $event.rowIndex, $event.columnIndex);
    }, "option": _vm.onOption }, scopedSlots: _vm._u([{ key: "button", fn: function() {
      return [_c("k-button", { attrs: { "icon": "add" }, on: { "click": function($event) {
        return _vm.onOption("add");
      } } }, [_vm._v(" " + _vm._s(_vm.$t("add")) + " ")])];
    }, proxy: true }]) })], 1);
  };
  var staticRenderFns$a = [];
  render$a._withStripped = true;
  const __vue2_script$a = {
    components: {
      List
    },
    props: {
      data: Object,
      hasLog: Boolean
    },
    computed: {
      columns() {
        let columns = {
          from: {
            label: this.$t("retour.redirects.from"),
            type: "link",
            filter: true,
            width: "7/20"
          },
          to: {
            label: this.$t("retour.redirects.to"),
            type: "link",
            filter: true,
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
            width: "1/20"
          }
        };
        if (this.hasLog) {
          columns.hits = {
            label: this.$t("retour.hits"),
            width: "1/10",
            type: "count"
          };
        }
        return columns;
      },
      options() {
        return [
          { text: this.$t("edit"), icon: "edit", click: "edit" },
          { text: this.$t("remove"), icon: "trash", click: "remove" }
        ];
      }
    },
    methods: {
      onOption(option, row = {}, rowIndex = null, column = null) {
        switch (option) {
          case "add":
            return this.$dialog("retour/redirects/create");
          case "edit":
            return this.$dialog(`retour/redirects/${rowIndex}/edit`);
          case "remove":
            return this.$dialog(`retour/redirects/${rowIndex}/delete`);
        }
      }
    }
  };
  const __cssModules$a = {};
  var __component__$a = /* @__PURE__ */ normalizeComponent(__vue2_script$a, render$a, staticRenderFns$a, false, __vue2_injectStyles$a, null, null, null);
  function __vue2_injectStyles$a(context) {
    for (let o in __cssModules$a) {
      this[o] = __cssModules$a[o];
    }
  }
  __component__$a.options.__file = "src/panel/components/Tabs/RedirectsTab.vue";
  var RedirectsTab = /* @__PURE__ */ function() {
    return __component__$a.exports;
  }();
  var render$9 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-view", [_c("list", { attrs: { "name": "failures", "columns": _vm.columns, "empty": _vm.$t("retour.failures.empty"), "options": _vm.options, "rows": _vm.data }, on: { "option": _vm.onOption }, scopedSlots: _vm._u([{ key: "button", fn: function() {
      return [_c("k-button", { attrs: { "icon": "trash" }, on: { "click": function($event) {
        return _vm.$dialog("retour/failures/flush");
      } } }, [_vm._v(" " + _vm._s(_vm.$t("retour.failures.clear")) + " ")])];
    }, proxy: true }]) })], 1);
  };
  var staticRenderFns$9 = [];
  render$9._withStripped = true;
  const __vue2_script$9 = {
    components: {
      List
    },
    props: {
      data: Object
    },
    computed: {
      columns() {
        return {
          path: {
            label: this.$t("retour.failures.path"),
            type: "link",
            filter: true,
            width: "1/2"
          },
          referrer: {
            label: this.$t("retour.failures.referrer"),
            type: "link",
            filter: true,
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
      options() {
        return [
          { text: this.$t("retour.failures.resolve"), icon: "add", click: "resolve" },
          { text: this.$t("remove"), icon: "trash", click: "remove" }
        ];
      }
    },
    methods: {
      onOption(option, row) {
        switch (option) {
          case "remove":
            return this.$dialog(`retour/failures/${row.path}/delete`);
          case "resolve":
            return this.$dialog(`retour/failures/${row.path}/resolve`);
        }
      }
    }
  };
  const __cssModules$9 = {};
  var __component__$9 = /* @__PURE__ */ normalizeComponent(__vue2_script$9, render$9, staticRenderFns$9, false, __vue2_injectStyles$9, null, null, null);
  function __vue2_injectStyles$9(context) {
    for (let o in __cssModules$9) {
      this[o] = __cssModules$9[o];
    }
  }
  __component__$9.options.__file = "src/panel/components/Tabs/FailuresTab.vue";
  var FailuresTab = /* @__PURE__ */ function() {
    return __component__$9.exports;
  }();
  var render$8 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-view", { staticClass: "retour-plugin-tab" }, [_c("header", [_c("h3", [_c("k-icon", { attrs: { "type": "road-sign" } }), _vm._v(" Retour for Kirby ")], 1)]), _c("ul", { staticClass: "k-system-info-box" }, [_c("li", [_c("dl", [_c("dt", [_vm._v(" " + _vm._s(_vm.$t("retour.plugin.redirects")) + " ")]), _c("dd", [_vm._v(_vm._s(_vm.data.redirects))])])]), _c("li", [_c("dl", [_c("dt", [_vm._v(" " + _vm._s(_vm.$t("retour.plugin.failures")) + " ")]), _c("dd", [_vm._v(_vm._s(_vm.data.failures))])])]), _c("li", [_c("dl", [_c("dt", [_vm._v(" " + _vm._s(_vm.$t("retour.plugin.deleteAfter")) + " ")]), _c("dd", [_vm._v(" " + _vm._s(_vm.$t("retour.plugin.deleteAfter.months", { count: _vm.data.deleteAfter })) + " ")])])]), _c("li", [_c("dl", [_c("dt", [_vm._v(" " + _vm._s(_vm.$t("retour.plugin.support")) + " ")]), _c("dd", [_c("k-button", { attrs: { "link": "https://paypal.me/distantnative", "target": "_blank", "theme": "positive" } }, [_vm._v(" \u{1F49B} " + _vm._s(_vm.$t("retour.plugin.support.donate")) + " ")])], 1)])])]), _c("footer", { staticClass: "mt-2" }, [_c("k-text", { attrs: { "theme": "help" }, domProps: { "innerHTML": _vm._s(_vm.$t("retour.plugin.docs", {
      docs: "https://distantnative.com/retour/docs"
    })) } })], 1)]);
  };
  var staticRenderFns$8 = [];
  render$8._withStripped = true;
  var PluginTab_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$8 = {
    props: {
      data: Object
    }
  };
  const __cssModules$8 = {};
  var __component__$8 = /* @__PURE__ */ normalizeComponent(__vue2_script$8, render$8, staticRenderFns$8, false, __vue2_injectStyles$8, null, null, null);
  function __vue2_injectStyles$8(context) {
    for (let o in __cssModules$8) {
      this[o] = __cssModules$8[o];
    }
  }
  __component__$8.options.__file = "src/panel/components/Tabs/PluginTab.vue";
  var PluginTab = /* @__PURE__ */ function() {
    return __component__$8.exports;
  }();
  var render$7 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-inside", { staticClass: "retour" }, [_vm.stats ? [_c("stats", { attrs: { "data": _vm.stats, "timespan": _vm.timespan } }), _c("navigation", { attrs: { "tab": _vm.tab, "tabs": _vm.tabs, "timespan": _vm.timespan } })] : _vm._e(), _c("" + _vm.tab + "-tab", { tag: "component", attrs: { "data": _vm.data, "has-log": !!_vm.stats } })], 2);
  };
  var staticRenderFns$7 = [];
  render$7._withStripped = true;
  var View_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$7 = {
    components: {
      Stats,
      Navigation,
      FailuresTab,
      RedirectsTab,
      PluginTab
    },
    props: {
      data: [Object, Array],
      stats: [Boolean, Array],
      tab: String,
      tabs: Array,
      timespan: Array
    }
  };
  const __cssModules$7 = {};
  var __component__$7 = /* @__PURE__ */ normalizeComponent(__vue2_script$7, render$7, staticRenderFns$7, false, __vue2_injectStyles$7, null, null, null);
  function __vue2_injectStyles$7(context) {
    for (let o in __cssModules$7) {
      this[o] = __cssModules$7[o];
    }
  }
  __component__$7.options.__file = "src/panel/components/View.vue";
  var View = /* @__PURE__ */ function() {
    return __component__$7.exports;
  }();
  var render$6 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("table", _vm._g({ staticClass: "k-table", attrs: { "data-sortable": _vm.sortable } }, _vm.$listeners), [_c("thead", [_c("tr", [_c("th", { staticClass: "k-table-index-column" }, [_vm._v(" # ")]), _vm._l(_vm.columns, function(column, columnIndex) {
      return _c("th", { key: columnIndex + "-header", staticClass: "k-table-column", style: "width:" + _vm.width(column.width), on: { "click": function($event) {
        return _vm.onHeader({
          label: column.label || columnIndex,
          column,
          columnIndex
        });
      } } }, [_vm._t("header", function() {
        return [_c("p", { staticClass: "k-table-header-label" }, [_vm._v(" " + _vm._s(column.label || columnIndex) + " ")])];
      }, null, {
        column,
        columnIndex,
        label: column.label || columnIndex
      })], 2);
    }), _vm.options ? _c("th", { staticClass: "k-table-options-column" }, [_vm._t("button")], 2) : _vm._e()], 2)]), _c("k-draggable", { attrs: { "list": _vm.values, "options": _vm.dragOptions, "handle": true, "element": "tbody" }, on: { "end": _vm.onSort } }, _vm._l(_vm.values, function(row, rowIndex) {
      return _c("tr", { key: rowIndex }, [_c("td", { staticClass: "k-table-index-column" }, [_vm.sortable ? _c("k-sort-handle", { staticClass: "k-table-sort-handle" }) : _vm._e(), _c("span", { staticClass: "k-table-index" }, [_vm._v(_vm._s(_vm.indexOf(rowIndex)))])], 1), _vm._l(_vm.columns, function(column, columnIndex) {
        return _c("td", { key: rowIndex + "-" + columnIndex, staticClass: "k-table-column", style: "width:" + _vm.width(column.width), attrs: { "data-align": column.align, "title": column.label }, on: { "click": function($event) {
          return _vm.onCell({
            value: row[columnIndex],
            row,
            rowIndex,
            column,
            columnIndex
          });
        } } }, [_vm._t("cell", function() {
          return [_vm.isComponent("k-table-" + (column.type || "text") + "-cell") ? _c("k-table-" + (column.type || "text") + "-cell", { tag: "component", attrs: { "column": column, "row": row, "value": row[columnIndex] }, on: { "input": function($event) {
            return _vm.onCellUpdate({
              value: $event,
              row,
              rowIndex,
              column,
              columnIndex
            });
          } } }) : _c("p", { staticClass: "k-table-cell-value" }, [_vm._v(" " + _vm._s(column.before) + " " + _vm._s(row[columnIndex] || "") + " " + _vm._s(column.after) + " ")])];
        }, null, {
          column,
          columnIndex,
          row,
          rowIndex,
          value: row[columnIndex]
        })], 2);
      }), _vm.options ? _c("td", { staticClass: "k-table-options-column" }, [_c("k-options-dropdown", { attrs: { "options": _vm.options }, on: { "option": function($event) {
        return _vm.onOption($event, row, rowIndex);
      } } })], 1) : _vm._e()], 2);
    }), 0)], 1);
  };
  var staticRenderFns$6 = [];
  render$6._withStripped = true;
  var Table_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$6 = {
    props: {
      columns: Object,
      index: {
        type: Number,
        default: 1
      },
      rows: Array,
      options: [Array, Function],
      sortable: Boolean
    },
    data() {
      return {
        values: this.rows
      };
    },
    computed: {
      dragOptions() {
        return {
          disabled: !this.sortable,
          fallbackClass: "k-table-row-fallback",
          ghostClass: "k-table-row-ghost"
        };
      }
    },
    watch: {
      rows() {
        this.values = this.rows;
      }
    },
    methods: {
      isComponent(name) {
        if (this.$options.components[name] !== void 0) {
          return true;
        }
        return false;
      },
      indexOf(index) {
        return this.index + index;
      },
      onCell(params) {
        this.$emit("cell", params);
      },
      onCellUpdate({ columnIndex, rowIndex, value }) {
        this.values[rowIndex][columnIndex] = value;
        this.$emit("input", this.values);
      },
      onHeader(params) {
        this.$emit("header", params);
      },
      onOption(option, row, rowIndex) {
        this.$emit("option", option, row, rowIndex);
      },
      onSort() {
        this.$emit("input", this.values);
        this.$emit("sort", this.values);
      },
      width(fraction) {
        if (!fraction) {
          return "auto";
        }
        const parts = fraction.toString().split("/");
        if (parts.length !== 2) {
          return "auto";
        }
        const a = Number(parts[0]);
        const b = Number(parts[1]);
        return parseFloat(100 / b * a, 2).toFixed(2) + "%";
      }
    }
  };
  const __cssModules$6 = {};
  var __component__$6 = /* @__PURE__ */ normalizeComponent(__vue2_script$6, render$6, staticRenderFns$6, false, __vue2_injectStyles$6, null, null, null);
  function __vue2_injectStyles$6(context) {
    for (let o in __cssModules$6) {
      this[o] = __cssModules$6[o];
    }
  }
  __component__$6.options.__file = "src/panel/ployfills/Table.vue";
  var Table = /* @__PURE__ */ function() {
    return __component__$6.exports;
  }();
  var render$5 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("div", { staticClass: "k-table-count-cell", attrs: { "title": _vm.title } }, [_vm.row.hits > 0 ? [_vm._v(" " + _vm._s(_vm.row.hits) + " "), _c("k-icon", { attrs: { "title": _vm.title, "type": "clock" } })] : _c("p", [_vm._v("\u2013")])], 2);
  };
  var staticRenderFns$5 = [];
  render$5._withStripped = true;
  var TableCountCell_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$5 = {
    props: {
      row: Object
    },
    computed: {
      title() {
        return this.$t("retour.hits.last") + ": " + this.row.last;
      }
    }
  };
  const __cssModules$5 = {};
  var __component__$5 = /* @__PURE__ */ normalizeComponent(__vue2_script$5, render$5, staticRenderFns$5, false, __vue2_injectStyles$5, null, null, null);
  function __vue2_injectStyles$5(context) {
    for (let o in __cssModules$5) {
      this[o] = __cssModules$5[o];
    }
  }
  __component__$5.options.__file = "src/panel/components/List/Cells/TableCountCell.vue";
  var TableCountCell = /* @__PURE__ */ function() {
    return __component__$5.exports;
  }();
  var render$4 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("div", { staticClass: "k-table-link-cell", attrs: { "title": _vm.column.label + ": " + _vm.value } }, [_vm.value && _vm.value != "-" ? _c("k-button", { attrs: { "link": _vm.link, "icon": "url", "target": "_blank" }, nativeOn: { "click": function($event) {
      $event.stopPropagation();
    } } }) : _vm._e(), _vm._v(" " + _vm._s(_vm.value) + " ")], 1);
  };
  var staticRenderFns$4 = [];
  render$4._withStripped = true;
  var TableLinkCell_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$4 = {
    props: {
      value: String,
      column: Object
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
  const __cssModules$4 = {};
  var __component__$4 = /* @__PURE__ */ normalizeComponent(__vue2_script$4, render$4, staticRenderFns$4, false, __vue2_injectStyles$4, null, null, null);
  function __vue2_injectStyles$4(context) {
    for (let o in __cssModules$4) {
      this[o] = __cssModules$4[o];
    }
  }
  __component__$4.options.__file = "src/panel/components/List/Cells/TableLinkCell.vue";
  var TableLinkCell = /* @__PURE__ */ function() {
    return __component__$4.exports;
  }();
  var render$3 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("div", { staticClass: "k-table-priority-cell" }, [_vm.value ? _c("k-icon", { attrs: { "type": "bolt", "title": _vm.column.label } }) : _c("p", [_vm._v("\u2013")])], 1);
  };
  var staticRenderFns$3 = [];
  render$3._withStripped = true;
  var TablePriorityCell_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$3 = {
    props: {
      value: String,
      column: Object
    }
  };
  const __cssModules$3 = {};
  var __component__$3 = /* @__PURE__ */ normalizeComponent(__vue2_script$3, render$3, staticRenderFns$3, false, __vue2_injectStyles$3, null, null, null);
  function __vue2_injectStyles$3(context) {
    for (let o in __cssModules$3) {
      this[o] = __cssModules$3[o];
    }
  }
  __component__$3.options.__file = "src/panel/components/List/Cells/TablePriorityCell.vue";
  var TablePriorityCell = /* @__PURE__ */ function() {
    return __component__$3.exports;
  }();
  var color = {
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
  var render$2 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("div", { staticClass: "k-table-status-cell", attrs: { "title": _vm.column.label + ": " + (_vm.value || "-") } }, [_c("k-icon", { style: "color: " + _vm.color, attrs: { "type": "circle" } }), _vm.value ? _c("code", [_vm._v(_vm._s(_vm.value))]) : _c("span", [_vm._v("\xA0\u2013")])], 1);
  };
  var staticRenderFns$2 = [];
  render$2._withStripped = true;
  var TableStatusCell_vue_vue_type_style_index_0_lang = "";
  const __vue2_script$2 = {
    mixins: [color],
    props: {
      value: String,
      row: Object,
      column: Object
    }
  };
  const __cssModules$2 = {};
  var __component__$2 = /* @__PURE__ */ normalizeComponent(__vue2_script$2, render$2, staticRenderFns$2, false, __vue2_injectStyles$2, null, null, null);
  function __vue2_injectStyles$2(context) {
    for (let o in __cssModules$2) {
      this[o] = __cssModules$2[o];
    }
  }
  __component__$2.options.__file = "src/panel/components/List/Cells/TableStatusCell.vue";
  var TableStatusCell = /* @__PURE__ */ function() {
    return __component__$2.exports;
  }();
  var render$1 = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-field", _vm._b({ staticClass: "k-text-field", attrs: { "input": _vm._uid, "counter": _vm.counterOptions }, scopedSlots: _vm._u([{ key: "options", fn: function() {
      return [_c("k-button", { staticClass: "k-field-options-button", attrs: { "icon": "circle-nested" }, on: { "click": _vm.open } }, [_vm._v(" " + _vm._s(_vm.$t("select")) + " ")])];
    }, proxy: true }]) }, "k-field", _vm.$props, false), [_c("k-input", _vm._g(_vm._b({ ref: "input", attrs: { "id": _vm._uid, "type": "text", "theme": "field" } }, "k-input", _vm.$props, false), _vm.$listeners)), _c("k-pages-dialog", { ref: "selector", on: { "submit": _vm.onSelect } })], 1);
  };
  var staticRenderFns$1 = [];
  render$1._withStripped = true;
  const __vue2_script$1 = {
    extends: "k-text-field",
    methods: {
      open() {
        this.$refs.selector.open({
          endpoint: "retour/pagepicker",
          max: 1,
          multiple: false,
          selected: [],
          search: true
        });
      },
      onSelect(items) {
        this.$emit("input", items[0].id);
      }
    }
  };
  const __cssModules$1 = {};
  var __component__$1 = /* @__PURE__ */ normalizeComponent(__vue2_script$1, render$1, staticRenderFns$1, false, __vue2_injectStyles$1, null, null, null);
  function __vue2_injectStyles$1(context) {
    for (let o in __cssModules$1) {
      this[o] = __cssModules$1[o];
    }
  }
  __component__$1.options.__file = "src/panel/components/Fields/DestinationField.vue";
  var DestinationField = /* @__PURE__ */ function() {
    return __component__$1.exports;
  }();
  var render = function() {
    var _vm = this;
    var _h = _vm.$createElement;
    var _c = _vm._self._c || _h;
    return _c("k-field", _vm._b({ staticClass: "k-select-field", attrs: { "input": _vm._uid } }, "k-field", _vm.$props, false), [_c("k-input", _vm._g(_vm._b({ ref: "input", attrs: { "id": _vm._uid, "type": "select", "theme": "field" }, on: { "input": _vm.onInput }, scopedSlots: _vm._u([{ key: "before", fn: function() {
      return [_c("k-icon", { attrs: { "type": "circle", "color": _vm.color } })];
    }, proxy: true }]) }, "k-input", _vm.$props, false), _vm.$listeners))], 1);
  };
  var staticRenderFns = [];
  render._withStripped = true;
  const __vue2_script = {
    extends: "k-select-field",
    mixins: [color],
    methods: {
      onInput(value) {
        this.value = value;
      }
    }
  };
  const __cssModules = {};
  var __component__ = /* @__PURE__ */ normalizeComponent(__vue2_script, render, staticRenderFns, false, __vue2_injectStyles, null, null, null);
  function __vue2_injectStyles(context) {
    for (let o in __cssModules) {
      this[o] = __cssModules[o];
    }
  }
  __component__.options.__file = "src/panel/components/Fields/StatusField.vue";
  var StatusField = /* @__PURE__ */ function() {
    return __component__.exports;
  }();
  panel.plugin("distantnative/retour", {
    components: {
      "k-table": Table,
      "k-table-count-cell": TableCountCell,
      "k-table-link-cell": TableLinkCell,
      "k-table-priority-cell": TablePriorityCell,
      "k-table-status-cell": TableStatusCell,
      "k-retour-view": View
    },
    fields: {
      "rt-destination": DestinationField,
      "rt-status": StatusField
    }
  });
})();
