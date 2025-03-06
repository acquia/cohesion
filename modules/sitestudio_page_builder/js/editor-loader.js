(function ($, Drupal, once, drupalSettings) {
  var destroyEvent, reAttachEvent;
  if (typeof window.CustomEvent !== "function") {
    // Polyfill for IE11 creating events.
    function CustomEvent(event, params) {
      params = params || {
        bubbles: false,
        cancelable: false,
        detail: undefined,
      };
      var evt = document.createEvent("CustomEvent");
      evt.initCustomEvent(
        event,
        params.bubbles,
        params.cancelable,
        params.detail
      );
      return evt;
    }
    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;
    //End polyfill.
    // destroyEvent = new CustomEvent('siteStudioDestroy');
    reAttachEvent = new CustomEvent("siteStudioReAttach");
  } else {
    // destroyEvent = new Event('siteStudioDestroy');
    reAttachEvent = new Event("siteStudioReAttach");
  }
  drupalSettings.cohesion.formGroup = "frontendEditor";
  drupalSettings.cohesion.formId = "frontendEditor";
  let localStorageLocation = "Drupal.siteStudio.pageBuilderEnabled";
  let pageBuilderEnabled = isPageBuilderEnabled();
  const $loader = $(
    '<div class="coh-editor-loading-container"><div class="coh-editor-loading"><p class="visually-hidden">Loading</p></div></div>'
  );

  function initLoader() {
    $("body").append($loader);
  }

  function removeLoader() {
    $loader.remove();
  }

  function contentLocking() {
    const modalSettings = {
      url: '/cohesionapi/content-lock',
      dialogType: 'modal',
      dialog: {
        title: 'Content Locked',
        width: 650,
        dialogClass: 'ssa-content-lock-modal',
        data: drupalSettings.cohesion.content_lock,
      },
    }

    // Open the modal.
    Drupal.ajax(modalSettings).execute();
  }

  function closeToolbarTrays() {
    if (Drupal.toolbar.models && Drupal.toolbar.models.toolbarModel) {
      $(Drupal.toolbar.models.toolbarModel.get("activeTab")).trigger("click");
    }
  }
  function removeDrupalToolbar() {
    const toolbarBar = document.getElementById("toolbar-bar");

    if (toolbarBar) {
      closeToolbarTrays();
      toolbarBar.style.opacity = 0;
      toolbarBar.style.visibility = "hidden";
      toolbarBar.setAttribute("aria-hidden", "true");
    }
  }

  function enablePageBuilder(ev) {
    // If there is a content lock set then we need to show a modal to the user
    // & disable the page builder if it's not the current user who is editing.
    if (drupalSettings.cohesion.content_lock && drupalSettings.user.uid !== drupalSettings.cohesion.content_lock.uid) {
      contentLocking();
      storePageBuilderEnabled(false);
    } else {
      if (ev) {
        ev.preventDefault();
      }

      if (isDrupalInEditMode()) {
        // Come out of Drupal's edit mode by triggering a click of the edit button.
        $("#toolbar-bar .toolbar-icon-edit").click();
      }

      initLoader();

      $("#coh-builder-btn").attr("aria-pressed", "true");

      // Init the Site Studio page builder
      const appEl = document.getElementById("ssaApp");
      if (!appEl) {
        const domEl = document.createElement("div");
        domEl.id = "ssaApp";
        domEl.classList.add("ssa-app");
        document.body.appendChild(domEl);
        $.getScript(
          drupalSettings.cohesion.urls["frontend-builder-js"].url,
          function () {
            removeDrupalToolbar();
            removeLoader();
          }
        );
      } else {
        appEl.dispatchEvent(reAttachEvent);
      }
      storePageBuilderEnabled(true);
    }
  }

  function isDrupalInEditMode() {
    return (
      localStorage.getItem("Drupal.contextualToolbar.isViewing") === "false"
    ); // false means we are in edit mode.
  }

  function isPageBuilderEnabled() {
    return localStorage.getItem(localStorageLocation) === "true";
  }

  /**
   *
   * @param value boolean
   */
  function storePageBuilderEnabled(value) {
    return localStorage.setItem(localStorageLocation, value ? "true" : "false");
  }

  // Attach the cohesion quick edit functionality to the page, binding the onclick function to toggle edit mode.
  Drupal.behaviors.siteStudioEditor = {
    attach: function attach(context) {
      if (window.frameElement) {
        // The page is embedded in an iframe on the same origin, so probably the style preview. Don't load vpb.
        return;
      }
      $(once("initSiteStudio", "body", context)).each(function () {
        if ($("[data-ssa-canvas]").length > 0) {
          if (isPageBuilderEnabled()) {
            enablePageBuilder();
          }

          $("body").on("click", "#coh-builder-btn", () => {
            if (!isPageBuilderEnabled()) {
              enablePageBuilder();
            }
          });

          $("body #ssa-builder-toggle").removeClass("hidden");
        } else {
          $("body #ssa-builder-toggle").addClass("hidden");
        }
      });
    },
  };

  $(document).on('click', '.ssa-btn.cancel.exit', function () {
    const currentPath = drupalSettings.path.currentPath;
    const nodeId = parseInt(currentPath.split('/')[1], 10);
    if (nodeId) {
      $.ajax({
        url: `${drupalSettings.path.baseUrl}cohesionapi/release-lock/${nodeId}`,
        type: 'POST',
      });
    }
  });

})(jQuery, Drupal, once, drupalSettings);
