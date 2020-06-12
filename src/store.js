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
      deleteAfter: null,
      headers: [],
      logs: false,
      release: null,
      version: null,
      update: 0
    }
  },
  getters: {
    hasLogs: state => {
      return state.system.logs !== false;
    },
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
    SET_SELECTION(state, dates) {
      state.selection.from = dates.from;
      state.selection.to   = dates.to;
      state.selection.all  = dates.all || false;
    },
    SET_SYSTEM(state, data) {
      state.system = { ...state.system, ...data };
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
    async selection(context, dates) {
      if (dates === "all") {
        const all = await Vue.$api.get("retour/logs/all");
        dates = {
          from: Vue.$library.dayjs(all.first.date),
          to: Vue.$library.dayjs(all.last.date),
          all: true
        };
      }

      context.commit("SET_SELECTION", dates);
      let load = [context.dispatch("redirects")];

      if (context.getters.hasLogs) {
        load.push(context.dispatch("failures"));
        load.push(context.dispatch("stats"));
      }

      await Promise.all(load);
    },
    async system(context) {
      const system = await Vue.$api.get("retour/system");
      context.commit("SET_SYSTEM", system);
    }
  }
});
