<div class="settings-group settings-group-{{ $settings['key'] }}">
    <div class="settings-group-title">
        {{ $settings['display_name'] }}
    </div>
    @foreach ($settings['components'] as $settingComponent)
        {{ $settingComponent }}
    @endforeach
</div>
