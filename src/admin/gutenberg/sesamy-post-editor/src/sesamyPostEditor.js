import { ToggleControl, TextControl, SelectControl } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useEffect, useState, useMemo } from  '@wordpress/element';


function* getPaymentOptions(enableTiers){

  if(enableTiers){
    yield {label: 'Tier', value: 'tier'};
  }

  yield {label: 'Custom Price', value: 'custom'};
}

// Only load for enabled post types
export default () => {

  const currentPostType = useSelect(select => select('core/editor').getCurrentPostType()); 
  const [settings, setSettings] = useState(); 

    // Load settings
    useEffect(() => {

      fetch('/wp-json/sesamy/v1/settings')
      .then((response) => response.json())
      .then((data) => setSettings(data) );
  
    }, []);

  // Only show box for enabled post types
  return (settings && settings.content_types.includes(currentPostType))  ? (<SesamyPostEditor />) : null;

}

/**
 * The sesamy post editor
 * @returns 
 */
const SesamyPostEditor = () => {
  
  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta')); 
  const sesamyTiersTaxonomy = wp.data.select('core').getEntityRecords('taxonomy', 'sesamy_tiers');  
  const currentPost = useSelect(select => select('core/editor').getCurrentPost());
  const sesamy_tiers = useSelect(select => select('core/editor').getEditedPostAttribute('sesamy_tiers'));
  const selectedTier = sesamy_tiers && sesamy_tiers.length > 0 ? sesamy_tiers[0] : undefined;
  const [currencies, setCurrencies] = useState(); 
  

  const dispatch = useDispatch();

  const setMeta = (meta) => {
    dispatch('core/editor').editPost({ meta });
  }


  // Load currencies
  useEffect(() => {

    fetch('/wp-json/sesamy/v1/currencies')
    .then((response) => response.json())
    .then((data) => setCurrencies(Object.keys(data)) );

  }, []);

  const setTier = (value) => {
    dispatch( 'core' ).editEntityRecord( 'postType', currentPost.type, currentPost.id, { 'sesamy_tiers': [ value ] } );
  }

  // Enable tiers if there is at least one
  const enableTiers = useMemo(() => {

    if (!sesamyTiersTaxonomy){
      return;
    }
    
    return sesamyTiersTaxonomy.length > 0;
  }, [sesamyTiersTaxonomy]);

  // Populate payment types
  const paymentTypes = useMemo(() => {

    if(enableTiers === undefined) {
      return;
    }

    return Array.from(getPaymentOptions(enableTiers))
  }, [enableTiers]);


  // Default to first payment type if nothing is set or misconfigured (tier removed etc)
  useEffect(() => {

    if(!paymentTypes){
      return;
    }

    if(!meta['_sesamy_payment_type']){      
      setMeta({ '_sesamy_payment_type': paymentTypes[0].value })
    }

    // Notify user if configuration is invalid
    if(!paymentTypes.map(x => x.value).includes(meta['_sesamy_payment_type'])){
      console.log(paymentTypes);
      setMeta({ '_sesamy_payment_type': paymentTypes[0].value })
      wp.data.dispatch( 'core/notices' ).createNotice(
        'error',
        'Sesamy: The configured tier is no longer available. Defaulting to custom pricing.',
        { id: 'sesamy-tier-config', isDismissible: true }
      );
    }


    


  }, ['_sesamy_payment_type', paymentTypes])

  useEffect(() => {
   

    if(!meta || meta['_sesamy_locked'] !== true){
      return;
    }

    switch (meta['_sesamy_payment_type']) {
        case 'tier':

          if(!selectedTier && sesamyTiersTaxonomy && sesamyTiersTaxonomy.length > 0){
            setTier(sesamyTiersTaxonomy[0].id);
          }        

          break;
        case 'custom':

          if( !meta['_sesamy_currency'] && currencies && currencies.length > 0){
            setMeta({ '_sesamy_currency': currencies[0] });
          }

          
          break;
    }


  }, [selectedTier, sesamyTiersTaxonomy, meta['_sesamy_payment_type'], meta['_sesamy_locked'], currencies ]);


  

console.log(meta, paymentTypes, enableTiers);
  
  return (
    <PluginDocumentSettingPanel
      name="sesamy-post-editor"
      title={__('Sesamy', 'sesamy')}
    >

      <ToggleControl
        checked={meta['_sesamy_locked']}
        label={__('Locked', 'sesamy')}
        onChange={(value) => setMeta({ '_sesamy_locked': value }) }
      />


      {meta['_sesamy_locked'] && <>

      <SelectControl
          label="Payment Type"
          value={ meta['_sesamy_payment_type'] }
          options={ paymentTypes }
          onChange={(value) => {
            setMeta({ '_sesamy_payment_type': value })
          }}
          __nextHasNoMarginBottom
        />


      {enableTiers && meta['_sesamy_payment_type']  == 'tier' &&  

      <SelectControl
          label="Selected Tier"
          value={ selectedTier }
          options={ sesamyTiersTaxonomy && sesamyTiersTaxonomy.map(x => ({ label: x.name, value: x.id})) }
          onChange={(value) => setTier(value) }
          __nextHasNoMarginBottom
        />


        
      }

        {meta['_sesamy_payment_type'] == "custom" && <>

          <TextControl
            label="Price"
            value={meta['_sesamy_price']}
            placeholder={""}
            onChange={(value) => {
              setMeta({ '_sesamy_price': value })
            }}
          />

          <SelectControl
            label="Currency"
            value={meta['_sesamy_currency']}
            options={currencies && currencies.map(x => ({label: x, value: x}))}
            onChange={(value) => setMeta({ '_sesamy_currency': value })}
            __nextHasNoMarginBottom
          />

      </>}

      </>
      }


    </PluginDocumentSettingPanel>
  );

}