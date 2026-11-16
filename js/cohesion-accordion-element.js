(function ($, Drupal) {
  "use strict";

  const processedElements = new Set();

  function constructLinkEl(linkUrl) {
    const linkElement = document.createElement("a");
    linkElement.href = linkUrl;
    linkElement.textContent = "?"; // You can set the link text here
    linkElement.setAttribute(
      "aria-label",
      "Open support article in new window"
    );
    linkElement.setAttribute("title", "Open support article in new window");
    linkElement.setAttribute("target", "_blank");
    linkElement.classList.add("ssa-drupal-help-link");
    return linkElement;
  }

  Drupal.behaviors.cohesionAccordionElement = {
    attach: function (context, settings) {
      const accordions = document.querySelectorAll("[data-ssa-help-link]");
      accordions.forEach((el) => {
        if (!processedElements.has(el)) {
          const linkUrl = el.dataset.ssaHelpLink;

          // Create an <a> element
          const linkElement = constructLinkEl(linkUrl);

          // Append the <a> element to the accordion element
          el.insertBefore(linkElement, el.firstChild);
          processedElements.add(el);
        }
      });
    },
  };
})(jQuery, Drupal);
