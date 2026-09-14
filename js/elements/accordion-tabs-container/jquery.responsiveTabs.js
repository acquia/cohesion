/**
 * @sitestudioexcludesonar
 * *************************************************
 * ************ WARNING WARNING WARNING ************
 * *************************************************
 * This code has been modified from the original
 * there are several pull requests which should
 * include these modifications
 * See ...
 * *************************************************
 */

(function ($, once, window, undefined) {

  /** Default settings */
  var defaults = {
    active: null,
    event: 'click',
    disabled: [],
    collapsible: 'accordion',
    startCollapsed: false,
    rotate: false,
    setHash: false,
    animation: 'default',
    animationQueue: false,
    duration: 500,
    fluidHeight: true,
    scrollToAccordion: false,
    scrollToAccordionOnLoad: false,
    scrollToAccordionOffset: 0,
    accordionTabElement: '<div></div>',
    accordionTriggerHTML: '<a></a>',
    click: function () { },
    activate: function () { },
    activateStart: function () { },
    activateFinished: function () { },
    deactivate: function () { },
    load: function () { },
    activateState: function () { },
    classes: {
      stateDefault: 'r-tabs-state-default',
      stateActive: 'r-tabs-state-active',
      stateDisabled: 'r-tabs-state-disabled',
      stateExcluded: 'r-tabs-state-excluded',
      stateTypePrefix: 'r-tabs-state',
      container: 'r-tabs',
      ul: 'r-tabs-nav',
      tab: 'r-tabs-tab',
      anchor: 'r-tabs-anchor',
      panel: 'r-tabs-panel',
      accordionTitle: 'r-tabs-accordion-title'
    }
  };

  var events = [
    'tabs-click',
    'tabs-activate',
    'tabs-active-start',
    'tabs-activate-finished',
    'tabs-deactivate',
    'tabs-activate-state',
    'tabs-load',
    'tabs-refresh'
  ];

  /**
   * Responsive Tabs
   * @constructor
   * @param {object} element - The HTML element the validator should be bound to
   * @param {object} options - An option map
   */
  function ResponsiveTabs(element, options) {
    this.element = element; // Selected DOM element
    this.$element = $(element); // Selected jQuery element

    this.tabs = []; // Create tabs array
    this.panels = []; // Create panels array
    this.tabItems = []; // Create tabbed navigation items array
    this.tabItemAnchors = []; // Create tabbed naviation anchors array
    this.accordionItems = []; // Create accordion items array
    this.accordionItemAnchors = []; // Create accordion item anchor
    this.state = ''; // Define the plugin state (tabs/accordion)
    this.rotateInterval = 0; // Define rotate interval
    this.$queue = $({});

    // Extend the defaults with the passed options
    this.options = $.extend({}, defaults, options);

    this.init();
  }


  /**
   * This function initializes the tab plugin
   */
  ResponsiveTabs.prototype.init = function () {
    var _this = this;

    // Load all the elements
    this.tabs = this._loadElements();
    this._loadClasses();
    this._loadEvents();
    this._loadAria();

    // Window resize bind to check state
    $(window).on('resize', function (e) {
      _this._setState(e);
      if (_this.options.fluidHeight !== true) {
        _this._equaliseHeights();
      }
    });

    // Hashchange event
    $(window).on('hashchange', function (e) {
      var tabRef = _this._getTabRefBySelector(window.location.hash);
      var oTab = _this._getTab(tabRef);

      // Check if a tab is found that matches the hash
      if (tabRef >= 0 && !oTab._ignoreHashChange && !oTab.disabled) {
        // If so, open the tab and auto close the current one
        _this._openTab(e, _this._getTab(tabRef), true);
      }
    });

    // Start rotate event if rotate option is defined
    if (this.options.rotate !== false) {
      this.startRotation();
    }

    // Set fluid height
    if (this.options.fluidHeight !== true) {
      _this._equaliseHeights();
    }

    // --------------------
    // Define plugin events
    //

    // Activate: this event is called when a tab is selected
    this.$element.bind('tabs-click', function (e, oTab) {
      _this.options.click.call(this, e, oTab);
    });

    // Activate: this event is called when a tab is selected
    this.$element.bind('tabs-activate', function (e, oTab) {
      _this.options.activate.call(this, e, oTab);
    });
    // Activate start: this event is called when a tab is selected and before the animation has completed
    this.$element.bind('tabs-activate-start', function (e, oTab) {
      _this.options.activateFinished.call(this, e, oTab);
    });
    // Activate finished: this event is called when a tab is selected and the animation has completed
    this.$element.bind('tabs-activate-finished', function (e, oTab) {
      _this.options.activateFinished.call(this, e, oTab);
    });
    // Deactivate: this event is called when a tab is closed
    this.$element.bind('tabs-deactivate', function (e, oTab) {
      _this.options.deactivate.call(this, e, oTab);
    });
    // Activate State: this event is called when the plugin switches states
    this.$element.bind('tabs-activate-state', function (e, state) {
      _this.options.activateState.call(this, e, state);
    });

    // Load: this event is called when the plugin has been loaded
    this.$element.bind('tabs-load', function (e) {
      e.stopPropagation();
      var startTab;

      if(_this.options.startCollapsed === 'true') {
          _this.options.startCollapsed = true;
      } else if (_this.options.startCollapsed === 'false') {
          _this.options.startCollapsed = false;
      }

      _this._setState(e); // Set state

      // Check if the panel should be collapsed on load
      if ((_this.options.startCollapsed !== true) && !(_this.options.startCollapsed === 'accordion' && _this.state === 'accordion')) {

        startTab = _this._getStartTab();

        // disable animation on initial page load
        var cacheAnimationType = _this.options.animation;
        _this.options.animation = 'default';

        // Open the initial tab
        _this._openTab(e, startTab); // Open first tab

        // restore animation after initial page load
        _this.options.animation = cacheAnimationType;

        // Call the callback function
        _this.options.load.call(this, e, startTab); // Call the load callback
      }
    });
    // Trigger loaded event
    this.$element.trigger('tabs-load', _this);
  };

  //
  // PRIVATE FUNCTIONS
  //

  /**
   * This function loads the tab elements and stores them in an array
   * @returns {Array} Array of tab elements
   */
  ResponsiveTabs.prototype._loadElements = function () {
    var _this = this;
    var $ul = this.$element.children('ul:first');
    var tabs = [];
    var id = 0;

    // Add the classes to the basic html elements
    this.$element.addClass(_this.options.classes.container); // Tab container
    $ul.addClass(_this.options.classes.ul); // List container

    var wrapper = $('.coh-accordion-tabs-content-wrapper:first', this.$element);

    // use .not to ensure we dont select child tab items from any nested accordions
    var tabButtons = $('.' + _this.options.classes.accordionTitle, wrapper)
      .not(wrapper.find('.coh-accordion-tabs-content-wrapper .'+ _this.options.classes.accordionTitle));

    once('tab-init', $(tabButtons)).forEach(function (e, i) {

      var $accordionTab = $(e);

      var $anchor = $('a, button', $accordionTab);

      var isExcluded = $accordionTab.hasClass(_this.options.classes.stateExcluded);
      var $panel, panelSelector, $tab, $tabAnchor, tabSettings;

      // Check if the tab should be excluded
      if (!isExcluded) {

        panelSelector = $anchor.attr('href') || $anchor.data('href');
        $panel = $(panelSelector);
        $panel.hide();

        tabSettings = $accordionTab.data('cohTabSettings');

        $tab = $('<li />').appendTo($ul);

        $tab.addClass(tabSettings.customStyle);

        $tabAnchor = $('<a />', {
          href: panelSelector,
        }).html($anchor.html()).appendTo($tab);

        var oTab = {
          _ignoreHashChange: false,
          id: id,
          disabled: typeof tabSettings.disabled !== 'undefined' ? tabSettings.disabled : false,
          tab: $tab,
          anchor: $tabAnchor,
          panel: $panel,
          selector: panelSelector,
          accordionTab: $accordionTab,
          accordionAnchor: $anchor,
          active: false,
          linkUrl: typeof tabSettings.linkUrl !== 'undefined' ? tabSettings.linkUrl : false,
          linkTarget: typeof tabSettings.linkTarget !== 'undefined' ? tabSettings.linkTarget : false,
          hide: typeof tabSettings.hide !== 'undefined' ? tabSettings.hide : false
        };

        // 1up the ID
        id++;
        // Add to tab array
        tabs.push(oTab);

        // Add to panels array
        _this.panels.push(oTab.panel);

        // Add to tab items array
        _this.tabItems.push(oTab.tab);
        _this.tabItemAnchors.push(oTab.anchor);

        // Add to accordion items array
        _this.accordionItems.push(oTab.accordionTab);
        _this.accordionItemAnchors.push(oTab.accordionAnchor);
      }
    });

    return tabs;
  };

  /**
   * Load the initial aria attributes
   * @private
   */
  ResponsiveTabs.prototype._loadAria = function () {
    for (var i = 0; i < this.tabs.length; i++) {

      this.tabs[i].accordionAnchor.attr('aria-expanded', this.tabs[i].active);

      if (this.tabs[i].disabled) {
        this.tabs[i].accordionAnchor.attr('aria-disabled', this.tabs[i].disabled);
      }
    }
  };

  /**
   * Update the state of the aria attributes
   * @param tab | obj - the tab object
   * @private
   */
  ResponsiveTabs.prototype._updateAria = function (tab) {

    tab.accordionAnchor.attr('aria-expanded', tab.active);
    tab.accordionAnchor.removeAttr('aria-disabled');

    if (tab.disabled || (!this.options.collapsible && tab.active)) {
      tab.accordionAnchor.attr('aria-disabled', true);
    }

    // Update aria-selected on tab anchor when in tab mode.
    if (this.state === 'tabs' && tab.linkUrl === false) {
      tab.anchor.attr('aria-selected', tab.active ? 'true' : 'false');
    }
  };

  /**
   * This function adds classes to the tab elements based on the options
   */
  ResponsiveTabs.prototype._loadClasses = function () {
    for (var i = 0; i < this.tabs.length; i++) { // Change this to a $.each with once
      this.tabs[i].tab.addClass(this.options.classes.stateDefault).addClass(this.options.classes.tab);
      this.tabs[i].anchor.addClass(this.options.classes.anchor);
      this.tabs[i].panel.addClass(this.options.classes.stateDefault).addClass(this.options.classes.panel);
      this.tabs[i].accordionTab.addClass(this.options.classes.accordionTitle);
      this.tabs[i].accordionAnchor.addClass(this.options.classes.anchor);
      if (this.tabs[i].disabled) {
        this.tabs[i].tab.removeClass(this.options.classes.stateDefault).addClass(this.options.classes.stateDisabled);
        this.tabs[i].accordionTab.removeClass(this.options.classes.stateDefault).addClass(this.options.classes.stateDisabled);
      }
    }
  };

  /**
   * This function adds events to the tab elements
   */
  ResponsiveTabs.prototype._loadEvents = function () {
    var _this = this;

    // Define activate event on a tab element
    var fActivate = function (e) {
      var current = _this._getCurrentTab(); // Fetch current tab
      var activatedTab = e.data.tab;

      e.preventDefault();

      // If the tab is a link
      if (activatedTab.linkUrl !== false) {
        window.open(activatedTab.linkUrl, activatedTab.linkTarget);
        return;
      }

      // Trigger click event for whenever a tab is clicked/touched even if the tab is disabled
      activatedTab.tab.trigger('tabs-click', activatedTab);

      // Make sure this tab isn't disabled
      if (!(activatedTab.disabled || activatedTab.linkUrl)) {

        // Check if hash has to be set in the URL location
        if (_this.options.setHash) {
          // Set the hash using the history api if available to tackle Chromes repaint bug on hash change
          if (history.pushState) {
            // Fix for missing window.location.origin in IE
            if (!window.location.origin) {
              window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port : '');
            }

            history.pushState(null, null, window.location.origin + window.location.pathname + window.location.search + activatedTab.selector);
          } else {
            // Otherwise fallback to the hash update for sites that don't support the history api
            window.location.hash = activatedTab.selector;
          }
        }

        e.data.tab._ignoreHashChange = true;

        // Check if the activated tab isnt the current one or if its collapsible. If not, do nothing
        if (current !== activatedTab || _this._isCollapisble()) {
          // The activated tab is either another tab of the current one. If it's the current tab it is collapsible
          // Either way, the current tab can be closed
          _this._closeTab(e, current);

          // Check if the activated tab isnt the current one or if it isnt collapsible
          if (current !== activatedTab || !_this._isCollapisble()) {
            _this._openTab(e, activatedTab, false, true);
          }
        }
      }
    };

    // Loop tabs
    for (var i = 0; i < this.tabs.length; i++) {
      // Add activate function to the tab and accordion selection element
      $(once('loadEvent', this.tabs[i].anchor.get(0))).on(_this.options.event, { tab: _this.tabs[i] }, fActivate);
      $(once('loadEvent', this.tabs[i].accordionAnchor.get(0))).on(_this.options.event, { tab: _this.tabs[i] }, fActivate);
    }
  };

  /**
   * This function gets the tab that should be opened at start
   * @returns {Object} Tab object
   */
  ResponsiveTabs.prototype._getStartTab = function () {
    var tabRef = this._getTabRefBySelector(window.location.hash);
    var startTab;

    // Check if the page has a hash set that is linked to a tab
    if (tabRef >= 0 && !this._getTab(tabRef).disabled) {
      // If so, set the current tab to the linked tab
      startTab = this._getTab(tabRef);
    } else if (this.options.active > 0 && !this._getTab(this.options.active).disabled) {
      startTab = this._getTab(this.options.active);
    } else {
      // If not, just get the first one
      startTab = this._getTab(0);
    }

    return startTab;
  };

  /**
   * This function sets the current state of the plugin
   * @param {Event} e - The event that triggers the state change
   */
  ResponsiveTabs.prototype._setState = function (e) {
    var $ul = $('ul:first', this.$element);
    var oldState = this.state;
    var startCollapsedIsState = (typeof this.options.startCollapsed === 'string');
    var startTab;

    var visible = $ul.is(':visible');

    // The state is based on the visibility of the tabs list
    if (visible) {
      // Tab list is visible, so the state is 'tabs'
      this.state = 'tabs';
    } else {
      // Tab list is invisible, so the state is 'accordion'
      this.state = 'accordion';
    }

    // If the new state is different from the old state
    if (this.state !== oldState) {

      // Add the state class to the Container
      this.$element.toggleClass(this.options.classes.stateTypePrefix + '-' + 'tabs', visible);
      this.$element.toggleClass(this.options.classes.stateTypePrefix + '-' + 'accordion', !visible);

      // If so, the state activate trigger must be called
      this.$element.trigger('tabs-activate-state', { oldState: oldState, newState: this.state, tabs: this });

      // Check if the state switch should open a tab
      if (oldState && startCollapsedIsState && this.options.startCollapsed !== this.state && this._getCurrentTab() === undefined) {
        // Get initial tab
        startTab = this._getStartTab(e);
        // Open the initial tab
        this._openTab(e, startTab); // Open first tab
      }

      // Apply or remove role attributes based on mode.
      if (this.state === 'tabs') {
        this._applyTabRoles();
      } else {
        this._removeTabRoles();
      }
    }
  };

  /**
   * This function opens a tab
   * @param {Event} e - The event that triggers the tab opening
   * @param {Object} oTab - The tab object that should be opened
   * @param {Boolean} closeCurrent - Defines if the current tab should be closed
   * @param {Boolean} stopRotation - Defines if the tab rotation loop should be stopped
   */
  ResponsiveTabs.prototype._openTab = function (e, oTab, closeCurrent, stopRotation) {
    var _this = this;
    var scrollOffset;

    // If there is no tab (generally when tabs are empty) just return
    if (typeof oTab === 'undefined') {
      return;
    }

    // Check if the current tab has to be closed
    if (closeCurrent) {
      this._closeTab(e, this._getCurrentTab());
    }

    // Check if the rotation has to be stopped when activated
    if (stopRotation && this.rotateInterval > 0) {
      this.stopRotation();
    }

    // Set this tab to active
    oTab.active = true;
    // Set active classes to the tab button and accordion tab button
    oTab.tab.removeClass(_this.options.classes.stateDefault).addClass(_this.options.classes.stateActive);
    oTab.accordionTab.removeClass(_this.options.classes.stateDefault).addClass(_this.options.classes.stateActive);
    // Run panel transition
    _this._doTransition(oTab, _this.options.animation, 'open', function () {
      // Determine if we should scroll:
      // 1. On page load (tabs-load): only if hash matches THIS accordion's tab
      // 2. On user interaction: always scroll (if enabled)
      var hashMatchesThisTab = window.location.hash && oTab.selector === window.location.hash;
      var isPageLoad = e.type === 'tabs-load';
      var shouldScrollOnLoad = isPageLoad ? (_this.options.scrollToAccordionOnLoad && hashMatchesThisTab) : true;

      // When finished, set active class to the panel
      oTab.panel.removeClass(_this.options.classes.stateDefault).addClass(_this.options.classes.stateActive);

      // Update the aria
      _this._updateAria(oTab);

      // Scroll if enabled, in correct state (accordion or tabs), not in view, and should scroll
      var isCorrectState = (_this.getState() === 'accordion' || _this.getState() === 'tabs');

      // For tabs mode, scroll to the container; for accordion mode, scroll to the specific accordion tab
      var scrollTarget = _this.getState() === 'tabs' ? _this.$element : oTab.accordionTab;

      var shouldScroll = isCorrectState &&
                        _this.options.scrollToAccordion &&
                        (!_this._isInView(scrollTarget) || _this.options.animation !== 'default') &&
                        shouldScrollOnLoad;

      if (shouldScroll) {
        // Add offset element's height to scroll position
        scrollOffset = scrollTarget.offset().top - _this.options.scrollToAccordionOffset;

        // Use consistent delay for both accordion and tabs to ensure content is rendered
        var scrollDelay = 0;
        if (isPageLoad) {
          scrollDelay = 150;
        } else {
          // For user interactions (clicks), add small delay to ensure content is rendered
          scrollDelay = 0;
        }

        setTimeout(function() {
          // Check if the animation option is enabled, and if the duration isn't 0
          if (_this.options.animation !== 'default' && _this.options.duration > 0) {
            // If so, set scrollTop with animate and use the 'animation' duration
            $('html, body').animate({
              scrollTop: scrollOffset
            }, _this.options.duration);
          } else {
            //  If not, just set scrollTop
            $('html, body').animate({
              scrollTop: scrollOffset
            }, 1);
          }
        }, scrollDelay);
      }
    });

    this.$element.trigger('tabs-activate', oTab);
  };

  /**
   * This function closes a tab
   * @param {Event} e - The event that is triggered when a tab is closed
   * @param {Object} oTab - The tab object that should be closed
   */
  ResponsiveTabs.prototype._closeTab = function (e, oTab) {
    var _this = this;
    var doQueueOnState = typeof _this.options.animationQueue === 'string';
    var doQueue;

    if (oTab !== undefined) {
      if (doQueueOnState && _this.getState() === _this.options.animationQueue) {
        doQueue = true;
      } else if (doQueueOnState) {
        doQueue = false;
      } else {
        doQueue = _this.options.animationQueue;
      }

      // Deactivate tab
      oTab.active = false;
      // Set default class to the tab button
      oTab.tab.removeClass(_this.options.classes.stateActive).addClass(_this.options.classes.stateDefault);

      // Update the aria
      _this._updateAria(oTab);

      // Run panel transition
      _this._doTransition(oTab, _this.options.animation, 'close', function () {
        // Set default class to the accordion tab button and tab panel
        oTab.accordionTab.removeClass(_this.options.classes.stateActive).addClass(_this.options.classes.stateDefault);
        oTab.panel.removeClass(_this.options.classes.stateActive).addClass(_this.options.classes.stateDefault);
      }, !doQueue);

      this.$element.trigger('tabs-deactivate', oTab);
    }
  };

  /**
   * This function runs an effect on a panel
   * @param {Object} oTab - The object for the panel
   * @param {String} method - The transition method reference
   * @param {String} state - The state (open/closed) that the panel should transition to
   * @param {Function} callback - The callback function that is called after the transition
   * @param {Boolean} dequeue - Defines if the event queue should be dequeued after the transition
   */
  ResponsiveTabs.prototype._doTransition = function (oTab, method, state, callback, dequeue) {
    var effect;
    var _this = this;
    var duration = _this.options.duration;

    // Get effect based on method
    switch (method) {
      case 'slide':
        effect = (state === 'open') ? 'slideDown' : 'slideUp';
        duration = _this.options.duration;
        break;
      case 'fade':
        effect = (state === 'open') ? 'fadeIn' : 'fadeOut';
        duration = _this.options.duration;
        break;
      default:
        effect = (state === 'open') ? 'show' : 'hide';
        // When default is used, set the duration to 0
        //_this.options.duration = 0;
        duration = 0;
        break;
    }

    // Prevent new tab content stacking underneath current tab content by removing fade animation duration
    if (_this.options.animation === 'fade' && _this.state === 'tabs') {
      effect = state === 'open' ? effect : 'hide';
      duration = state === 'open' ? duration : 0;
      oTab.panel.css('opacity', '');
    }
    // Run the transition on the panel
    oTab.panel[effect]({
      duration: duration,
      queue: 'responsive-tabs-' + state,
      complete: function () {
        // Call the callback function
        callback.call(oTab.panel, method, state);
        _this.$element.trigger('tabs-activate-finished', oTab);
      }
    }).dequeue('responsive-tabs-' + state);
  };

  /**
   * This function returns the collapsibility of the tab in this state
   * @returns {Boolean} The collapsibility of the tab
   */
  ResponsiveTabs.prototype._isCollapisble = function () {
    return (typeof this.options.collapsible === 'boolean' && this.options.collapsible) || (typeof this.options.collapsible === 'string' && this.options.collapsible === this.getState());
  };

  /**
   * This function returns a tab by numeric reference
   * @param {Integer} numRef - Numeric tab reference
   * @returns {Object} Tab object
   */
  ResponsiveTabs.prototype._getTab = function (numRef) {
    return this.tabs[numRef];
  };

  /**
   * This function returns the numeric tab reference based on a hash selector
   * @param {String} selector - Hash selector
   * @returns {Integer} Numeric tab reference
   */
  ResponsiveTabs.prototype._getTabRefBySelector = function (selector) {
    // Loop all tabs
    for (var i = 0; i < this.tabs.length; i++) {
      // Check if the hash selector is equal to the tab selector
      if (this.tabs[i].selector === selector) {
        return i;
      }
    }
    // If none is found return a negative index
    return -1;
  };

  /**
   * This function returns the current tab element
   * @returns {Object} Current tab element
   */
  ResponsiveTabs.prototype._getCurrentTab = function () {
    return this._getTab(this._getCurrentTabRef());
  };

  /**
   * This function returns the next tab's numeric reference
   * @param {Integer} currentTabRef - Current numeric tab reference
   * @returns {Integer} Numeric tab reference
   */
  ResponsiveTabs.prototype._getNextTabRef = function (currentTabRef) {
    var tabRef = (currentTabRef || this._getCurrentTabRef());
    var nextTabRef = (tabRef === this.tabs.length - 1) ? 0 : tabRef + 1;
    return (this._getTab(nextTabRef).disabled) ? this._getNextTabRef(nextTabRef) : nextTabRef;
  };

  /**
   * This function returns the previous tab's numeric reference
   * @returns {Integer} Numeric tab reference
   */
  ResponsiveTabs.prototype._getPreviousTabRef = function () {
    return (this._getCurrentTabRef() === 0) ? this.tabs.length - 1 : this._getCurrentTabRef() - 1;
  };

  /**
   * This function returns the current tab's numeric reference
   * @returns {Integer} Numeric tab reference
   */
  ResponsiveTabs.prototype._getCurrentTabRef = function () {
    // Loop all tabs
    for (var i = 0; i < this.tabs.length; i++) {
      // If this tab is active, return it
      if (this.tabs[i].active) {
        return i;
      }
    }
    // No tabs have been found, return negative index
    return -1;
  };

  /**
   * This function gets the tallest tab and applied the height to all tabs
   */
  ResponsiveTabs.prototype._equaliseHeights = function () {
    var maxHeight = 0;

    $.each($.map(this.tabs, function (tab) {
      maxHeight = Math.max(maxHeight, tab.panel.css('minHeight', '').height());
      return tab.panel;
    }), function () {
      this.css('minHeight', maxHeight);
    });
  };

  //
  // HELPER FUNCTIONS
  //

  ResponsiveTabs.prototype._isInView = function ($element) {
    var docViewTop = $(window).scrollTop(),
      docViewBottom = docViewTop + $(window).height(),
      elemTop = $element.offset().top,
      elemBottom = elemTop + $element.height();
    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
  };

  //
  // PUBLIC FUNCTIONS
  //


  /**
   * This function returns the current tab's numeric reference
   * @returns {Integer} Numeric tab reference
   */
  ResponsiveTabs.prototype.getCurrentTab = function () {
    return this._getCurrentTabRef();
  };

  /**
   * This function returns the previous tab's numeric reference
   * @returns {Integer} Numeric tab reference
   */
  ResponsiveTabs.prototype.getPreviousTab = function () {
    return this._getPreviousTabRef();
  };

  /**
   * This function activates a tab
   * @param {Integer} tabRef - Numeric tab reference
   * @param {Boolean} stopRotation - Defines if the tab rotation should stop after activation
   */
  ResponsiveTabs.prototype.activate = function (tabRef, stopRotation) {
    var e = jQuery.Event('tabs-activate');
    var oTab = this._getTab(tabRef);
    if (!oTab.disabled) {
      this._openTab(e, oTab, true, stopRotation || true);
    }
  };

  /**
   * This function deactivates a tab
   * @param {Integer} tabRef - Numeric tab reference
   */
  ResponsiveTabs.prototype.deactivate = function (tabRef) {
    var e = jQuery.Event('tabs-dectivate');
    var oTab = this._getTab(tabRef);
    if (!oTab.disabled) {
      this._closeTab(e, oTab);
    }
  };

  /**
   * This function enables a tab
   * @param {Integer} tabRef - Numeric tab reference
   */
  ResponsiveTabs.prototype.enable = function (tabRef) {
    var oTab = this._getTab(tabRef);
    if (oTab) {
      oTab.disabled = false;
      oTab.tab.addClass(this.options.classes.stateDefault).removeClass(this.options.classes.stateDisabled);
      oTab.accordionTab.addClass(this.options.classes.stateDefault).removeClass(this.options.classes.stateDisabled);
      if (this.state === 'tabs') {
        oTab.anchor.removeAttr('aria-disabled');
      }
    }
  };

  /**
   * This function disable a tab
   * @param {Integer} tabRef - Numeric tab reference
   */
  ResponsiveTabs.prototype.disable = function (tabRef) {
    var oTab = this._getTab(tabRef);
    if (oTab) {
      oTab.disabled = true;
      oTab.tab.removeClass(this.options.classes.stateDefault).addClass(this.options.classes.stateDisabled);
      oTab.accordionTab.removeClass(this.options.classes.stateDefault).addClass(this.options.classes.stateDisabled);
      if (this.state === 'tabs') {
        oTab.anchor.attr('aria-disabled', 'true');
      }
    }
  };

  /**
   * This function gets the current state of the plugin
   * @returns {String} State of the plugin
   */
  ResponsiveTabs.prototype.getState = function () {
    return this.state;
  };

  /**
   * This function starts the rotation of the tabs
   * @param {Integer} speed - The speed of the rotation
   */
  ResponsiveTabs.prototype.startRotation = function (speed) {
    var _this = this;
    // Make sure not all tabs are disabled
    if (this.tabs.length > this.options.disabled.length) {
      this.rotateInterval = setInterval(function () {
        var e = jQuery.Event('rotate');
        _this._openTab(e, _this._getTab(_this._getNextTabRef()), true);
      }, speed || ((typeof (_this.options.rotate) === 'number') ? _this.options.rotate : 4000));
    } else {
      throw new Error("Rotation is not possible if all tabs are disabled");
    }
  };

  /**
   * This function stops the rotation of the tabs
   */
  ResponsiveTabs.prototype.stopRotation = function () {
    window.clearInterval(this.rotateInterval);
    this.rotateInterval = 0;
  };

  /**
   * This function can be used to get/set options
   * @return {any} Option value
   */
  ResponsiveTabs.prototype.option = function (key, value) {
    if (typeof value !== 'undefined') {
      this.options[key] = value;
    }
    return this.options[key];
  };

  /**
   * This function refreshes current list of tabs - particularly useful for adding tabs with AJAX
   * @returns {undefined}
   */
  ResponsiveTabs.prototype.refresh = function () {

    this.tabs = this.tabs.concat(this._loadElements());

    this._loadClasses();
    this._loadEvents();

    // Set fluid height
    if (this.options.fluidHeight !== true) {
      this._equaliseHeights();
    }

    this.$element.trigger('tabs-refresh', this);
    this._setState();

    // Re-apply roles for any newly added tabs.
    if (this.state === 'tabs') {
      this._applyTabRoles();
    }

    return this;
  };

  /**
   * This function adds specific role attributes when in tab mode.
   * @returns {undefined}
   */
  ResponsiveTabs.prototype._applyTabRoles = function () {
    var hasTabItems = false;

    // Add tab & tabpanels attributes only for items that activate panels.
    for (var i = 0; i < this.tabs.length; i++) {
      var tab = this.tabs[i];
      if (tab.linkUrl === false) {
        hasTabItems = true;
        var panelId = tab.panel.attr('id');
        var tabId = 'tab-' + panelId;
        tab.tab.attr('role', 'presentation');
        tab.anchor.attr('role', 'tab');
        tab.anchor.attr('id', tabId);
        tab.anchor.attr('aria-selected', tab.active ? 'true' : 'false');
        tab.anchor.attr('aria-controls', panelId);
        if (tab.disabled) {
          tab.anchor.attr('aria-disabled', 'true');
        } else {
          tab.anchor.removeAttr('aria-disabled');
        }
        tab.panel.attr('role', 'tabpanel');
        tab.panel.attr('aria-labelledby', tabId);
      } else {
        tab.tab.attr('role', 'presentation');
        tab.anchor.removeAttr('role id aria-selected aria-controls aria-disabled');
        tab.panel.removeAttr('role aria-labelledby');
      }
    }

    // Only add tablist role when the list contains tab items.
    if (hasTabItems) {
      this.$element.children('ul:first').attr('role', 'tablist');
    } else {
      this.$element.children('ul:first').removeAttr('role');
    }
  };

  /**
   * This function removes role attributes when not in tab mode.
   * @returns {undefined}
   */
  ResponsiveTabs.prototype._removeTabRoles = function () {
    this.$element.children('ul:first').removeAttr('role');

    for (var i = 0; i < this.tabs.length; i++) {
      this.tabs[i].tab.removeAttr('role');
      this.tabs[i].anchor.removeAttr('role id aria-selected aria-controls aria-disabled');
      this.tabs[i].panel.removeAttr('role aria-labelledby');
    }
  };

  /** jQuery wrapper */
  $.fn.responsiveTabs = function (options) {
    var args = arguments;
    var instance;

    var classes = [
      'stateActive',
      'stateDisabled',
      'stateExcluded'
    ];

    if (options === undefined || typeof options === 'object') {
      return this.each(function () {

        // If responsiveTabs doesn't exist init
        if (!$.data(this, 'responsivetabs')) {
          $.data(this, 'responsivetabs', new ResponsiveTabs(this, options));
        } else {
          // Otherwise just update the settings
          $.extend($.data(this, 'responsivetabs').options, options);
        }
      });
    } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
      instance = $.data(this[0], 'responsivetabs');

      // Allow instances to be destroyed via the 'destroy' method
      if (options === 'destroy') {
        // TODO: destroy instance classes, etc

        // Clean up the tabs etc
        if (typeof instance !== 'undefined') {
          for (var i = 0; i < instance.tabs.length; i++) {
            $.each($([instance.tabs[i].accordionTab, instance.tabs[i].panel, instance.tabs[i].tab]), function () {
              var $this = $(this);
              $this.removeAttr('style');
              $this.removeClass(instance.options.classes.stateActive);
              $this.removeClass(instance.options.classes.stateDisabled);
              $this.removeClass(instance.options.classes.stateExcluded);
            });
          }

          // Remove any bound event handlers on the container
          for (var i = 0; i < events.length; i++) {
            instance.$element.unbind(events[i]);
          }

          // Loop tabs to remove any 'event' bindings
          for (var i = 0; i < instance.tabs.length; i++) {
            // Add activate function to the tab and accordion selection element
            instance.tabs[i].anchor.off(instance.options.event);
            instance.tabs[i].accordionAnchor.off(instance.options.event);
          }

          // Remove data from the DOM element
          $.removeData(this[0], 'responsivetabs');
        }
      }

      if (instance instanceof ResponsiveTabs && typeof instance[options] === 'function') {
        return instance[options].apply(instance, Array.prototype.slice.call(args, 1));
      } else {
        return this;
      }
    }
  };

}(jQuery, once, window));
