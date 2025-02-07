Array.from(document.querySelectorAll("[data-sesamy-paywall]")).forEach(
  function (item) {
    if (!sesamy) {
      console.error("Sesamy JavaScript Api not available.");
      return;
    }

    var itemSrc = item.dataset.sesamyItemSrc || "";
    var content = sesamy.content.get(item);
    var passes = content?.pass?.split(";") || [];

    sesamy.getEntitlement(itemSrc, passes).then((entitlement) => {
      if (entitlement !== undefined) {
        item.style.display = "none";
      }
    });
  }
);
