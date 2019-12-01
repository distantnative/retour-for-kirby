
export default {
  namespaced: true,
  state: {
    data: {
      redirects: [],
      fails: [],
      stats: []
    },
    view: {
      table: "redirects",
      from: null,
      to: null
    },
    options: {
      headers: [],
      logs: false,
      deleteAfter: null
    }
  },
  getters: {
    days: state => {
      return state.view.to.diff(state.view.from, "day");
    },
    view: state => {
      const from = state.view.from;
      const to   = state.view.to;

      if (
        from.isSame(to, "date") &&
        from.isSame(to, "month") &&
        from.isSame(to, "year")
      ) {
        return "day";
      }

      if (
        from.isSame(to, "month") &&
        from.isSame(to, "year") &&
        from.date() === 1 &&
        to.date() === to.daysInMonth()
      ) {
        return "month";
      }

      if (
        to.day() === 0 && from.isSame(to.subtract(6, "day").startOf("day"))
      ) {
        return "week";
      } else if (
        from.isSame(to.subtract(to.day() - 1, "day").startOf("day"))
      ) {
        return "week";
      }


      if (
        from.isSame(to, "year") &&
        from.date() === 1 &&
        from.month() === 0 &&
        to.date() === 31 &&
        to.month() === 11
      ) {
        return "year";
      }

      return false;
    }
  },
  mutations: {
    SET_DATA(state, [type, data]) {
      this._vm.$set(state.data, type, data);
    },
    SET_OPTIONS(state, data) {
      this._vm.$set(state, "options", data);
    },
    SET_TABLE(state, table) {
      state.view.table = table;
    },
    SET_TIMEFRAME(state, dates) {
      state.view.from = dates.from;
      state.view.to = dates.to;
    }
  },
  actions: {
    init(context) {
      context.commit("SET_TIMEFRAME", {
        from: this._vm.$library.dayjs().startOf("month"),
        to: this._vm.$library.dayjs().endOf("month")
      });
    },
    fails(context, dates) {
      return this._vm.$api.get("retour/fails", dates).then(response => {
        context.commit("SET_DATA", ["fails", response]);
      });
    },
    redirects(context, dates) {
      return this._vm.$api.get("retour/redirects", dates).then(response => {
        context.commit("SET_DATA", ["redirects", response]);
      });
    },
    stats(context, dates) {
      const view = context.getters["view"];
      return this._vm.$api.get("retour/stats/", {
        view: view ? view : "custom",
        ...dates
      }).then(response => {
        context.commit("SET_DATA", ["stats", response]);
      });
    },
    system(context) {
      return this._vm.$api.get("retour/system").then(response => {
        context.commit("SET_OPTIONS", response);
      });
    },
    load(context) {
      context.dispatch("system").then(() => {
        const dates = {
          from: context.state.view.from.format("YYYY-MM-DD HH:mm:ss"),
          to:   context.state.view.to.format("YYYY-MM-DD HH:mm:ss")
        };

        context.dispatch("redirects", dates);

        if (context.state.options.logs === true) {
          context.dispatch("fails", dates);
          context.dispatch("stats", dates);
          this._vm.$api.post("retour/limit");
        }
      });
    },
    table(context, table) {
      context.commit("SET_TABLE", table);
    },
    timeframe(context, dates) {
      context.commit("SET_TIMEFRAME", dates);
      context.dispatch("load");
    }
  }
};
