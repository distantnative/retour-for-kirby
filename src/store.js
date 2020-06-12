export default (Vue) => ({
  namespaced: true,
  state: {
    data: {
      routes: [],
      failures: [],
      stats: []
    },
    selection: {
      from: Vue.$library.dayjs().startOf("month"),
      to: Vue.$library.dayjs().endOf("month"),
      all: false
    },
    system: {
      headers: [],
      logs: false,
      deleteAfter: null
    }
  },
  getters: {
    timeframe: state => ({
      from: state.selection.from.format("YYYY-MM-DD"),
      to:   state.selection.to.format("YYYY-MM-DD")
    }),
    days: state => {
      return state.selection.to.diff(state.selection.from, "day");
    },
    selection: state => {
      const from = state.selection.from;
      const to = state.selection.to;

      if (state.selection.all === true) {
        return "all";
      }

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
    SET_DATA(state, { type, data }) {
      Vue.$set(state.data, type, data);
    },
    SET_TIMEFRAME(state, dates) {
      state.selection.from = dates.from;
      state.selection.to   = dates.to;
    },
    SET_SYSTEM(state, data) {
      state.system = data;
    }
  },
  actions: {
    async load(context) {
      // what we need for sure
      await Promise.all([
        context.dispatch("system"),
        context.dispatch("routes")
      ]);

      // what we might need as well
      if (context.state.system.logs === true) {
        await Promise.all([
          context.dispatch("failures"),
          context.dispatch("stats")
        ]);

        Vue.$api.post("retour/logs/purge");
      }
    },
    async failures(context) {
      const timeframe = context.getters["timeframe"];
      const failures  = await Vue.$api.get("retour/failures", timeframe);
      context.commit("SET_DATA", { type: "failures", data: failures });
    },
    async routes(context) {
      const timeframe = context.getters["timeframe"];
      const routes    = await Vue.$api.get("retour/redirects", timeframe);
      context.commit("SET_DATA", { type: "routes", data: routes });
    },
    async stats(context) {
      const selection = context.getters["selection"];
      const stats = await Vue.$api.get("retour/stats", {
        view: selection ? selection : "custom",
        ...context.getters["timeframe"]
      });
      context.commit("SET_DATA", { type: "stats", data: stats });
    },
    async system(context) {
      const system = await Vue.$api.get("retour/system");
      context.commit("SET_SYSTEM", system);
    },
    all(context) {
      Vue.$api.get("retour/logs/all").then(response => {
        context.dispatch("timeframe", {
          from: Vue.$library.dayjs(response.first.date),
          to: Vue.$library.dayjs(response.last.date)
        });
        context.state.selection.all = true;
      })
    },
    timeframe(context, dates) {
      context.commit("SET_TIMEFRAME", dates);
      context.state.selection.all = false;
      context.dispatch("redirects");

      if (context.state.system.logs === true) {
        context.dispatch("fails");
        context.dispatch("stats");
      }
    },
  }
});
