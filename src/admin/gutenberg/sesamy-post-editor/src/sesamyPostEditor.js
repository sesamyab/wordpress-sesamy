import { ToggleControl, __experimentalNumberControl  as NumberControl, SelectControl, CheckboxControl } from '@wordpress/components';
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
  const sesamyTiersTaxonomy = wp.data.select('core').getEntityRecords('taxonomy', 'sesamy_passes');  
  const currentPost = useSelect(select => select('core/editor').getCurrentPost());
  const sesamy_passes = useSelect(select => select('core/editor').getEditedPostAttribute('sesamy_passes'));
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


  const setTier = (tier, included) => {


    var newPasses = [];

    if(included && !sesamy_passes.includes(tier.id)){
      newPasses = [...sesamy_passes, tier.id];
    }else if(!included){
      newPasses = [...sesamy_passes.filter(id => id !== tier.id)]
    }



    dispatch( 'core' ).editEntityRecord( 'postType', currentPost.type, currentPost.id, { 'sesamy_passes': newPasses } );
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


  useEffect(() => {

    if (!meta || meta['_sesamy_locked'] !== true){
      return;
    }


    // Set first currency as default
    if( !meta['_sesamy_currency'] && currencies && currencies.length > 0){
      setMeta({ '_sesamy_currency': currencies[0] });
    }

  }, [meta['_sesamy_locked'], currencies ]);

  
  
  return (
    <PluginDocumentSettingPanel
      name="sesamy-post-editor"
      title={__('Sesamy', 'sesamy')}
    >

      <ToggleControl
        checked={meta['_sesamy_locked']}
        label={__('Enable paywall', 'sesamy')}
        onChange={(value) => setMeta({ '_sesamy_locked': value }) }
      />

   
      


      {meta['_sesamy_locked'] && <>

     
      <h3>Single purchase</h3>
      <ToggleControl
          checked={meta['_sesamy_enable_single_purchase']}
          label={__('Enable single-purchase', 'sesamy')}
          onChange={(value) => setMeta({ '_sesamy_enable_single_purchase': value }) }
        />

     {meta['_sesamy_enable_single_purchase'] && <>


     <NumberControl
              label="Price"
              value={parseFloat(meta['_sesamy_price'])}
              min={0}   
              step={'0.01'}
              onChange={(value) => {
                setMeta({ '_sesamy_price': value })
              }}
            />

            <SelectControl
              label="Currency"
              value={meta['_sesamy_currency']}
              min={1}
              step={'0.01'}
              options={currencies && currencies.map(x => ({label: x, value: x}))}
              onChange={(value) => setMeta({ '_sesamy_currency': value })}
              __nextHasNoMarginBottom
            />

    </>}

          {enableTiers &&  <>


              <h3>Sesamy Passes</h3>
             

            {sesamyTiersTaxonomy.map(tier => {
              
              const isChecked = sesamy_passes && sesamy_passes.includes(tier.id);

              return <CheckboxControl
                label={tier.name}
                checked={ isChecked }
                onChange={(checked) => setTier(tier, checked) }
                __nextHasNoMarginBottom
              />

            })}
            
          </>}



      </>
      }


    </PluginDocumentSettingPanel>
  );

}