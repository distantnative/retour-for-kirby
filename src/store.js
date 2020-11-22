export default (Vue) => ({
  namespaced: true,
  state: {
    data: {
      routes: [],
      failures: [],
      stats: []
    },
    system: {
      deleteAfter: null,
      hasLog: true,
      headers: [],
      release: null,
      version: null,
      update: 0
    },
    view: {
      dates: [
        Vue.$library.dayjs().startOf("month"),
        Vue.$library.dayjs().endOf("month")
      ],
      all: false
    }
  },
  getters: {
    dates: state => {
      return state.view.dates;
    },
    days: (state, getters) => {
      const dates = getters.dates;
      return dates[0].diff(dates[1], "day");
    },
    mode: (state, getters) => {
      if (state.view.all === true) {
        return "all";
      }

      const from = getters.dates[0];
      const to   = getters.dates[1];

      if (from.isSame(to, "day")) {
        return "day";
      }

      if (to.day(0) && from.isSame(to.subtract(6, 'day'), "day")) {
        return "week";
      }

      if (
        from.isSame(to, "month") &&
        from.date() === 1 &&
        to.date() === to.daysInMonth()
      ) {
        return "month";
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
    },
    timeframe: (state, getters) => ({
      from: getters.dates[0].format("YYYY-MM-DD"),
      to:   getters.dates[1].format("YYYY-MM-DD")
    }),
  },
  mutations: {
    SET_DATA(state, { type, data }) {
      Vue.$set(state.data, type, data);
    },
    SET_SYSTEM(state, data) {
      state.system = { ...state.system, ...data };
    },
    SET_VIEW(state, view) {
      state.view.dates = view.dates;
      state.view.all   = view.all || false;
    },
  },
  actions: {
    async load(context, reload = false) {
      let payload = [];

      if (reload !== false) {
        await Vue.$api.post("retour/log/purge");
        payload.push("system");
      }

      payload.push("routes");

      if (context.state.system.hasLog === true) {
        payload.push("failures");
        payload.push("stats");
      }

      await Promise.all(payload.map(load => context.dispatch(load)));
    },
    async failures(context) {
      const timeframe = context.getters["timeframe"];
      const failures  = await Vue.$api.get("retour/failures", timeframe);
      context.commit("SET_DATA", { type: "failures", data: failures });
    },
    async routes(context) {
      const timeframe = context.getters["timeframe"];
      const routes    = await Vue.$api.get("retour/routes", timeframe);
      context.commit("SET_DATA", { type: "routes", data: routes });
    },
    async stats(context) {
      const mode  = context.getters["mode"];
      const stats = await Vue.$api.get("retour/stats", {
        mode: mode || "custom",
        ...context.getters["timeframe"]
      });
      context.commit("SET_DATA", { type: "stats", data: stats });
    },
    async system(context, reload = false) {
      const system = await Vue.$api.get("retour/system", { reload: reload });
      context.commit("SET_SYSTEM", system);
    },
    async view(context, view) {
      if (view === "all") {
        const response = await Vue.$api.get("retour/log/all");
        context.commit("SET_VIEW", {
          dates: [
            Vue.$library.dayjs.utc(response.from.date),
            Vue.$library.dayjs.utc(response.to.date)
          ],
          all: true
        });
      } else {
        context.commit("SET_VIEW", {
          dates: view.map(date => Vue.$library.dayjs.utc(date))
        });
      }

      this.dispatch("load", true);
    }
  }
});
