(function ($, Drupal, once) {
    'use strict';

    Drupal.behaviors.DX8Menus = {
        // Attachment trigger.
        attach: function (context, settings) {
            // Cache
            var menuItemLinks = [];

            // Counters
            var menuItemLinkCounter;

            // Libs
            var cmm = new Drupal.CohesionResponsiveBreakpoints();

            // States
            var over = false;
            var $lastAnimatedSubmenu = $();
            var hasEntered = false;

            // Constants
            var onceMenuItem = 'js-coh-menu-item-init';
            var onceMenuItemLink = 'js-coh-menu-item-link-init';
            var nameSpace = '.coh-menu-item-link';

            // Constants - classes
            var cls = {
                isCollapsed: 'is-collapsed',
                isExpanded: 'is-expanded',
                both: 'is-collapsed is-expanded',
                hasChildren: 'has-children',
                menuListContainer: 'coh-menu-list-container',
                menuListItem: 'coh-menu-list-item',
                menuListLink: 'js-coh-menu-item-link'
            };

            // Constants - aria attributes
            var aria = {
                expanded: 'aria-expanded',
                popup: 'aria-haspopup'
            };

            // Events
            var onEnter = ['pointerenter'];
            var onLeave = ['mouseleave'];
            var onClick = ['click'];
            var onFocus = ['focus'];
            var onFocusOut = ['focusout'];
            var onKeyDown = ['keydown'];

            /**
             * Call back for initializing the Drupal menu item link functionality
             * @param {Object} settings
             * @returns {undefined}
             */
            function initDrupalMenuItemLink(settings) {
                var bOnClick = true;

                settings = settings.cohesion;
                var $a = settings.settings.$a; // Can be either `<a>`, `<button>` or <span>
                var $li = settings.settings.$li;
                var isParent = settings.settings.isParent;
                var thisNameSpace = nameSpace + $a[0].nodeName;

                // Ensure all previous bound events are removed (by nameSpace)
                $li.off(thisNameSpace);
                $a.off(thisNameSpace);

                // Grab the current breakpoint setting
                var setting = settings.settings.breakpoints[settings.key];

                // If animation target exists, store for later use
                if (typeof setting.animationTarget !== 'undefined') {
                    $a.data('currentAnimationTarget', $(setting.animationTarget, $li));
                }

                // If animation target is stored, remove inline display property
                if ($a.data('currentAnimationTarget')) {
                    $($a.data('currentAnimationTarget')).css('display','');
                }

                var toggleSiblings = setting.link_interaction === 'toggle-on-click-hide-siblings' || setting.button_interaction === 'toggle-on-click-hide-siblings';

                if ((setting.link_interaction === 'toggle-on-hover' || toggleSiblings || setting.link_interaction === 'toggle-on-click' || setting.button_interaction === 'toggle-on-click' || settings.button_interaction === 'toggle-parent-on-click') && $li.hasClass(cls.hasChildren) ) {

                    $a.attr(aria.popup, true);

                } else {
                    $a.removeAttr(aria.popup);
                    $a.removeAttr(aria.expanded);
                }

                // Hover events
                if (setting.link_interaction === 'toggle-on-hover') {
                    over = false;

                    if ($li.hasClass(cls.hasChildren)) {
                        $li.on(onLeave.join(thisNameSpace + ' ') + thisNameSpace, function (event) {
                            // hotfix/COH-4793 - prevent leave event being able to fire before enter event to stop menu show/hide getting inverted on page load.
                            if (!hasEntered) {
                                return;
                            }
                            window.setTimeout(function () {
                                over = false;
                            }, 200);

                            // Disable click events for links and buttons.
                            event.preventDefault();

                            // If the menu is closed when leaving ensure it doesnt get reopened
                            if($li.hasClass(cls.isCollapsed))   {
                                return;
                            }

                            toggleSubMenu($li, $a, setting, true, event);
                        });

                        $li.on(onEnter.join(thisNameSpace + ' ') + thisNameSpace, function (event) {
                            // Once the menu is open, make the link clickable - delay so it doesn't happen immediately on touch device.
                            window.setTimeout(function () {
                                over = true;
                            }, 200);

                            // Disable click events for links and buttons.
                            event.preventDefault();
                            // Ensure that the mouseEnter event always fires after a mouseLeave event when mousing between menu elements.
                            setTimeout(function () {
                                toggleSubMenu($li, $a, setting);
                                hasEntered = true;
                            }, 1);

                        });

                        $a.on(onClick.join(thisNameSpace + ' ') + thisNameSpace, function (event) {
                            if (!over) {
                                event.preventDefault();
                            }
                        });

                    }
                    bOnClick = false;
                }

                // Click events
                if (bOnClick) {
                    $a.on(onClick.join(thisNameSpace + ' ') + thisNameSpace, function (e) {

                        // Click through to link || return to parent
                        if (setting.link_interaction === 'click-through-to-link') {
                            return;
                        }

                        // No interaction
                        if (setting.link_interaction === 'no-interaction') {
                            e.preventDefault();
                            return;
                        }

                        // If has children - do something with them
                        if ($li.hasClass(cls.hasChildren)) {

                            e.preventDefault();

                            // On click, toggle sub-menu visibility and hide sibling items. If no sub-menu, go to link
                            toggleSubMenu($li, $(this), setting, toggleSiblings);

                            if(setting.link_interaction === 'toggle-parent-on-click') {
                                return;
                            }
                        }
                    });
                }

                // Focus events
                $a.on(onFocus.join(thisNameSpace + ' ') + thisNameSpace, function (e) {
                    over = true;

                    // Ensure the pointer is always up to date on focus - probably a little overkill, but safer than sorry
                    for (var i = 0; i < menuItemLinks.length; i++) {
                        if ($(this).is(menuItemLinks[i]['$a'])) {
                            menuItemLinkCounter = i;
                            break;
                        }
                    }

                    $a.on(onKeyDown.join(thisNameSpace + ' ') + thisNameSpace, function (e) {

                        // Handle accessibility keys
                        switch (e.key) {

                            case ' ':
                            case 'Spacebar': // IE
                                e.preventDefault();
                                e.stopPropagation();

                                // If menu list item does not have child menu, space follows link
                                if (!$li.hasClass(cls.hasChildren)) {
                                    window.location = $a.attr('href');
                                }

                                // If menu list item has child menu, space toggles child menu
                                if ($li.hasClass(cls.hasChildren)) {
                                    toggleSubMenu($li, $a, setting, toggleSiblings);
                                }

                                break;

                            case 'ArrowDown':
                            case 'ArrowRight':
                            case 'Down': // IE
                            case 'Right': // IE
                                e.preventDefault();
                                e.stopPropagation();

                                // Handle parent menu items (when collapsed you should be able to navigate left / right)
                                if (isParent && (e.key === 'ArrowRight' || e.key === 'Right') && $li.next('.' + cls.menuListItem) && !$li.hasClass(cls.isExpanded)) {
                                    $('.' + cls.menuListLink, $li.next('.' + cls.menuListItem)).eq(0).focus();
                                    break;
                                }

                                // If expanded then move into child items
                                if ($li.hasClass(cls.isExpanded) && $li.hasClass(cls.hasChildren)) {
                                    focusNextMenuItem($li);
                                }

                                // If !expanded then do it
                                if (!$li.hasClass(cls.isExpanded) && $li.hasClass(cls.hasChildren)) {
                                    toggleSubMenu($li, $a, setting, toggleSiblings);
                                }

                                // If !children move onto the next sibling
                                if (!$li.hasClass(cls.hasChildren)) {
                                    focusNextMenuItem($li);
                                }

                                break;

                            case 'Escape':
                            case 'Esc': // IE
                                e.preventDefault();
                                e.stopPropagation();

                                if ($li.parent().closest('.' + cls.menuListItem)) {

                                    toggleSubMenu($li.parent().closest('.' + cls.menuListItem), $li.parent().closest('.' + cls.menuListItem).children('.' + cls.menuListLink), setting, toggleSiblings);

                                    $li.parent().closest('.' + cls.menuListItem).children('.' + cls.menuListLink).focus();
                                }

                                break;

                            case 'ArrowUp':
                            case 'ArrowLeft':
                            case 'Up': // IE
                            case 'Left': // IE
                                e.preventDefault();
                                e.stopPropagation();

                                // Handle parent menu items (when collapsed you should be able to navigate left / right)
                                if (isParent && (e.key === 'ArrowLeft' || e.key === 'Left') && $li.prev('.' + cls.menuListItem) && !$li.hasClass(cls.isExpanded)) {
                                    $('.' + cls.menuListLink, $li.prev('.' + cls.menuListItem)).eq(0).focus();
                                    break;
                                }

                                // If !children || isCollapsed then move back to the previous link
                                if (!$li.hasClass(cls.hasChildren) || $li.hasClass(cls.isCollapsed)) {
                                    focusPreviousMenuItem();
                                }

                                // If !collapsed then do it, toggle siblings and move back to the previous link
                                if (!$li.hasClass(cls.isCollapsed) && $li.hasClass(cls.hasChildren)) {
                                    toggleSubMenu($li, $a, setting, toggleSiblings);
                                }

                                break;

                            case 'Enter':
                                e.preventDefault();
                                e.stopPropagation();
                                // Enter should always follow the link, expanding the menu can be handled with down or space.
                                // Just use the click event to follow the link.
                                $a[0].click();

                                break;

                            default:
                                return;
                        }


                    });
                });

                $a.on(onFocusOut.join(thisNameSpace + ' ') + thisNameSpace, function (e) {
                    // Remove the previous keyboard events
                    $a.off(onKeyDown.join(thisNameSpace + ' ') + thisNameSpace);
                    over = false;
                });
            }

            /**
             *
             * @param {type} settings
             * @returns {undefined}
             */
            function initDrupalMenuItem(settings) {
                settings = settings.cohesion;
                var $a = settings.settings.$a; // Can be either `<a>`, `<button>` or <span>
                var $li = settings.settings.$li;
                var bInteracted = false;
                var $interactees = $a.add($a.siblings('a, button, span')); // used to ensure sibling A and BUTTON elements get same aria values.

                // Grab the current breakpoint setting
                var setting = settings.settings.breakpoints[settings.key];

                if (typeof $li.data('interacted') !== 'undefined' && $li.data('interacted') === true) {
                    bInteracted = true;
                }

                if (!bInteracted) {
                    $li.toggleClass(cls.isCollapsed, setting === 'hidden' || (setting === 'trail' && !$li.hasClass('in-active-trail')));
                    $li.toggleClass(cls.isExpanded, setting === 'visible' || (setting === 'trail' && $li.hasClass('in-active-trail')));
                    if ($li.hasClass(cls.hasChildren)) {

                        $interactees.each(function () {
                            var $interactee = $(this);

                            if ($interactee.attr(aria.popup) === "true") {
                                if ($li.hasClass(cls.isCollapsed)) {
                                    $interactee.attr(aria.expanded, false);
                                }
                                if ($li.hasClass(cls.isExpanded)) {
                                    $interactee.attr(aria.expanded, true);
                                }
                            }
                        });
                    }
                }
            }

            function toggleSiblingsFn($li, $a, setting) {
                var $siblings = $li.siblings('li.has-children');
                $siblings.children('a, button, span').each(function() {
                    var $this = $(this);
                    if($this.attr(aria.expanded) === "true") {
                        toggleSubMenu($this.parent('li'), $this, setting, false);
                    }
                });
            }

            function toggleSubMenu($li, $a, setting, toggleSiblings) {
                var $interactees = $a.add($a.siblings('a, button, span'));
                var $submenu;

                if (setting.animationTarget && setting.animationType) {
                    // hardcoded general sibling selector for animation target, as sub menu will always be a sibling of parent menu's link
                    $submenu = $('~' + setting.animationTarget, $a);

                    if(setting.button_interaction === 'toggle-parent-on-click')  {
                        $submenu = $('> ' + setting.animationTarget, $li);
                        $interactees = $interactees.add($('> a, > button, > span', $li));
                    }

                    var animationOriginArray;

                    if (setting.animationOrigin) {
                        // convert animation origin string to array
                        animationOriginArray = setting.animationOrigin.split(',');
                    }

                    if ((setting.link_interaction !== 'toggle-on-click-hide-siblings' || setting.button_interaction !== 'toggle-on-click-hide-siblings') && $lastAnimatedSubmenu.length && !$lastAnimatedSubmenu.is($submenu)) {
                        $lastAnimatedSubmenu.stop(true, true);
                    }

                    $submenu.stop(true, true).toggle({
                        effect: setting.animationType,
                        direction: setting.animationDirection,
                        distance: setting.animationDistance,
                        percent: setting.animationScale,
                        origin: animationOriginArray,
                        size: setting.animationFoldHeight,
                        horizFirst: setting.animationHorizontalFirst,
                        times: setting.animationIterations,
                        easing: setting.animationEasing,
                        duration: setting.animationDuration
                    });
                    $lastAnimatedSubmenu = $submenu;
                }
                $li.toggleClass(cls.both);

                $interactees.each(function () {
                    var $interactee = $(this);
                    if ($interactee.attr(aria.popup) === "true") {
                        if ($li.hasClass(cls.isCollapsed)) {
                            $interactee.attr(aria.expanded, false);
                        }
                        if ($li.hasClass(cls.isExpanded)) {
                            $interactee.attr(aria.expanded, true);
                        }
                    }
                });

                $li.data('interacted', true);

                // Call the call back if defined
                if (toggleSiblings) {
                    toggleSiblingsFn($li, $a, setting);
                }
            }

            function focusPreviousMenuItem() {
                if (menuItemLinkCounter > 0) {
                    menuItemLinkCounter--;
                    menuItemLinks[menuItemLinkCounter]['$a'].focus();
                }
            }

            function focusNextMenuItem() {
                if (menuItemLinkCounter + 1 < menuItemLinks.length) {
                    menuItemLinkCounter++;
                    menuItemLinks[menuItemLinkCounter]['$a'].focus();
                }
            }

            function focusMenuItem() {
                menuItemLinks[menuItemLinkCounter]['$a'].focus();
            }

            // Each menu item link
            var menuItems = $(once(onceMenuItemLink, '.js-coh-menu-item-link, .js-coh-menu-item-button'));
            menuItems.each(function (i, e) {
                var $this = $(this),
                    $li = $(this).closest('.coh-menu-list-item'),
                    responsiveSettings = $this.data('cohSettings'),
                    key,
                    settings = {
                        $a: $this,
                        $li: $li,
                        isParent: !$li.parent().closest('.' + cls.menuListItem).length,
                        breakpoints: {}
                    };

                // Keep a list of the menu links
                menuItemLinks.push({
                    $a: $this,
                    $li: $li,
                    tabindex: $this.attr('tabindex') || i
                });

                // Ensure they are in the correct order
                if (i + 1 === menuItems.length) {

                    menuItemLinks.sort(function (a, b) {
                        return a.tabindex - b.tabindex;
                    });
                }

                for (var i = 0; i < cmm.breakpoints.length; i++) {

                    key = cmm.breakpoints[i].key;

                    // Populate all breakpoints regardless of whether the settings are set or not to simulate inheritance
                    settings.breakpoints[key] = {};
                    if (responsiveSettings && typeof responsiveSettings[key] !== 'undefined') {

                        settings.breakpoints[key] = responsiveSettings[key];

                        var previous = responsiveSettings[key];

                    } else {

                        if (typeof cmm.breakpoints[i - 1] !== 'undefined' && typeof previous !== 'undefined') {
                            settings.breakpoints[key] = previous;
                        }
                    }
                }

                cmm.addListeners(settings, initDrupalMenuItemLink);
            });

            // Each menu item
            $(once(onceMenuItem, '.js-coh-menu-item')).each(function () {

                var $this = $(this),
                    responsiveSettings = $this.data('cohSettings'),
                    key,
                    settings = {
                        $li: $this,
                        $a: $('> a, > button, > span', $this),
                        breakpoints: {}
                    };

                // No children or settings then just return
                if ($this.hasClass(cls.hasChildren)) {
                    for (var i = 0; i < cmm.breakpoints.length; i++) {

                        key = cmm.breakpoints[i].key;

                        // Populate all breakpoints regardless of whether the settings are set or not to simulate inheritance
                        settings.breakpoints[key] = {};
                        if (typeof responsiveSettings[key] !== 'undefined') {

                            settings.breakpoints[key] = responsiveSettings[key];

                            var previous = responsiveSettings[key];

                        } else {

                            if (typeof cmm.breakpoints[i - 1] !== 'undefined' && typeof previous !== 'undefined') {
                                settings.breakpoints[key] = previous;
                            }
                        }
                    }

                    cmm.addListeners(settings, initDrupalMenuItem);
                }
            });
        }
    };

})(jQuery, Drupal, once);
