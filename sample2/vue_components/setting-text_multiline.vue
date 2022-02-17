<template>
    <div :class="'settings-component settings-component-' + dataSettingsValue_type + ' settings-component-' + dataSettingsKey"
        v-show="dataSettingsDepends_onKey == '' || dataSettingsDepends_onVisible == true || $root.$refs[dataSettingsDepends_onKey].data.value == dataSettingsDepends_onValue">
        <div class="settings-component-title">{{ dataSettingsDisplay_name }}</div>
        <div class="settings-component-control">
            <textarea
                :name="name"
                :cols="dataSettingsCols"
                :rows="dataSettingsRows"
                v-model="data.value"
                :disabled="dataSettingsDepends_onKey != '' && $root.$refs[dataSettingsDepends_onKey].data.value != dataSettingsDepends_onValue"
                @change="emitChange"></textarea>
        </div>
    </div>
</template>

<script>
export default {
    props:
    [
        'name',
        'value',
        'data-settings-cols',
        'data-settings-rows',
        'data-settings-value_type',
        'data-settings-display_name',
        'data-settings-key',

        'data-settings-depends_on-key',
        'data-settings-depends_on-value',
        'data-settings-depends_on-visible'
    ],

    data()
    {
        return {
            data: {
                value: this.value,
                type: 'textarea'
            }
        }
    },

    created()
    {
        if (this.dataSettingsDepends_onKey != '')
            this.$root.$on(this.dataSettingsDepends_onKey, this.resetDefault);
    },

    methods:
    {
        resetDefault()
        {
            this.data.value = '';
        },

        emitChange()
        {
            this.$root.$emit(this.dataSettingsKey);
        }
    }
}
</script>
