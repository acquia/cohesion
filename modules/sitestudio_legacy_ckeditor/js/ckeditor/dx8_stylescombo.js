(function ($, Drupal, CKEDITOR) {
    'use strict';

    CKEDITOR.plugins.add('dx8_stylescombo', {
        requires: 'dx8_richcombo',

        init: function (editor) {
            this.groups = [];

            var config = editor.config,
                styles = {},
                stylesListDX8 = [],
                combo,
                allowedContent = [];

            editor.on('stylesSet', function (evt) {
                var stylesDefinitions = config.stylesSetDX8;

                if (!stylesDefinitions)
                    return;

                var style, styleName, styleType;

                // Put all styles into an Array.
                for (var i = 0,
                         count = stylesDefinitions.length; i < count; i++) {
                    var styleDefinition = stylesDefinitions[i];

                    if (editor.blockless && ( styleDefinition.element in CKEDITOR.dtd.$block ) ||
                        ( typeof styleDefinition.type == 'string' && !CKEDITOR.style.customHandlers[styleDefinition.type] )) {

                        continue;
                    }

                    styleName = styleDefinition.name;
                    style = new CKEDITOR.style(styleDefinition);

                    if (!editor.filter.customConfig || editor.filter.check(style)) {
                        style._name = styleName;
                        style._.enterMode = config.enterMode;
                        // Get the type (which will be used to assign style to one of 3 groups) from assignedTo if it's defined.
                        style._.type = styleType = style.assignedTo || style.type;

                        if(style.element.match(/[hH][1-6]/gm)) {
                            style._type = style.type = 3;
                        }

                        // Let the sort fall naturally rather than by CKEditor's styleType grouping.
                        style._.weight = i;

                        styles[styleName] = style;
                        stylesListDX8.push(style);
                        allowedContent.push(style);
                    }
                }

            });

            editor.ui.addDx8RichCombo('DX8Styles', {
                label: 'Element styles',
                title: 'Element styles',
                toolbar: 'styles,10',
                allowedContent: allowedContent,

                panel: {
                    css: [CKEDITOR.skin.getPath('editor')].concat(config.contentsCssDX8),
                    multiSelect: true,
                    attributes: {'aria-label': 'Element styles'},
                    className: 'cke_combopanel dx8_styles_combo_panel'
                },

                init: function () {
                    var lastDisplayGroup = '';

                    // Add all styles to the combo.
                    for (var i = 0, count = stylesListDX8.length; i < count; i++) {
                        var style = stylesListDX8[i],
                            styleName = style._name,
                            displayGroup = style._.definition.displayGroup;

                        // Add the header if we haven't already.
                        if (displayGroup !== lastDisplayGroup) {
                            this.startGroup(displayGroup);
                            lastDisplayGroup = displayGroup;
                        }

                        // We don't want a preview so plain everything.
                        this.add(styleName, styleName, styleName);
                    }

                    this.commit();
                },

                onClick: function (value) {
                    editor.focus();
                    editor.fire('saveSnapshot');

                    var style = styles[value],
                        elementPath = editor.elementPath();

                    // When more then one style from the same group is active ( which is not ok ),
                    // remove all other styles from this group and apply selected style.
                    if (style.group && style.removeStylesFromSameGroup(editor)) {
                        editor.applyStyle(style);
                    } else {
                        editor[style.checkActive(elementPath, editor) ? 'removeStyle' : 'applyStyle'](style);
                    }

                    editor.fire('saveSnapshot');
                },

                onRender: function () {
                    editor.on('selectionChange', function (ev) {
                        var currentValue = this.getValue(),
                            elementPath = ev.data.path,
                            elements = elementPath.elements;

                        // For each element into the elements path.
                        for (var i = 0, count = elements.length,
                                 element; i < count; i++) {
                            element = elements[i];

                            // Check if the element is removable by any of
                            // the styles.
                            for (var value in styles) {
                                if (styles[value].checkElementRemovable(element, true, editor)) {
                                    if (value != currentValue)
                                        this.setValue(value);
                                    return;
                                }
                            }
                        }

                        // If no styles match, just empty it.
                        this.setValue('');
                    }, this);
                },

                onOpen: function () {
                    var selection = editor.getSelection(),
                        element = selection.getSelectedElement(),
                        elementPath = editor.elementPath(element),
                        counter = [0, 0, 0, 0];

                    this.showAll();
                    this.unmarkAll();
                    // Get selected element tag name
                    let elementTagName = elementPath.lastElement.getName();

                    for (var name in styles) {
                        var style = styles[name],
                            type = style._.type;
                        // Allow Generic styles in all styles list
                        if (style.checkApplicable(elementPath, editor, editor.activeFilter) || (style._.definition.displayGroup === 'Generic')) {
                            counter[type]++;
                        }
                        else {
                            this.hideItem(name);
                            this.hideGroup(style._.definition.displayGroup);
                        }

                        // Set generic element tag on the fly based on active selected element
                        if ((style._.definition.displayGroup === 'Generic') && (typeof elementTagName !== 'undefined')){
                             style._.definition.element = elementTagName;
                        }

                        if (style.checkActive(elementPath, editor))
                            this.mark(name);
                    }
                },

                refresh: function () {
                    var elementPath = editor.elementPath();

                    if (!elementPath)
                        return;

                    // We need to build a list of header tags
                    // to apply custom styles since dx8 custom heading styles is
                    // generic to all headings. DX8 ONLY supports h1...h6
                    // for (var name in styles) {
                    //     let style = styles[name];
                    //     if (style._.definition.element[0] === 'h1'){
                    //         for(let i = 2; i < 7; i++){
                    //             let htag = "h"+i;
                    //             // Add heading tag if it doesn't already exist
                    //             if (style._.definition.element.indexOf(htag) === -1){
                    //                 style._.definition.element.push(htag);
                    //             }
                    //         }
                    //     }
                    // }
                    for (var name in styles) {
                        var style = styles[name];

                        if (style.checkApplicable(elementPath, editor, editor.activeFilter))
                            return;
                    }
                    this.setState(CKEDITOR.TRISTATE_DISABLED);
                },

                // Force a reload of the data
                reset: function () {
                    if (combo) {
                        delete combo._.panel;
                        delete combo._.list;
                        combo._.committed = 0;
                        combo._.items = {};
                        combo._.state = CKEDITOR.TRISTATE_OFF;
                    }
                    styles = {};
                    stylesListDX8 = [];
                }
            });
        }
    });
})(jQuery, Drupal, CKEDITOR);
