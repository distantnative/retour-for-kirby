export default (Vue) => ({
  namespaced: true,
  state: {
    data: {
      routes: [],
      failures: [],
      stats: []
    },
    selection: {
      view: [
        Vue.$library.dayjs.utc().startOf("month").format('YYYY-MM-DD HH:mm:ss'),
        Vue.$library.dayjs.utc().endOf("month").format('YYYY-MM-DD HH:mm:ss')
      ],
      all: false
    },
    system: {
      deleteAfter: null,
      hasLog: true,
      headers: [],
      release: null,
      version: null,
      update: 0
    }
  },
  getters: {
    days: state => {
      const from = Vue.$library.dayjs.utc(state.selection.view[0]);
      const to   = Vue.$library.dayjs.utc(state.selection.view[1]);
      return from.diff(to, "day");
    },
    mode: state => {
      if (state.selection.all === true) {
        return "all";
      }

      const from = Vue.$library.dayjs.utc(state.selection.view[0]);
      const to   = Vue.$library.dayjs.utc(state.selection.view[1]);

      if (from.isSame(to, "day")) {
        return "day";
      }

      if (to.day(0) && from.isSame(to.subtract(6, 'day'), "day")) {
        return "week";
      }

      if (from.isSame(to, "month")) {
        return "month";
      }

      if (from.isSame(to, "year")) {
        return "year";
      }

      return false;
    },
    timeframe: state => ({
      begin: Vue.$library.dayjs.utc(state.selection.view[0]).format("YYYY-MM-DD"),
      end:   Vue.$library.dayjs.utc(state.selection.view[1]).format("YYYY-MM-DD")
    }),
  },
  mutations: {
    SET_DATA(state, { type, data }) {
      Vue.$set(state.data, type, data);
    },
    SET_SELECTION(state, selection) {
      state.selection.view = selection.view;
      state.selection.all = selection.all || false;
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
      if (context.state.system.hasLog === true) {
        await Promise.all([
          context.dispatch("failures"),
          context.dispatch("stats")
        ]);

        await Vue.$api.post("retour/log/purge");
      }
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
    async selection(context, selection) {
      if (selection === "all") {
        const response = await Vue.$api.get("retour/log/all");
        selection = {
          view: [response.begin.date, response.end.date],
          all: true
        };
      }

      context.commit("SET_SELECTION", { view: selection });
      let load = [context.dispatch("redirects")];

      if (context.state.system.hasLog) {
        load.push(context.dispatch("failures"));
        load.push(context.dispatch("stats"));
      }

      await Promise.all(load);
    },
    async system(context, reload = false) {
      const system = await Vue.$api.get("retour/system", { reload: reload });
      context.commit("SET_SYSTEM", system);
    }
  }
});
