// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
  Object.defineProperty(Array.prototype, "find", {
    value: function(predicate) {
      // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this);

      // 2. Let len be ? ToLength(? Get(O, "length")).
      var len = o.length >>> 0;

      // 3. If IsCallable(predicate) is false, throw a TypeError exception.
      if (typeof predicate !== "function") {
        throw new TypeError("predicate must be a function");
      }

      // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
      var thisArg = arguments[1];

      // 5. Let k be 0.
      var k = 0;

      // 6. Repeat, while k < len
      while (k < len) {
        // a. Let Pk be ! ToString(k).
        // b. Let kValue be ? Get(O, Pk).
        // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
        // d. If testResult is true, return kValue.
        var kValue = o[k];
        if (predicate.call(thisArg, kValue, k, o)) {
          return kValue;
        }
        // e. Increase k by 1.
        k++;
      }

      // 7. Return undefined.
      return undefined;
    },
    configurable: true,
    writable: true
  });
}

(function($, once) {
  "use strict";

  Drupal.behaviors.CohesionLink = {
    attach: function(context) {
      // Libs
      var cmm = new Drupal.CohesionResponsiveBreakpoints();

      // Scroll to functionality.
      $.each(
        once("coh-js-scroll-to-init", ".coh-js-scroll-to", context),
        bindScrollTo
      );

      // Scroll to top functionality.
      $.each(
        once("coh-js-scroll-top-init", ".coh-js-scroll-top", context),
        bindScrollTop
      );

      // Toggle modifier interactivity.
      $.each(
        once("coh-toggle-modifier-init", ".coh-interaction", context),
        bindModifier
      );

      // Animation interactivity.
      $.each(
        once("coh-animation-init", ".coh-interaction", context),
        bindAnimation
      );

      // Function from David Walsh: http://davidwalsh.name/css-animation-callback
      function whichTransitionEvent() {
        var t,
          el = document.createElement("fakeelement");

        var transitions = {
          transition: "transitionend",
          OTransition: "oTransitionEnd",
          MozTransition: "transitionend",
          WebkitTransition: "webkitTransitionEnd"
        };

        for (t in transitions) {
          if (el.style[t] !== undefined) {
            return transitions[t];
          }
        }
      }

      function getComponentClass($el) {
        if (!$el || !$el.length) {
          return undefined;
        }
        for (var i = 0; i < $el.prop("classList").length; i++) {
          if (
            $el
              .prop("classList")
              [i].match(
                /coh-component-instance-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/
              )
          ) {
            return $el.prop("classList")[i];
          }
        }
      }

      function bindScrollTo() {
        var $this = $(this);

        $this.on("click", function(e) {
          // Don't click through to the page.
          e.preventDefault();

          var defaultSettings = {
            duration: 450,
            offset: 0
          };

          var scrollTarget = $this.data("cohScrollTo");
          var scrollDuration = $this.data("cohScrollDuration");

          /**
           * Offset can either be a jQuery selector (in which cas return elements height) or a number
           * @returns {*}
           */
          var scrollOffset = function() {
            var offset = $this.data("cohScrollOffset");

            if (typeof offset === "string") {
              var $el = $(offset);

              if ($el.length) {
                return 0 - $el.height();
              }

              return 0;
            }

            // plugin expects negative value, when positive value is better UX - reverses logic
            return offset * -1;
          };
          var scrollSettings = {
            duration: scrollDuration,
            offset: scrollOffset()
          };

          var settings = $.extend(defaultSettings, scrollSettings);

          // And smoothly scroll.
          $("html, body").scrollTo(scrollTarget, settings);
        });
      }

      function bindScrollTop() {
        $(this).on("click", function(e) {
          // Don't click through to the page.
          e.preventDefault();

          // And smoothly scroll.
          $("html, body").scrollTo(0, 450);
        });
      }

      function bindModifier() {
        var settings = $(this).data("interactionModifiers");

        // If there are no settings just return
        if (!settings || settings[0].modifierType === "") {
          return;
        }

        $(this).on("click.coh.modifier", function(e) {
          var $this = $(this);
          // Don't click through to the page.
          e.preventDefault();

          var modifier_types = {
            "add-modifier": "addClass",
            "remove-modifier": "removeClass",
            "toggle-modifier": "toggleClass",
            "toggle-modifier-accessible-collapsed": "toggleClass",
            "toggle-modifier-accessible-expanded": "toggleClass"
          };

          for (var i = 0; i < settings.length; i++) {
            if (!settings[i].modifierName) {
              console.warn(
                'Link element is set to toggle a modifier class on a target element but no "Modifier class name" was specified. You must specify a "Modifier class name".'
              );
              return;
            }

            var modifier_name = settings[i].modifierName.replace(/^\./, ""); // Check for and remove leading dot character
            var interaction_target = settings[i].interactionTarget;
            var modifier_type = settings[i].modifierType;
            var transitionEvent = whichTransitionEvent();
            var $interaction_scope;
            var $target;

            // If the scope is something other than 'this' but a target isn't supplied, don't do anything.
            if (
              settings[i].interactionScope !== "this" &&
              !interaction_target
            ) {
              console.warn(
                'Link element is set to "' +
                  modifier_type +
                  '" but does not have a "Target (jQuery selector)" specified. You must specify a "Target (jQuery selector)" or set "Scope to" to "This element"'
              );
              return;
            }

            $interaction_scope = getScope(
              $this,
              settings[i].interactionScope,
              settings[i].interactionParent
            );

            $target = getTarget(
              $this,
              interaction_target,
              $interaction_scope,
              settings[i].interaction_scope
            );

            if ($target.length) {
              $this.data("clickedModifier", !$this.data.clickedModifier);
              // Get jQuery method from modifier types object based on model and apply transition class
              $target[modifier_types[modifier_type]](modifier_name).addClass(
                "coh-transition"
              );

              $target.on(transitionEvent, function() {
                // Remove transition class once transition has finished
                $(this)
                  .removeClass("coh-transition")
                  .off(transitionEvent);
              });

              // If the modifier is an accessible popup
              if (modifier_type.indexOf("toggle-modifier-accessible-") === 0) {
                // Toggle aria-expanded attribute value
                $(this).attr(
                  "aria-expanded",
                  $(this).attr("aria-expanded") === "true" ? "false" : "true"
                );
              }
              // Run Drupal behaviors for anything that is hidden
              Drupal.attachBehaviors($target[0]);
            }
          }
        });
      }

      /**
       * Called when moving between breakpoints. This function compares the breakpoint you have just moved to with the
       * breakpoint you've just come from (stored in previousBreakPointAnimSettings). If you are moving from a breakpoint
       * that has animation to a breakpoint that does not have settings for a matching animationTarget
       * then the css display property is removed. https://cohesion-dev.atlassian.net/browse/COH-4794
       * @param mm
       */
      function cohCheckDisplayResize(mm) {
        var currentSettings =
          mm.cohesion.settings.breakpoints[mm.cohesion.key] || {};
        var animSettings =
          currentSettings.linkAnimation ||
          currentSettings.buttonAnimation ||
          [];
        mm.cohesion.settings.element
          .data("previousBreakPointAnimSettings")
          .forEach(function(prevSetting) {
            // if prevSetting.target doesn't exist in any of the current settings
            var matchedSetting = animSettings.find(function(setting) {
              return prevSetting.animationTarget === setting.animationTarget;
            });

            // In future we can add `displayReset` as a bool/toggle to the JSON form and control this behaviour
            if (!matchedSetting && prevSetting.displayReset !== false) {
              if (mm.cohesion.settings.element.data("clickedAnimation")) {
                // mm.cohesion.settings.element.trigger('click.coh.animation');
                runAnimation(mm.cohesion.settings.element, prevSetting, true);
                if (mm.cohesion.settings.element.data("clickedModifier")) {
                  mm.cohesion.settings.element.trigger("click.coh.modifier");
                }
              } else {
                if (prevSetting.animationTarget) {
                  var $interaction_scope = getScope(
                    mm.cohesion.settings.element,
                    prevSetting.animationScope,
                    prevSetting.animationParent
                  );
                  var $target = getTarget(
                    mm.cohesion.settings.element,
                    prevSetting.animationTarget,
                    $interaction_scope,
                    prevSetting.animationScope
                  );
                  $target.css("display", "");
                }
              }
            }
          });

        mm.cohesion.settings.element.data(
          "previousBreakPointAnimSettings",
          animSettings || []
        );
      }

      function bindAnimation() {
        var $this = $(this),
          data = $this.data("cohSettings"),
          settings = {
            element: $this,
            breakpoints: {}
          },
          key;

        $this.data("previousBreakPointAnimSettings", []);

        for (var i = 0; i < cmm.breakpoints.length; i++) {
          key = cmm.breakpoints[i].key;

          // Populate all breakpoints regardless of whether the settings are set or not to simulate inheritance
          settings.breakpoints[key] = {};
          if (typeof data[key] !== "undefined" && !$.isEmptyObject(data[key])) {
            settings.breakpoints[key] = data[key];

            var previous = data[key];
          } else {
            if (
              typeof cmm.breakpoints[i - 1] !== "undefined" &&
              typeof previous !== "undefined"
            ) {
              settings.breakpoints[key] = previous;
            }
          }
        }

        cmm.addListeners(settings, cohCheckDisplayResize);

        $this.on("click.coh.animation", function(e) {
          e.preventDefault();

          var currentSettings =
            settings.breakpoints[cmm.getCurrentBreakpoint().key];
          currentSettings =
            currentSettings.linkAnimation || currentSettings.buttonAnimation;

          if (currentSettings) {
            for (var i = 0; i < currentSettings.length; i++) {
              var currentSetting = currentSettings[i];

              if (currentSetting.animationType !== "none") {
                runAnimation($this, currentSetting);
              }
            }
          }
        });
      }

      function runAnimation($this, settings, removeDisplay) {
        var $interaction_scope;
        var $target, origin;

        $interaction_scope = getScope(
          $this,
          settings.animationScope,
          settings.animationParent
        );
        $target = getTarget(
          $this,
          settings.animationTarget,
          $interaction_scope,
          settings.animationScope
        );

        if ($target.length) {
          $this.data("clickedAnimation", !$this.data("clickedAnimation"));

          if (settings.animationOrigin) {
            origin = settings.animationOrigin.split(",");
          }

          $.each($($target), function() {
            var $that = $(this);

            $that.toggle({
              effect: settings.animationType,
              direction: settings.animationDirection,
              distance: settings.animationDistance,
              pieces: settings.animationPieces,
              percent: settings.animationScale,
              origin: origin,
              size: settings.animationFoldHeight,
              horizFirst: settings.animationHorizontalFirst,
              times: settings.animationIterations,
              easing: settings.animationEasing,
              duration: removeDisplay ? 0 : settings.animationDuration,
              complete: function() {
                // Run Drupal behaviors for anything that is hidden
                Drupal.attachBehaviors($(this)[0]);
                if (removeDisplay) {
                  $that.css("display", "");
                }
              }
            });
          });
        }
      }

      function getScope($this, scope, parent) {
        var $interaction_scope;
        switch (scope) {
          case "this":
            $interaction_scope = $this;
            break;
          case "parent":
            $interaction_scope = $this.closest(parent);
            break;
          case "component":
            var componentClass = getComponentClass(
              $this.closest(".coh-component")
            );

            if (componentClass) {
              $interaction_scope = $("." + componentClass);
            } else {
              // 'component' scope was chosen but there isn't a parent component. (i.e. element is just sat on a layout canvas).
              $interaction_scope = $(document);
            }
            break;
          default:
            $interaction_scope = $(document);
            break;
        }
        return $interaction_scope;
      }

      function getTarget(
        $this,
        interaction_target,
        $interaction_scope,
        interaction_scope
      ) {
        var $target = $();
        if (interaction_scope === "this" && !interaction_target) {
          $target = $this;
        } else if (!interaction_target) {
          console.warn(
            'Element does not have a "Target (jQuery selector)" specified. You must specify a "Target (jQuery selector)" or set "Scope to" to "This element".'
          );
          return $target;
        }

        if (!$target.length) {
          // Process the interaction target as a jQuery selector
          $target = $(interaction_target, $interaction_scope);
        }

        // Is it one of the top level elements in a component
        if (!$target.length) {
          $target = $interaction_scope.filter(interaction_target);
        }

        // Is it a child of the scope?
        if (!$target.length) {
          $target = $interaction_scope.find($(interaction_target));
        }

        // Try the interaction target as a className (legacy - we used to except a class, unprefixed with . instead of a jquery selector.
        if (interaction_target.indexOf(".") !== 0) {
          if (!$target.length) {
            $target = $interaction_scope.filter("." + interaction_target);
          }

          if (!$target.length) {
            $target = $interaction_scope.find($("." + interaction_target));
          }
        }

        if (!$target.length) {
          console.warn(
            'Element has "Target (jQuery selector)" set to "' +
              interaction_target +
              '", but no matching element was found on the page.'
          );
        }

        return $target;
      }
    }
  };
})(jQuery, once);
