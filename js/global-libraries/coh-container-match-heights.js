(function ($, Drupal) {

  'use strict';

  // Create the defaults once
  var cmm = new Drupal.CohesionResponsiveBreakpoints();

  var pluginName = 'cohesionContainerMatchHeights';

  var defaults = {
    current: false,
    excludeElements: ['column'],
    expressionPrefixes: ['>', '> .coh-column'],
    loadersPrefix: '.coh-row > .coh-row-inner',
    elements : {
      'none': {
        'parent': 'none',
        'child': false
      },
      'h1': {
        'parent': 'h1',
        'child': false
      },
      'h2': {
        'parent': 'h2',
        'child': false
      },
      'h3': {
        'parent': 'h3',
        'child': false
      },
      'h4': {
        'parent': 'h4',
        'child': false
      },
      'h5': {
        'parent': 'h5',
        'child': false
      },
      'h6': {
        'parent': 'h6',
        'child': false
      },
      'p': {
        'parent': 'p',
        'child': false
      },
      'list-container': {
        'parent': '.coh-list-container',
        'child': false
      },
      'container': {
        'parent': '.coh-container',
        'child': false
      },
      'wysiwyg': {
        'parent': '.coh-wysiwyg',
        'child': false
      },
      'hyperlink': {
        'parent': 'a',
        'child': false
      },
      'blockquote': {
        'parent': 'blockquote',
        'child': false
      },
      'slide': {
        'parent': '.slick-list > .slick-track > .coh-slider-item',
        'child': false
      },
      'iframe': {
        'parent': '.coh-iframe',
        'child': false
      },
      'youtube-video-background': {
        'parent': '.coh-youtube-video-background',
        'child': false
      }
    },
    loaders: [
      '.coh-row > .coh-row-inner frame',
      '.coh-row > .coh-row-inner iframe',
      '.coh-row > .coh-row-inner img',
      '.coh-row > .coh-row-inner input[type="image"]',
      '.coh-row > .coh-row-inner link',
      '.coh-row > .coh-row-inner script',
      '.coh-row > .coh-row-inner style'
    ]
  };

  // The actual plugin constructor
  function ccmh(element, options) {

    this.element = element;
    this.$element = $(element);

    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;

    this._name = pluginName;
    this._current = false;

    this.init();
  }

  ccmh.prototype.init = function () {
    // Place initialization logic here
    // Already have access to the DOM element and the options via the instance,
    // e.g., this.element and this.options

    var _self = this;
    var key = '';
    var previous = {
      target: 'none'
    };

    var settings = {};
    settings._self = _self;
    settings.breakpoints = {};

    for (var i = 0; i < cmm.breakpoints.length; i++) {

      key = cmm.breakpoints[i].key;

      settings.breakpoints[key] = previous;

      if (typeof _self.options.targets[key] !== 'undefined') {

        settings.breakpoints[key] = _self.options.targets[key];

        previous = _self.options.targets[key];

      } else {

        if (typeof cmm.breakpoints[i - 1] !== 'undefined' && typeof previous !== false) {

          settings.breakpoints[key] = previous;

          _self.options.targets[key] = {};
          _self.options.targets[key] = previous;
        }
      }
    }

    // Bind the listeners
    cmm.addListeners(settings, _self.setMatchHeightsCallback);

    // Once the ajax has finished loading AND anything else that could effect the layout (onload)
    $(_self.options.context).ajaxComplete(function (event, xhr, settings) {

      $.fn.matchHeight._update();

      $(_self.options.loaders.toString(), _self.options.context).on('load', function () {
        if ($(this).length) {
          $.fn.matchHeight._update();
        }
      });
    });
  };

  /**
     * Grabs the HTML Element / Class from our mapper otherwise turns the value into a class
     * @param {String} elementKey - the key of `this.settings.elements`
     * @returns CSS selector from the mapper || generates a custom class
     */
  ccmh.prototype.getElement = function (elementKey) {
    var element;

    // If the value exists in 'elements' then use this otherwise hopefully this is a CSS class
    if (this.options.elements.hasOwnProperty(elementKey)) {
      element = this.options.elements[elementKey];
    } else {
      element = elementKey.match(/^[.]/) ? elementKey : '.' + elementKey;
    }

    return element;
  };

  /**
     * Generates the jQuery selector
     * @param {String} element - the key of `this.settings.elements`
     * @param {Int} targetLevel (optional)
     * @returns jQuery selector
     */
  ccmh.prototype.getElementExpression = function (element, targetLevel) {

    var expression = [],
      prefixes = [''],
      el = this.getElement(element),
      elementIsClass = typeof el === 'string',
      depth = typeof targetLevel !== 'undefined' ? targetLevel : false;

    if(this.options.excludeElements.indexOf(element) < 0)   {
      prefixes = this.options.expressionPrefixes;
    }

    for (var i = 0; i < prefixes.length; i++) {

      if(!elementIsClass) {
        // Append the parent element
        // If element, the element should be an immediately inside
        expression[i] = prefixes[i] + ' > ' + el.parent;
      } else {
        // Append the parent element

        // If Custom class then drill down to the class to any level
        expression[i] = prefixes[i] + ' ' + el;
      }

      // Append the :nth-of-type
      if(depth !== false) {
        expression[i] = expression[i] + ':nth-of-type(' + depth + ')';
      }

      // Append any children
      if(!elementIsClass && el.child)   {
        expression[i] = expression[i] + ' > ' + el.child;
      }
    }
    return expression.join(', ');
  };

  /**
     * Initialises match heights
     * @param {Object} settings - object of the breakpoints
     * @returns {jQuery obj} - match heights jQuery object
     */
  ccmh.prototype.setMatchHeights = function (settings) {

    var _self = this;

    var target = settings.cohesion.settings.breakpoints[settings.cohesion.key];

    // If the breakpoint is false do not set anything - just let it inherit or do its thing
    if(typeof target === 'undefined' || target === false)    {
      return;
    }

    var el = _self.getElement(target.target);

    _self.destroyMatchHeights();

    if (el !== 'none') {

      var expression = _self.getElementExpression(target.target, target.targetLevel);

      // Save the current matches so we can destroy it later
      _self._current = $(expression, _self.$element);

      return _self._current.matchHeight({
        byRow: false
      });
    }
  };

  /**
     * Wrapper for when the callback is returned so we can apply the correct scope
     * @param {type} settings
     * @returns {undefined}
     */
  ccmh.prototype.setMatchHeightsCallback = function(settings) {
        
    var _self = settings.cohesion.settings._self || this;

    return _self.setMatchHeights(settings);
  };

  /**
     * Destroys match heights for the current instance
     * @returns {unresolved}
     */
  ccmh.prototype.destroyMatchHeights = function () {
    return $(this._current).matchHeight({
      remove: true
    });
  };


  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName, new ccmh(this, options));
      }
    });
  };

})(jQuery, Drupal);
