export default (Vue) => ({
  namespaced: true,
  state: {
    data: {
      manual: [],
      tracked: [],
      failures: [],
      stats: []
    },
    selection: {
      begin: Vue.$library.dayjs().startOf("month"),
      end: Vue.$library.dayjs().endOf("month"),
      all: false
    },
    system: {
      deleteAfter: null,
      hasLog: false,
      hasTracking: false,
      headers: [],
      release: null,
      version: null,
      update: 0
    }
  },
  getters: {
    days: state => {
      return state.selection.end.diff(state.selection.begin, "day");
    },
    mode: state => {
      if (state.selection.all === true) {
        return "all";
      }

      const begin = state.selection.begin;
      const end   = state.selection.end;

      if (
        begin.isSame(end, "date") &&
        begin.isSame(end, "month") &&
        begin.isSame(end, "year")
      ) {
        return "day";
      }

      if (
        begin.isSame(end, "month") &&
        begin.isSame(end, "year") &&
        begin.date() === 1 &&
        end.date() === end.daysInMonth()
      ) {
        return "month";
      }

      if (
        end.day() === 0 && begin.isSame(end.subtract(6, "day").startOf("day"))
      ) {
        return "week";
      } else if (
        begin.isSame(end.subtract(end.day() - 1, "day").startOf("day"))
      ) {
        return "week";
      }

      if (
        begin.isSame(end, "year") &&
        begin.date() === 1 &&
        begin.month() === 0 &&
        end.date() === 31 &&
        end.month() === 11
      ) {
        return "year";
      }

      return false;
    },
    timeframe: state => ({
      begin: state.selection.begin.format("YYYY-MM-DD"),
      end:   state.selection.end.format("YYYY-MM-DD")
    }),
  },
  mutations: {
    SET_DATA(state, { type, data }) {
      Vue.$set(state.data, type, data);
    },
    SET_SELECTION(state, dates) {
      state.selection.begin = dates.begin;
      state.selection.end   = dates.end;
      state.selection.all   = dates.all || false;
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
        context.dispatch("routes", "manual"),
        context.dispatch("routes", "tracked")
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
    async routes(context, type = "manual") {
      const timeframe = context.getters["timeframe"];
      const routes    = await Vue.$api.get("retour/routes/" + type, timeframe);
      context.commit("SET_DATA", { type: type, data: routes });
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
        const all = await Vue.$api.get("retour/log/all");
        selection = {
          begin: Vue.$library.dayjs(all.begin.date),
          end:   Vue.$library.dayjs(all.end.date),
          all:   true
        };
      }

      context.commit("SET_SELECTION", selection);
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
