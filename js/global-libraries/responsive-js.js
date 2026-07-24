(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.CohesionResponsiveBreakpoints = function (settings) {

    this.constants = {
      'desktop': 'desktop-first',
      'mobile': 'mobile-first',
      'widthType': {
        'fluid': 'fluid',
        'fixed': 'fixed'
      },
      'mediaQuery': {
        'all': 'all',
        'max': 'max-width',
        'min': 'min-width'
      }
    };

    // Custom responsive grid settings
    this.settings = settings || drupalSettings.cohesion.responsive_grid_settings;

    // Array of breakpoints in the correct order
    this.breakpoints = [];
        
    // Keyed list of listeners
    this.listeners = {};

    /**
         * Init when first loaded to do some setup
         */
    this.init = function () {

      this.sortBreakpoints();
    };

    /**
         * Returns the current media query depending on the breakpoint "key" passed
         * @param {string} breakpoint xs|ps|sm|md|lg|xl
         */
    this.getBreakpointMediaQuery = function (breakpoint) {

      if (this.getGridType() === this.constants.mobile) {

        if (breakpoint === this.getFirstBreakpoint()) {

          return this.constants.mediaQuery.all;

        } else {

          if (typeof this.getBreakpoints()[breakpoint] !== 'undefined') {

            return '(min-width: ' + this.getBreakpointWidth(breakpoint) + 'px)';

          } else {

            // Custom breakpoint
            return '(min-width: ' + breakpoint + 'px)';
          }
        }
      }

      if (this.getGridType() === this.constants.desktop) {

        var breakpointIndex = this.getBreakpointIndex(breakpoint);

        var minWidth = 0;
        var breakpointMaxWidth = false;

        if (breakpoint !== this.getLastBreakpoint()) {
          minWidth = this.getBreakpointWidth(this.breakpoints[breakpointIndex].key);
        }

        if (breakpoint !== this.getFirstBreakpoint()) {
          breakpointMaxWidth = this.getBreakpointWidth(this.breakpoints[breakpointIndex - 1].key) - 1;
        }

        var mediaQuery = '(min-width: ' + minWidth + 'px)';
        if (breakpointMaxWidth) {
          mediaQuery = mediaQuery + ' and (max-width: ' + breakpointMaxWidth + 'px)';
        }

        return mediaQuery;
      }
    };

    /**
         * Returns the key for the first breakpoint (xs||ps) etc
         * @returns {string}
         */
    this.getFirstBreakpoint = function () {
      return this.breakpoints[0].key;
    };

    /**
         * Returns the key for the last breakpoint (xs||ps) etc
         * @returns {string}
         */
    this.getLastBreakpoint = function () {
      return this.breakpoints[this.breakpoints.length - 1].key;
    };

    /**
         * Get the current grid type mobile / desktop first
         * @return {string} dekstop-fist || mobile-first
         */
    this.getGridType = function () {
      return this.settings.gridType;
    };
        
    /**
         * Simple helper function to determine if we are mobile first
         * @returns {Boolean}
         */
    this.isMobileFirst = function() {
      return this.settings.gridType === this.constants.mobile;
    };
        
    /**
         * Simple helper function to determine if we are desktop first
         * @returns {Boolean}
         */
    this.isDesktopFirst = function()    {
      return this.settings.gridType === this.constants.desktop;
    }

    /**
         * Gets the responsive width type - fluid || fixed
         * @param {string} breakpoint xs|ps|sm|md|lg|xl
         * @returns {string} fixed || fluid
         */
    this.getBreakpointType = function (breakpoint) {
      return this.settings.breakpoints[breakpoint].widthType;
    };

    /**
         * Returns the current breakpoint width depending on the breakpoint "key" passed
         * @param {string} breakpoint xs|ps|sm|md|lg|xl
         * @returns {int} breakpoint width
         */
    this.getBreakpointWidth = function (breakpoint) {
      var bp = this.settings.breakpoints[breakpoint];
      if (!bp) return 0;
      return bp.width !== undefined ? bp.width : (bp.mobilePlaceholderWidth !== undefined ? bp.mobilePlaceholderWidth : 0);
    };

    /**
         * Returns the min-width breakpoint
         * @param {string} breakpoint xs|ps|sm|md|lg|xl
         * @returns {int}
         */
    this.getBreakpointMediaWidth = function (breakpoint) {

      if (this.getGridType() === this.constants.mobile) {

        if (breakpoint === this.getFirstBreakpoint()) {

          return 0;

        } else {

          if (typeof this.getBreakpoints()[breakpoint] !== 'undefined') {

            return this.getBreakpointWidth(breakpoint);

          } else {

            // Custom breakpoint
            return breakpoint;
          }
        }
      }

      if (this.getGridType() === this.constants.desktop) {

        var breakpointIndex = this.getBreakpointIndex(breakpoint);

        var minWidth = 0;
        var breakpointMaxWidth = false;

        if (typeof this.breakpoints[breakpointIndex - 1] !== 'undefined') {
          minWidth = this.getBreakpointWidth(this.breakpoints[breakpointIndex].key);
        }

        if (breakpoint !== this.getFirstBreakpoint()) {
          breakpointMaxWidth = this.getBreakpointWidth(this.breakpoints[breakpointIndex - 1].key) - 1;
        }

        var mediaQuery = '(min-width: ' + minWidth + 'px)';
        if (breakpointMaxWidth) {
          mediaQuery = mediaQuery + ' and (max-width: ' + breakpointMaxWidth + 'px)';
        }

        return breakpointMaxWidth;
      }

    };

    /**
         * Returns the current breakpoint outerGutter depending on the breakpoint "key" passed
         * @param {string} breakpoint xs|ps|sm|md|lg|xl
         * @returns {int} outerGutter width
         */
    this.getBreakpointOuterGutter = function (breakpoint) {
      return this.settings.breakpoints[breakpoint].outerGutter;
    };

    /**
         * Returns the current breakpoint index depending on the breakpoint "key" passed
         * @param {string} breakpoint xs|ps|sm|md|lg|xl
         * @returns {int} position of the breakpoint
         */
    this.getBreakpointIndex = function (breakpoint) {
      for (var i = 0; i < this.breakpoints.length; i++) {
        if (this.breakpoints[i].key === breakpoint) {
          return i;
        }
      }
    };

    /**
         * Returns a list of the breakpoint settings
         * @returns {object}
         */
    this.getBreakpoints = function () {
      return this.settings.breakpoints;
    };

    /**
         * Returns the settings for a specific breakpoint depending on the breakpoint "key" passed
         * @param {string} breakpoint xs|ps|sm|md|lg|xl
         */
    this.getBreakpointSettings = function (breakpoint) {
      return this.settings.breakpoints[breakpoint];
    };

    /**
         * Returns the settings for the current matched breakpoint
         * @returns {object}
         */
    this.getCurrentBreakpoint = function () {

      var match = false;

      for (var i = 0; i < this.breakpoints.length; i++) {
        // Set the first item to match as this is the default
        if (i === 0) {
          match = this.breakpoints[i];
        }

        // Check for matches
        var m = window.matchMedia(this.getBreakpointMediaQuery(this.breakpoints[i].key));
        if (m.matches) {
          match = this.breakpoints[i];
        }
      }
      return match;
    };

    /**
         * Patch to help out Safari understand where the object actually is
         * @param {Object} - The returned `MediaQueryListEvent` from the `window`
         * @returns {Object} - `MediaQueryListEvent`
         */
    this.getMediaQueryListEventObject = function (mql) {
      return typeof mql.target !== 'undefined' ? mql.target : mql;
    };

    /**
         * Shorthand method for this.getMediaQueryListEventObject()
         * @param {Object} - The returned `MediaQueryListEvent` from the `window`
         * @returns {Object} - `MediaQueryListEvent`
         */
    this.getMql = function (mql) {
      return this.getMediaQueryListEventObject(mql);
    };

    /**
         * @param {function} cb - the callback function to be executed at each breakpoint
         * @returns {object}
         */
    this.addListener = function (breakpoint, cb) {

    };

    /**
         * Run the callback with the correct settings
         * @param {type} mql
         * @param {type} key
         * @param {type} callback
         * @param {type} settings
         * @returns {undefined}
         */
    this.handleListener = function(mql, key, callback, callbackSettings)   {
            
      var _this = this;
            
      if (!mql.matches && this.isDesktopFirst())    {
        return;
      }
            
      // If there is no match in mobile first manually grab the current breakpoint settings as the user is most likely scaling down
      if(!mql.matches && this.isMobileFirst())  {
        key = _this.getCurrentBreakpoint().key;
        mql = _this.listeners[key];
      }
            
      mql = _this.getMql(mql);
            
      mql.cohesion = {
        'key': key,
        'settings': callbackSettings
      };
            
      return callback(mql);
    };

    /**
         * Binding of the native window.addListener
         * @param {type} cb - the callback function to be executed at each breakpoint
         * @returns {undefined}
         */
    this.addListeners = function (settings, callback) {
      var _this = this;
      var i, breakpointKey, mq, match;

      for (i = 0; i < _this.breakpoints.length; i++) {

        breakpointKey = _this.breakpoints[i].key;
        mq = _this.getBreakpointMediaQuery(breakpointKey);

        var listener;
        listener = window.matchMedia(mq);
                    
        // Keep a record of the listeners
        _this.listeners[breakpointKey] = listener;

        listener.addListener(this.handleListener.bind(this, listener, breakpointKey, callback, settings));

        // Store a current match
        if(listener.matches)    {
          match = listener;
          match.key = breakpointKey;
        }
      }
            
      // Run the callback for the first time
      if(match)   {
        this.handleListener(match, match.key, callback, settings);
      }
    };

    /**
         * Sorts the responsive breakpoints into the correct order
         * @returns {array}
         */
    this.sortBreakpoints = function () {

      var _this = this;

      var i = 0;
      // Pass breakpoints into an array ready to be sorted.
      // Normalize width: fall back to mobilePlaceholderWidth when width is missing (xs in mobile-first).
      for (var k in _this.settings.breakpoints) {
        if (_this.settings.breakpoints.hasOwnProperty(k)) {
          var bp = Object.assign({}, _this.settings.breakpoints[k]);
          if (bp.width === undefined) {
            bp.width = bp.mobilePlaceholderWidth !== undefined ? bp.mobilePlaceholderWidth : 0;
          }
          bp.key = k;
          _this.breakpoints.push(bp);
          i++;
        }
      }
      // Sort the array depending on mobile || desktop first
      if (_this.getGridType() === _this.constants.mobile) {

        _this.breakpoints.sort(function (a, b) {
          return a.width - b.width;
        });

      } else if (_this.getGridType() === _this.constants.desktop) {

        _this.breakpoints.sort(function (a, b) {
          return b.width - a.width;
        });

      } else {

        throw 'Mobile or Desktop first must be set in Website settings > Responsive grid settings';
      }
    };

    // Init the
    this.init();
  };

})(jQuery, Drupal, drupalSettings);
