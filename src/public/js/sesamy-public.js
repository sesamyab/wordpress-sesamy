(function ($) {
  "use strict";

  $("[data-sesamy-paywall]").each(function () {
    if (!sesamy) {
      console.error("Sesamy JavaScript Api not available.");
      return;
    }

    var data = $(this).data();

    if (!data.sesamyItemSrc) {
      console.warn("Attribute data-sesamy-item-src not set.");
      return;
    }

    sesamy
      .getEntitlement(data.sesamyItemSrc, data.sesamyPasses?.split(",") ?? [])
      .then((entitlement) => {
        if (entitlement !== undefined) {
          $(this).hide();
        }
      });
  });
})(jQuery);
