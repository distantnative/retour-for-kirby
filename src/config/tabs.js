
export default (Vue) => {
  let tabs    = [];
  const store = Vue.$store.state.retour;

  // routes
  const routes = store.data.routes.length;

  tabs.push({
    name: "routes",
    label: Vue.$t("retour.routes"),
    icon: "undo",
    badge: routes ? {
      count: routes,
      color: "focus"
    }: false
  });

  // failures
  if (store.system.hasLog) {
    const failures = store.data.failures.length;

    if (failures > 1000) {
      failures = (Math.floor(failures / 100) / 10) + "k";
    }

    tabs.push({
      name: "failures",
      label: Vue.$t("retour.failures"),
      icon: "alert",
      badge: failures ? {
        count: failures,
        color: "negative"
      } : false
    });
  }

  // system
  tabs.push({
    name: "system",
    label: Vue.$t("retour.system"),
    icon: "box",
    badge: store.system.update < 0 ? {
      count: 1,
      color: "positive"
    } : false
  });

  return tabs;
}
