
export default (Vue) => {
  let tabs    = [];
  const store = Vue.$store.state.retour;

  // manual routes
  const routes = store.data.manual.length;

  tabs.push({
    name: "routes",
    label: Vue.$t("retour.routes"),
    icon: "undo",
    badge: routes ? {
      count: routes,
      color: "focus"
    }: false
  });

  // tracked routes
  const tracked = store.data.tracked.filter(route => route.active === false).length;

  tabs.push({
    name: "tracked",
    label: "Tracked",
    icon: "live",
    badge: tracked ? {
      count: tracked,
      color: "yellow"
    } : false
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
