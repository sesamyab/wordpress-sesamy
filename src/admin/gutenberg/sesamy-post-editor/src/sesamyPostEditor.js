import { ToggleControl, TextControl, SelectControl } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default () => {

  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta')); 
  const sesamyTiersTaxonomy = wp.data.select('core').getEntityRecords('taxonomy', 'sesamy_tiers');  
  const currentPost = useSelect(select => select('core/editor').getCurrentPost());
  const sesamy_tiers = useSelect(select => select('core/editor').getEditedPostAttribute('sesamy_tiers'));
  const selectedTier = sesamy_tiers && sesamy_tiers.length > 0 ? sesamy_tiers[0] : null;
  const dispatch = useDispatch();

  const setMeta = (meta) => {
    dispatch('core/editor').editPost({ meta });
  }

  const currencies = ['SEK', 'NOK', 'DKK'];

  return (
    <PluginDocumentSettingPanel
      name="sesamy-post-editor"
      title={__('Sesamy', 'sesamy')}
    >

      <ToggleControl
        checked={meta['_sesamy_locked']}
        label={__('Locked', 'sesamy')}
        onChange={(value) => setMeta({ '_sesamy_locked': value })}
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
          onChange={(value) => {

            dispatch( 'core' ).editEntityRecord( 'postType', currentPost.type, currentPost.id, { 'sesamy_tiers': [ value ] } );

          }}
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


        <h3>Price overrides</h3>

        <ToggleControl
                checked={meta['_sesamy_enable_price_overrides']}
                label={__('Enable price overrides', 'sesamy')}
                onChange={(value) => setMeta({ '_sesamy_enable_price_overrides': value })}
              />

            {meta['_sesamy_enable_price_overrides'] && <>

                

                {currencies && currencies.map(currency =>
                  <TextControl
                  label={`Price in ${currency}`}
                  value={meta['_sesamy_price_overrides'] && meta['_sesamy_price_overrides'][currency] ? meta['_sesamy_price_overrides'][currency] : ''}
                  placeholder={""}
                  onChange={(value) => {
                    // TODO: Implement
                    //setMeta({ 'meta['_sesamy_price_overrides'][currency]': value })
                  }}
                />

                )}

      </>}

      </>}

      </>
      }


    </PluginDocumentSettingPanel>
  );

}