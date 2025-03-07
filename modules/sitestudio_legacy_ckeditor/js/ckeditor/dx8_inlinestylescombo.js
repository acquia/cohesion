(function ($, Drupal, CKEDITOR) {
    'use strict';

    CKEDITOR.plugins.add('dx8_inlinestylescombo', {
        requires: 'dx8_richcombo',

        init: function (editor) {
            this.groups = [];

            var config = editor.config,
                styles = {},
                stylesListDX8Inline = [],
                combo,
                allowedContent = [];

            editor.on('stylesSet', function (evt) {
                var stylesDefinitions = config.stylesSetInlineDX8;

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

                        // Let the sort fall naturally rather than by CKEditor's styleType grouping.
                        style._.weight = i;

                        styles[styleName] = style;
                        stylesListDX8Inline.push(style);
                        allowedContent.push(style);
                    }
                }

                // Sorts the Array, so the styles get grouped by type in proper order (#9029).
                stylesListDX8Inline.sort(function (styleA, styleB) {
                    return styleA._.weight - styleB._.weight;
                });
            });

            editor.ui.addDx8RichCombo('DX8InlineStyles', {
                label: 'Inline styles',
                title: 'Inline styles',
                toolbar: 'styles,10',
                allowedContent: allowedContent,

                panel: {
                    css: [CKEDITOR.skin.getPath('editor')].concat(config.contentsCssDX8Inline),
                    multiSelect: true,
                    attributes: {'aria-label': 'Inline styles'},
                    className: 'cke_combopanel dx8_inline_styles_combo_panel'
                },

                init: function () {
                    var lastDisplayGroup = '';

                    // Sort the individual styles by name.
                    stylesListDX8Inline.sort(function(a, b) {
                        return a._name.localeCompare(b._name);
                    });

                    // Now sort by group name as we have to add headings sequentially.
                    stylesListDX8Inline.sort(function(a, b) {
                        return a._.definition.displayGroup.localeCompare(b._.definition.displayGroup);
                    });

                    // Add all styles to the combo.
                    for (var i = 0, count = stylesListDX8Inline.length; i < count; i++) {
                        var style = stylesListDX8Inline[i],
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
                        selectedText = editor.getSelection().getSelectedText(),
                        elementPath = editor.elementPath();
                
                    // You should be able to apply inline styles to a block as well
                    // Only do this though when no text has been selected
                    if(selectedText.length <= 0 && style.element === 'span') {
                        var i = findIndexFromArrayOfObjects(config.stylesSetInlineDX8, 'name', value);
                        
                        var styleDef = config.stylesSetInlineDX8[i];
                            styleDef.element = elementPath.block.getName();
                            style = new CKEDITOR.style(styleDef);
                    };
                    
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
                    for (var name in styles) {
                        var style = styles[name],
                            type = style._.type;

                        if (style.checkApplicable(elementPath, editor, editor.activeFilter)) {
                            counter[type]++;
                        }
                        else {
                            this.hideItem(name);
                            this.hideGroup(style._.definition.displayGroup);
                        }

                        if (style.checkActive(elementPath, editor))
                            this.mark(name);
                    }
                },

                refresh: function () {
                    var elementPath = editor.elementPath();

                    if (!elementPath)
                        return;

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
                    stylesListDX8Inline = [];
                }
            });
            
            // Helper to get the index of an array objects
            function findIndexFromArrayOfObjects(arr, prop, val)    {
                for (var i = 0; i < arr.length; i++) { 
                    if(arr[i][prop] === val)    {
                        return i;
                    }
                };
                return -1;
            }
        }
    });
})(jQuery, Drupal, CKEDITOR);
