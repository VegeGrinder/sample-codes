<template>
    <div :class="'settings-component no-gutters settings-component-' + dataSettingsValue_type + ' settings-component-' + dataSettingsKey"
        v-show="dataSettingsDepends_onKey == '' || dataSettingsDepends_onVisible == true || $root.$refs[dataSettingsDepends_onKey].data.value == dataSettingsDepends_onValue">
        <div class="settings-component-title col-6">{{ dataSettingsDisplay_name }}</div>
        <div class="settings-component-control col-6">
            <input type="number"
                :name="name"
                v-model="data.value"
                :step="dataSettingsStep"
                :disabled="dataSettingsDepends_onKey != '' && $root.$refs[dataSettingsDepends_onKey].data.value != dataSettingsDepends_onValue"
                @change="emitChange"
            >
        </div>
    </div>
</template>

<script>
export default {
    props:
    [
        'name',
        'value',
        'data-settings-step',
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
                type: 'number',
            }
        }
    },

    created()
    {
        if (this.dataSettingsDepends_on_key != '')
            this.$root.$on(this.dataSettingsDepends_onKey, this.resetDefault);
    },

    methods:
    {
        resetDefault()
        {
            this.data.value = 0;
        },

        emitChange()
        {
            this.$root.$emit(this.dataSettingsKey);
        }
    }
}
</script>
