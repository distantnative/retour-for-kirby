
/**
 * @param {object} view object with from and to keys that hold dayjs objects 
 * @returns {string}
 */
const getUnit = ({ from, to }) => {
  // full units
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

  // custom ranges
  const diff = to.diff(from, "day");
  
  if (diff > 50) {
    return "months"
  }

  return "days";
};

export default (Vue) => ({
  namespaced: true,
  state: {
    data: {
      redirects: [],
      fails: [],
      stats: []
    },
    view: {
      from: Vue.$library.dayjs().startOf("month"),
      to:   Vue.$library.dayjs().endOf("month")
    },
    meta: {
      deleteAfter: null,
      hasLog: null,
      headers: [],
      first: null,
      last:  null
    }
  },
  getters: {
    isAll: (state) => {
      const first = Vue.$library.dayjs(state.meta.first);
      const last  = Vue.$library.dayjs(state.meta.last);
      return state.view.from.isSame(first, "day") && 
             state.view.to.isSame(last, "day");
    },
    hasPrev: (state, getters) => {
      return state.view.from.isAfter(Vue.$library.dayjs(state.meta.first), getters.unit)
    },
    hasNext: (state, getters) => {
      return state.view.to.isBefore(Vue.$library.dayjs(state.meta.last), getters.unit)
    },
    unit: (state) => getUnit(state.view)
  },
  mutations: {
    SET_DATA(state, data) {
      Vue.$set(state, "data", data);
    },
    SET_META(state, data) {
      Vue.$set(state, "meta", data);
    },
    SET_VIEW(state, view) {
      Vue.$set(state, "view", view);
    }
  },
  actions: {
    init(context) {
      context.dispatch("meta");
      context.dispatch("data");
    },
    async meta(context) {
      const data = await Vue.$api.get("retour/meta");
      context.commit("SET_META", data);
    },
    async data(context, view = context.state.view) {
      const payload = {
        from: view.from.format("YYYY-MM-DD"),
        to:   view.to.format("YYYY-MM-DD"),
        unit: getUnit(view)
      };
      const data = await Vue.$api.get("retour/data", payload);
      context.commit("SET_DATA", data);
    },
    async view(context, view) {
      // Making sure these are dayjs objects
      view = {
        from: Vue.$library.dayjs(view.from),
        to: Vue.$library.dayjs(view.to)
      };

      await context.dispatch("data", view);
      context.commit("SET_VIEW", view);
    }
  }
})