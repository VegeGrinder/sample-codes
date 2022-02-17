<template>
    <div :class="'settings-component no-gutters settings-component-' + dataSettingsValue_type + ' settings-component-' + dataSettingsKey"
        v-show="dataSettingsDepends_onKey == '' || dataSettingsDepends_onVisible == true || $root.$refs[dataSettingsDepends_onKey].data.value == dataSettingsDepends_onValue">
        <div class="settings-component-title col-6">{{ dataSettingsDisplay_name }}</div>
        <div class="settings-component-control col-6">
            <select :name="name + '[]'" v-model="valueKeys" multiple="true" style="display: none">
                <option :value="option.key" v-for="(option, index) in data.options" :key="index"></option>
            </select>
            <multiselect
                v-model="data.value"
                :options="data.options"
                label="label"
                track-by="key"
                :multiple="true"
                :close-on-select="false"></multiselect>
        </div>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect';

export default {
    components: {
        Multiselect
    },

    props:
    [
        'name',
        'value',
        'data-settings-meta-options',
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
                value: [],
                type: 'select',
                options: []
            }
        }
    },

    created()
    {
        if (this.dataSettingsDepends_onKey != '')
            this.$root.$on(this.dataSettingsDepends_onKey, this.resetDefault);

        this.processOptions();
    },

    methods:
    {
        processOptions()
        {
            let optionsArray = Object.entries(this.dataSettingsMetaOptions);

            for (let i = 0; i < optionsArray.length; i++)
            {
                this.data.options.push({
                    key: optionsArray[i][0],
                    label: optionsArray[i][1]
                });

                if (this.value.includes(optionsArray[i][0]))
                {
                    this.data.value.push({
                        key: optionsArray[i][0],
                        label: optionsArray[i][1]
                    });
                }
            }
        },

        resetDefault()
        {
            this.data.value = [];
        },

        emitChange()
        {
            this.$root.$emit(this.dataSettingsKey);
        }
    },

    computed:
    {
        valueKeys: function()
        {
            return this.data.value.map(element => element.key);
        }
    }
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
