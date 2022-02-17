<template>
    <div :class="'settings-component settings-component-' + dataSettingsValue_type + ' settings-component-' + dataSettingsKey + ' rows-' + dataSettingsRows"
        v-show="dataSettingsDepends_onKey == '' || dataSettingsDepends_onVisible == true || $root.$refs[dataSettingsDepends_onKey].data.value == dataSettingsDepends_onValue">
        <div class="settings-component-title">{{ dataSettingsDisplay_name }}</div>
        <div class="settings-component-control">
            <input type="hidden" :name="name" :value="data.value">
            <ckeditor :editor="editor" :config="editorConfig" v-model="data.value"></ckeditor>
        </div>
    </div>
</template>

<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props:
    [
        'name',
        'value',
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
            editor: ClassicEditor,
            data: {
                value: this.value
            },
            editorConfig: {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'insertTable', '|', 'undo', 'redo' ],
                table: {
                    toolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
                }
            },
        };
    }
}
</script>
