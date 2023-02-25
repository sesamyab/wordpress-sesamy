Array.from( document.querySelectorAll( '[data-sesamy-paywall]' ) ).forEach(
	function(item){

		if ( ! sesamy) {
			console.error( 'Sesamy JavaScript Api not available.' );
			return;
		}

		var data = item.dataset;

		if ( ! data.sesamyItemSrc) {
			console.warn( 'Attribute data-sesamy-item-src not set.' );
			return;
		}

		sesamy.getEntitlement( data.sesamyItemSrc, data.sesamyPasses ? .split( ',' ) ? ? [] ).then(
			entitlement => {
            if (entitlement !== undefined) {
                item.style.display = 'none';
            }
			}
		)

	}
);
