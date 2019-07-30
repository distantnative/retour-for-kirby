
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
      stats: "month",
      offset: 0,
      title: "..."
    },
    plugin: {
      version: null,
      headers: [],
      deleteAfter: null
    }
  },
  mutations: {
    SET_DATA(state, [type, data]) {
      this._vm.$set(state.data, type, data);
    },
    SET_PLUGIN(state, data) {
      this._vm.$set(state, "plugin", data);
    },
    SET_OFFSET(state, offset) {
      state.view.offset = state.view.offset + offset;
    },
    SET_STATS(state, stats) {
      state.view.stats = stats;
      state.view.offset = 0;
    },
    SET_TABLE(state, table) {
      state.view.table = table;
    },
    SET_TITLE(state, title) {
      state.view.title = title;
    }
  },
  actions: {
    fetchFails(context) {
      return this._vm.$api.get("retour/fails").then(response => {
        context.commit("SET_DATA", ["fails", response]);
      });
    },
    fetchRedirects(context) {
      return this._vm.$api.get("retour/redirects").then(response => {
        context.commit("SET_DATA", ["redirects", response]);
      });
    },
    fetchStats(context) {
      const endpoint = "retour/stats/" + context.state.view.stats + "/" + context.state.view.offset;
      return this._vm.$api.get(endpoint, {
        locale: context.rootState.user.current.language
      }).then(response => {
        context.commit("SET_DATA", ["stats", response.data]);
        context.commit("SET_TITLE", response.title);
      });
    },
    fetchSystem(context) {
      return this._vm.$api.get("retour/system").then(response => {
        context.commit("SET_PLUGIN", response);
      });
    },
    load(context) {
      context.dispatch("fetchFails");
      context.dispatch("fetchRedirects");
      context.dispatch("fetchStats");
      context.dispatch("fetchSystem");
      this._vm.$api.post("retour/limit");
    },
    offset(context, offset) {
      context.commit("SET_OFFSET", offset);
      context.dispatch("fetchStats");
    },
    stats(context, stats) {
      context.commit("SET_STATS", stats);
      context.dispatch("fetchStats");
    },
    table(context) {
      if (context.state.view.table === "redirects") {
        context.commit("SET_TABLE", "fails");
      } else {
        context.commit("SET_TABLE", "redirects");
      }
    }
  }
};
