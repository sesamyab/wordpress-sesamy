import { ToggleControl, __experimentalNumberControl as NumberControl, SelectControl, CheckboxControl, __experimentalInputControl as InputControl } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useEffect, useState, useMemo } from '@wordpress/element';


function* getPaymentOptions(enableTiers) {

  if (enableTiers) {
    yield { label: 'Tier', value: 'tier' };
  }

  yield { label: 'Custom Price', value: 'custom' };
}

// Only load for enabled post types
export default () => {

  const currentPostType = useSelect(select => select('core/editor').getCurrentPostType());
  const [settings, setSettings] = useState();

  // Load settings
  useEffect(() => {

    fetch( sesamy_block_obj.home + '/wp-json/sesamy/v1/settings' )
      .then((response) => response.json())
      .then((data) => setSettings(data));

  }, []);

  // Only show box for enabled post types
  return (settings && settings.content_types.includes(currentPostType)) ? (<SesamyPostEditor />) : null;

}

/**
 * The sesamy post editor
 *
 * @returns 
 */
const SesamyPostEditor = () => {

  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'));
  const sesamyTiersTaxonomy = wp.data.select('core').getEntityRecords('taxonomy', 'sesamy_passes');
  const currentPost = useSelect(select => select('core/editor').getCurrentPost());
  const sesamy_passes = useSelect(select => select('core/editor').getEditedPostAttribute('sesamy_passes'));

  const dispatch = useDispatch();

  const setMeta = (meta) => {
    dispatch('core/editor').editPost({ meta });
  }


  const setTier = (tier, included) => {


    var newPasses = [];

    if (included && !sesamy_passes.includes(tier.id)) {
      newPasses = [...sesamy_passes, tier.id];
    } else if (!included) {
      newPasses = [...sesamy_passes.filter(id => id !== tier.id)]
    }



    dispatch('core').editEntityRecord('postType', currentPost.type, currentPost.id, { 'sesamy_passes': newPasses });
  }

  // Enable tiers if there is at least one
  const enableTiers = useMemo(() => {

    if (!sesamyTiersTaxonomy) {
      return;
    }

    return sesamyTiersTaxonomy.length > 0;
  }, [sesamyTiersTaxonomy]);

  // Populate payment types
  const paymentTypes = useMemo(() => {

    if (enableTiers === undefined) {
      return;
    }

    return Array.from(getPaymentOptions(enableTiers))
  }, [enableTiers]);

  // Helper function to get date and time in ISO format for input with datetime-local
  const getDateTimeISO = (date) => {

    if(!date){
      return undefined;
    }
    
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  };

  const datetimeLocalToUnixTimestampUTC = (localDateTimeString) => {

    if (!localDateTimeString) {
      return undefined;
    }

    const localDate = new Date(localDateTimeString);
    return Math.floor(localDate.getTime() / 1000);
  }

  const unixTimestampUTCToLocalDatetime = (unixTimestampUTC) => {

    if (!unixTimestampUTC || unixTimestampUTC < 0) {
      return undefined;
    }

    return new Date(unixTimestampUTC * 1000);
  }


  const minLockedFrom = new Date();
  const minLockedUntil = meta['_sesamy_locked_from'] && unixTimestampUTCToLocalDatetime(meta['_sesamy_locked_from']) > new Date() ? unixTimestampUTCToLocalDatetime(meta['_sesamy_locked_from']) : undefined;

  return (
    <PluginDocumentSettingPanel
      className="sesamy-editor-panel"
      name="sesamy-post-editor"
      title={__('Sesamy', 'sesamy')}
    >

      <ToggleControl
        checked={meta['_sesamy_locked']}
        label={__('Locked now', 'sesamy')}
        onChange={(value) => setMeta({ '_sesamy_locked': value })}
      />

      {meta['_sesamy_locked'] === false && 
        <InputControl
          label={__('Locked from', 'sesamy')}
          value={meta['_sesamy_locked_from'] ? getDateTimeISO(unixTimestampUTCToLocalDatetime(meta['_sesamy_locked_from'])) : ''}
          type='datetime-local'
          min={getDateTimeISO(minLockedFrom)}
          max={minLockedUntil ? getDateTimeISO(minLockedUntil) : ''}
          onChange={value => setMeta({ '_sesamy_locked_from': value ? datetimeLocalToUnixTimestampUTC(value) : -1 }) }
        />
      }

      {(meta['_sesamy_locked'] === true || !isNaN(meta['_sesamy_locked_from']) && meta['_sesamy_locked_from']  > 0 ) &&
        <InputControl
          label={__('Locked until', 'sesamy')}
          value={ meta['_sesamy_locked_until'] ? getDateTimeISO(unixTimestampUTCToLocalDatetime(meta['_sesamy_locked_until'])) : ''}
          min={getDateTimeISO(minLockedFrom)}
          type='datetime-local'
          onChange={value => setMeta({ '_sesamy_locked_until': value ? datetimeLocalToUnixTimestampUTC(value) : -1 })}
        />
      }


      {(meta['_sesamy_locked'] === true || !isNaN(meta['_sesamy_locked_from']) && meta['_sesamy_locked_from'] > 0) && <>


        <h3>Single purchase</h3>
        <ToggleControl
          checked={meta['_sesamy_enable_single_purchase']}
          label={__('Enable single-purchase', 'sesamy')}
          onChange={(value) => setMeta({ '_sesamy_enable_single_purchase': value })}
        />

        {meta['_sesamy_enable_single_purchase'] && <>


          <NumberControl
            label={__('Price', 'sesamy')}
            value={parseFloat(meta['_sesamy_price'])}
            min={0}
            step={'0.01'}
            onChange={(value) => {
              setMeta({ '_sesamy_price': value })
            }}
          />

        </>}

        {!!enableTiers && <>


          <h3>Sesamy Passes</h3>


          {sesamyTiersTaxonomy.map(tier => {

            const isChecked = sesamy_passes && sesamy_passes.includes(tier.id);

            return <CheckboxControl
              label={tier.name}
              checked={isChecked}
              onChange={(checked) => setTier(tier, checked)}
              __nextHasNoMarginBottom
            />

          })}

        </>}



      </>
      }


    </PluginDocumentSettingPanel>
  );

}