import { ToggleControl, TextControl, SelectControl } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default () => {

  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta')); 
  const sesamyTiersTaxonomy = wp.data.select('core').getEntityRecords('taxonomy', 'sesamy_tiers');  
  const currentPost = useSelect(select => select('core/editor').getCurrentPost());
  const sesamy_tiers = useSelect(select => select('core/editor').getEditedPostAttribute('sesamy_tiers'));
  const selectedTier = sesamy_tiers && sesamy_tiers.length > 0 ? sesamy_tiers[0] : undefined;
  const dispatch = useDispatch();

  const setMeta = (meta) => {
    dispatch('core/editor').editPost({ meta });
  }

  const currencies = ['SEK', 'NOK', 'DKK'];

  const setTier = (value) => {
    dispatch( 'core' ).editEntityRecord( 'postType', currentPost.type, currentPost.id, { 'sesamy_tiers': [ value ] } );
  }

  // Ensure no inconsistencies (NOTE: useEffect cannot be used in gutenberg as regular React)
  if(!selectedTier && meta['_sesamy_payment_type'] == 'tier' && sesamyTiersTaxonomy && sesamyTiersTaxonomy.length > 0 && meta['_sesamy_locked'] == true){
    setTier(sesamyTiersTaxonomy[0].id);
  }

  return (
    <PluginDocumentSettingPanel
      name="sesamy-post-editor"
      title={__('Sesamy', 'sesamy')}
    >

      <ToggleControl
        checked={meta['_sesamy_locked']}
        label={__('Locked', 'sesamy')}
        onChange={(value) => {
          if (sesamy_tiers && sesamy_tiers.length > 0 != selectedTier){

          }
          setMeta({ '_sesamy_locked': value });
        }
        }
      />


      {meta['_sesamy_locked'] && <>

      <SelectControl
          label="Payment Type"
          value={ meta['_sesamy_payment_type'] }
          options={ [{label: 'Tier', value: 'tier'}, {label: 'Custom Price', value: 'custom'}] }
          onChange={(value) => {
            setMeta({ '_sesamy_payment_type': value })
          }}
          __nextHasNoMarginBottom
        />


      {meta['_sesamy_payment_type']  == 'tier' &&  

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
            options={currencies.map(x => ({label: x, value: x}))}
            onChange={(value) => setMeta({ '_sesamy_currency': value })}
            __nextHasNoMarginBottom
          />

      </>}

      </>
      }


    </PluginDocumentSettingPanel>
  );

}