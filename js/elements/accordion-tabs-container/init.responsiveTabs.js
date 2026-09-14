(function ($, Drupal, once, drupalSettings) {
  "use strict";

  Drupal.behaviors.CohesionAccordionTabs = {
    attach: function (context) {

      var onceTabs = 'cohAccordionTabs';

      var cmm = new Drupal.CohesionResponsiveBreakpoints(drupalSettings.cohesion.responsive_grid_settings);

      var $at = $('.coh-accordion-tabs > .coh-accordion-tabs-inner', context);

      function matchHeights(elements, remove) {
        return $(elements).matchHeight({
          byRow: false,
          remove: remove
        });
      }

      /**
       * Callback when the tabs initially load
       * @param {type} e
       * @param {type} tabs
       * @returns {undefined}
       */
      function tabsLoad(e, tabs) {
        getTabSettings(tabs);
      }

      /**
       * Callback when the tabs have been manually refreshed - normally ajax
       * @param {type} e
       * @param {type} tabs
       * @returns {undefined}
       */
      function tabsRefresh(e, tabs) {

        var opts = tabs.options;

        // Match the heights of the content
        if (typeof opts.contentMatchHeight !== 'undefined') {
          matchHeights(tabs.tabs.panels, opts.contentMatchHeight === true ? false : true);
        }

        // Match the heights of the lid
        if (typeof opts.tabsMatchHeight !== 'undefined') {
          matchHeights(tabs.tabs.tabItemAnchors, opts.tabsMatchHeight === true ? false : true);
        }

        getTabSettings(tabs);
      }

      /**
       * Callback when the tabs change state
       * @param {type} e
       * @param {type} tabs
       * @returns {undefined}
       */
      function tabsStateChange(e, tabs) {

        var opts = tabs.tabs.options;

        // Match the heights of the content
        if (typeof opts.contentMatchHeight !== 'undefined') {
          matchHeights(tabs.tabs.panels, opts.contentMatchHeight === true ? false : true);
        }

        // Match the heights of the lid
        if (typeof opts.tabsMatchHeight !== 'undefined') {
          matchHeights(tabs.tabs.tabItemAnchors, opts.tabsMatchHeight === true ? false : true);
        }
      }

      /**
       * Callback when switching between tabs and will return the activated tab object
       * @param {type} e
       * @param {type} tab
       * @returns {undefined}
       */
      function tabsActivate(e, tab) {

        // Update Drupal behaviors
        for (var i = 0; i < tab.panel.length; i++) {
          Drupal.attachBehaviors(tab.panel[i]);
        }
      }

      /**
       * Callback function to update settings when a breakpoint changes
       * @param {type} settings
       * @returns {undefined}
       */
      function updateSettings(settings) {

        var key = settings.cohesion.key;
        settings = settings.cohesion.settings;

        settings.$element.responsiveTabs(settings.breakpoints[key]);

        // Update the settings for each of the tabs
        for (var i = 0; i < settings.act.tabs.length; i++) {
          if (settings.act.tabs[i].hide !== false) {
            $(settings.act.tabs[i].accordionTab).toggleClass('is-hidden', settings.act.tabs[i].hide[key]);
            $(settings.act.tabs[i].tab).toggleClass('is-hidden', settings.act.tabs[i].hide[key]);
          }
        }
      }

      /**
       *
       * @param {type} settings
       * @param {type} key
       * @returns {undefined}
       */
      function manageSettings(settings, key) {

        // Handle non-breakpointed settings (these are passed to the breakpointed settings)
        // setHash
        if (typeof settings.setHash !== 'undefined') {
          settings.styles[key].setHash = settings.setHash;

          // Set the behavior when accordion/tabs view to autoscroll if a hash is selected
          // Only enable if hash exists in URL (prevents unwanted scroll on clean page load)
          if((settings.styles[key].accordionOrTab === 'accordion' || settings.styles[key].accordionOrTab === 'tabs') && window.location.hash) {
            settings.styles[key].scrollToAccordionOnLoad = true;
          } else {
            settings.styles[key].scrollToAccordionOnLoad = false;
          }
        }

        // scrollToAccordion
        if (typeof settings.scrollToAccordion !== 'undefined') {
          settings.styles[key].scrollToAccordion = settings.scrollToAccordion;
        }

        // scrollToAccordionOffsetClass
        if (typeof settings.scrollToAccordionOffsetClass !== 'undefined' && typeof settings.offsetPositionAgainst !== 'undefined' && settings.offsetPositionAgainst === 'class') {

          var offsetClass = settings.scrollToAccordionOffsetClass.match(/^[.]/) ? settings.scrollToAccordionOffsetClass : '.' + settings.scrollToAccordionOffsetClass;

          settings.styles[key].scrollToAccordionOffset = $(offsetClass).outerHeight(true);
        }

        // Handle breakpointed settings
        var breakpoint = settings.styles[key];

        // The active property on the form is from 1 but the plugin expect it to be from 0 so -1 to it
        if (typeof breakpoint.active !== 'undefined') {
          settings.styles[key].active = (parseInt(breakpoint.active) - 1).toString();
        }

        // Handle a custom animation speed
        if (typeof breakpoint.duration !== 'undefined' && typeof breakpoint.durationMs !== 'undefined' && breakpoint.duration === 'custom') {
          settings.styles[key].duration = parseInt(breakpoint.durationMs);
        } else if (typeof breakpoint.duration !== 'undefined' && breakpoint.duration !== 'custom') {
          //ensure duration is a number
          settings.styles[key].duration = parseInt(breakpoint.duration);
        }
        return settings;
      }

      /**
       * Get the default and breakpointed settings
       * @param {type} $el
       * @param {type} settings
       * @returns {unresolved}
       */
      function getSettings($el, settings) {

        // Set the defaults
        var defaults = {

          classes: {
            stateDefault: '',
            stateActive: 'is-active',
            stateDisabled: 'is-disabled',
            stateExcluded: 'is-excluded',
            container: '',
            ul: '',
            tab: '',
            anchor: '',
            panel: '',
            accordionTitle: 'coh-accordion-title',
            stateTypePrefix: 'coh-accordion-tabs-display'
          }
        };

        settings.breakpoints = {};
        settings.$element = $el;

        // Manage the settings

        // Update the settings prior to attaching the listeners
        for (var i = 0; i < cmm.breakpoints.length; i++) {

          var key = cmm.breakpoints[i].key;

          // Populate all breakpoints regardless of whether the settings are set or not to simulate inheritance
          settings.breakpoints[key] = {};

          $.extend(settings.breakpoints[key], defaults);

          if (typeof settings.styles[key] === 'object') {

            // Some settings need to be manually updated
            settings = manageSettings(settings, key);

            $.extend(settings.breakpoints[key], settings.styles[key]);
            $.extend(defaults, settings.styles[key]);
          }

          if(typeof settings.breakpoints[key].animation !== 'undefined')  {

            switch(settings.breakpoints[key].animation) {
              case 'slide':
                settings.breakpoints[key].animationQueue = false;
                break;
              case 'fade':
                settings.breakpoints[key].animationQueue = true;
                break;
              default:
                settings.breakpoints[key].animationQueue = true;
                break;
            }
          }
        }

        return settings;
      }

      /**
       * Get the settings for each tab
       * @param {type} tabs
       * @returns {undefined}
       */
      function getTabSettings(tabs) {

        // Manage tabs responsive settings
        for (var i = 0; i < tabs.tabs.length; i++) {

          // Visibility settings
          var previous, key;
          if (tabs.tabs[i].hide !== false && typeof tabs.tabs[i].hide === 'object') {

            for (var c = 0; c < cmm.breakpoints.length; c++) {

              key = cmm.breakpoints[c].key;

              if (typeof tabs.tabs[i].hide[key] === 'undefined') {
                tabs.tabs[i].hide[key] = previous;
              }
              previous = tabs.tabs[i].hide[key];
            }
          }
        }
      }

      /**
       * Initialise each instance of Accordion tabs
       */
      $.each($at, function (i, e) {

        var $this = $(e);

        var $onecd = once.filter(onceTabs, $this);

        // Has been initialised previously (must be checked first otherwise it gets bound next)
        once.filter(onceTabs, $this).forEach(function (e, i) {

          var $f = $(e);

          // Refresh the tabs and the settings to makesure it's upto date with all the latest tabs etc
          $f.responsiveTabs('refresh');
        });

        // No need to do anything after this as we only want to refresh ^^
        if($onecd.length > 0) {
          return true;
        }

        // Init the tabs
        var settings = getSettings($this, $this.data('cohAccordion'));

        // Bind the custom events
        $this.on('tabs-load', tabsLoad);
        $this.on('tabs-refresh', tabsRefresh);
        $this.on('tabs-activate-state', tabsStateChange);
        $this.on('tabs-activate', tabsActivate);

        $(once(onceTabs, $this)).responsiveTabs(settings.breakpoints[cmm.getCurrentBreakpoint().key]);

        // Pass the object for the Accordion tabs to the callback settings
        settings.act = $.data(this, 'responsivetabs');

        cmm.addListeners(settings, updateSettings);
      });
    }
  };

})(jQuery, Drupal, once, drupalSettings);
