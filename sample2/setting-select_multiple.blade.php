<setting-select_multiple
    name="{{ $setting['name'] }}"
    :value="{{ json_encode($setting['value']) }}"
    :data-settings-meta-options="{{ json_encode($setting['meta']['options']) }}"
    data-settings-value_type="{{ $setting['value_type'] }}"
    data-settings-display_name="{{ $setting['display_name'] }}"
    data-settings-depends_on-key="{{ isset($setting['depends_on']) ? 'settings.' . $setting['depends_on']['key'] : '' }}"
    data-settings-depends_on-value="{{ isset($setting['depends_on']) ? $setting['depends_on']['value'] : '' }}"
    data-settings-depends_on-visible="{{ isset($setting['depends_on']) ? $setting['depends_on']['visible'] : '' }}"
    data-settings-key="{{ 'settings.' . $setting['key'] }}"
    ref="{{ 'settings.' . $setting['key'] }}"></setting-select_multiple>