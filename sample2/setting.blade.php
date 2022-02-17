@include(
    implode('.', ['common', 'settings', 'setting-' . $setting['value_type']]),
    $setting
)
