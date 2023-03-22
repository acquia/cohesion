(function($) {
    "use strict";
    const dxPreviewControls = function() {
        var self = this;
        this.el = {
            container: document.getElementById('dx-preview-container'),
            colContainer: document.getElementById('dx-preview-column-width'),
            body: document.getElementsByTagName('body')[0],
            html: document.getElementsByTagName('html')[0]
        };
        this.animSpeed = 200;
        this.columnWidth = 12;
        this.resizing = false;

        this.init = init;
        this.setContainerType = setContainerType;
        this.setContentWidth = setContentWidth;
        this.setBgColor = setBgColor;
        this.bindEvents = bindEvents;
        this.onResizeWindowFinished = onResizeWindowFinished;
        this.onResizePlaceholderFinished = onResizePlaceholderFinished;
        this.disableLinks = disableLinks;
        this.setGridType = setGridType;
        this.setHighlightedElement = setHighlightedElement;


        function getFollowingSiblings(elem) {

            // Setup siblings array and get the first sibling
            var siblings = [];
            var sibling = elem.parentNode.firstChild;
            var found = false;

            // Loop through each sibling and, once we find original elem, push them to the array
            while (sibling) {
                if(sibling === elem) {
                    found = true; // start adding siblings to the array.
                }
                if (found && sibling.nodeType === 1 && sibling !== elem) {
                    if(sibling.classList.contains('dx-preview-placeholder')) {
                        break;
                    }
                    if(!sibling.classList.contains('dx-contextual-region')) {
                        siblings.push(sibling);
                    }
                }
                sibling = sibling.nextSibling;
            }

            return siblings;

        }

        function setContainerType(boxedWidth) {
            if(!self.el.container) {
                return;
            }
            if (boxedWidth) {
                self.el.container.children[0].classList.add('coh-container-boxed');
            } else {
                self.el.container.children[0].classList.remove('coh-container-boxed');
            }
            setTimeout(function () {
                onResizeWindowFinished();
            }, self.animSpeed+10);
        }

        function setContentWidth(colWidth) {
            if(!self.el.colContainer) {
                return;
            }
            self.el.colContainer.classList.remove('dx-preview-col-' + self.columnWidth);
            self.el.colContainer.classList.add('dx-preview-col-' + colWidth);
            self.columnWidth = colWidth;
            setTimeout(function () {
                onResizeWindowFinished();
            }, self.animSpeed+10);
        }
        function setBgColor(color) {
            if (color) {
                self.el.html.style.background = color;
            } else {
                self.el.html.style.background = 'transparent';
            }
        }

        function bindEvents() {
            window.addEventListener('resize', function() {

                clearTimeout(self.resizeTimer);
                self.resizeTimer = setTimeout(function() {
                  var msg = {
                    type: 'contentResized',
                    width: self.el.body.offsetWidth,
                    height: self.el.body.offsetHeight,
                  };
                  window.parent.postMessage(JSON.stringify(msg), '*');
                }, self.animSpeed+10);

            });

            window.addEventListener('message', function (msg) {
                var data;
                try {
                    data = JSON.parse(msg.data);
                } catch (e) {
                    return;
                }

                switch (data.type) {
                    case 'updateHeight':
                        setTimeout(function () {
                            onResizeWindowFinished();
                        }, self.animSpeed+10);
                        break;
                    case 'setWidth':
                        setContentWidth(data.width);
                        break;

                    case 'setContainerType':
                        setContainerType(data.boxedWidth);
                        break;

                    case 'setGridType':
                        setGridType(data.gridType);
                        break;

                    case 'setHighlightedElement':
                        setHighlightedElement(data.uuid);
                        break;

                    case 'setBgColor':
                        setBgColor(data.color);
                        break;
                }
            }, false);

            [].forEach.call(document.getElementsByClassName('dx-preview-placeholder'), function (el) {
                el.dxdata.dxHighlightEls.forEach(function(highlightEl) {
                    highlightEl.dataset.dxPreviewHoverUuid = el.id;

                    highlightEl.addEventListener("mouseover", function( event ) {
                        if(self.resizing) {
                            return;
                        }
                        if((event.target.dataset.dxPreviewHoverUuid || event.currentTarget.dataset.dxPreviewHoverUuid) === el.id) {
                            event.stopPropagation();
                            onHoverCohElement(event.target.dataset.dxPreviewHoverUuid || event.currentTarget.dataset.dxPreviewHoverUuid, true);
                        }
                    });
                    highlightEl.addEventListener("mouseout", function( event ) {
                        if((event.target.dataset.dxPreviewHoverUuid || event.currentTarget.dataset.dxPreviewHoverUuid) === el.id) {
                            onHoverCohElement(event.target.dataset.dxPreviewHoverUuid || event.currentTarget.dataset.dxPreviewHoverUuid, false);
                        }
                    });
                });
            });

        }

        function onHoverCohElement(uuid, isMouseEnter) {
            var msg = {
                type: isMouseEnter ? 'elementMouseEnter' : 'elementMouseLeave',
                uuid: uuid
            };
            window.parent.postMessage(JSON.stringify(msg), '*');
        }

        function onResizePlaceholderFinished(size, uuid) {
            var msg = {
                type: 'placeholderResized',
                uuid: uuid,
                height: size.height,
            };
            window.parent.postMessage(JSON.stringify(msg), '*');
        }

        function onResizeWindowFinished() {
            // This is to ensure child elements like videos are triggered into updating their size.
            if (typeof(Event) === 'function') {
                window.dispatchEvent(new Event('resize'));
            } else {
              var evt = window.document.createEvent('UIEvents');
              evt.initUIEvent('resize', true, false, window, 0);
              window.dispatchEvent(evt);
            }

            Array.prototype.forEach.call(document.getElementsByClassName('dx-resizable'), function(el) {
                el.getElementsByClassName('dx-placeholder-dimensions')[0].innerHTML = '(' + el.offsetWidth + 'px x ' + el.offsetHeight + 'px)';

            });
        }

        function disableLinks() {
            var links = document.getElementsByTagName('a');
            for (var i = 0; i < links.length; i++) {
                var link = links[i];
                if(link.hasAttribute('href') && link.getAttribute('href').indexOf('#') !== 0) {
                    link.setAttribute('target', '_blank');
                }
            }
        }

        function setHighlightedElement(uuid) {
            if(uuid) {
                var el = document.getElementById(uuid);
                if(!el) {
                    return;
                }
                var minTop,
                    minLeft;
                var tops = [];
                var lefts = [];
                var rights = [];
                var bottoms = [];

                el.dxdata.dxHighlightEls.forEach(function(highlightEl) {
                    var rect = highlightEl.getBoundingClientRect();
                    // check the element is actually visible on the page - otherwise we end up incorrectly setting the minTop & minLeft to be 0 for hidden elements.
                    if (elemIsVisible(highlightEl)) {
                        tops.push(rect.top);
                        lefts.push(rect.left);
                        rights.push(rect.left + rect.width);
                        bottoms.push(rect.top + rect.height);
                    }
                });

                minTop = getMinOfArray(tops);
                minLeft = getMinOfArray(lefts);

                self.el.highlight.style.opacity = 1;
                self.el.highlight.style.top = minTop + 'px';
                self.el.highlight.style.left = minLeft + 'px';
                self.el.highlight.style.width = getMaxOfArray(rights) - minLeft + 'px';
                self.el.highlight.style.height = getMaxOfArray(bottoms) - minTop + 'px';
            } else {
                self.el.highlight.style.opacity = 0;
            }
        }

        function elemIsVisible(elem) {
            return !!( elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length );
        }

        function getMaxOfArray(numArray) {
            return Math.max.apply(null, numArray);
        }

        function getMinOfArray(numArray) {
            return Math.min.apply(null, numArray);
        }

        function setGridType(type) {
            if (!type || type === 'none') {
                self.el.overlay.style.display = 'none';
                self.el.overlayContainer.classList.remove('coh-container-boxed');
            } else if(type  === 'boxed') {
                self.el.overlayContainer.classList.add('coh-container-boxed');
                self.el.overlay.style.display = 'block';
            } else if(type === 'fluid') {
                self.el.overlayContainer.classList.remove('coh-container-boxed');
                self.el.overlay.style.display = 'block';
            }
        }

        function initHighlight() {
            // create the highlight overlay div.
            self.el.highlight = document.createElement('div');
            self.el.highlight.classList.add('dx-highlight-overlay');
            self.el.body.appendChild(self.el.highlight);

            [].forEach.call(document.getElementsByClassName('dx-preview-placeholder'), function (el) {
                el.dxdata = {
                    dxHighlightEls: getFollowingSiblings(el)
                };
                // Hide with JS after page load to ensure # in url scrolls down to last modified element.
                window.setTimeout(function () {
                    el.style.display = 'none';
                },100);
            });
        }

        function initGridMask() {
            self.el.overlay = document.createElement('div');
            self.el.overlay.classList.add('dx-grid-mask');
            self.el.overlay.style.display = 'none';

            self.el.overlayContainer = document.createElement('div');
            self.el.overlayContainer.classList.add('coh-container');

            self.el.row = document.createElement('div');
            self.el.row.classList.add('coh-row');
            self.el.row.classList.add('coh-row-xl');
            self.el.row.classList.add('coh-row-hidden-xl');

            self.el.rowInner = document.createElement('div');
            self.el.rowInner.classList.add('coh-row-inner');

            for (var cols = 0; cols < 12; cols++) {
                let column = document.createElement('div');
                column.classList.add('dx-preview-col');
                column.classList.add('coh-column');
                column.classList.add('coh-col-xl');
                column.classList.add('coh-col-xs');
                column.appendChild(document.createElement('div'));
                self.el.rowInner.appendChild(column);
            }

            self.el.row.appendChild(self.el.rowInner);
            self.el.overlayContainer.appendChild(self.el.row);
            self.el.overlay.appendChild(self.el.overlayContainer);
            self.el.body.appendChild(self.el.overlay);

        }

        function initResizable() {
            // only if we have jquery, jquery.ui and jquery ui resizable.
            if (!$ || typeof $.ui === 'undefined' || typeof $().resizable !== 'function'){
              return;
            }

            $(function() {
                $('.dx-resizable').resizable({
                    autoHide: true,
                    'handles': {
                        's': '.dx-iframe-resize'
                    },
                    resize: function( event, ui ) {
                        $(this).find('span').text('(' + ui.size.width + 'px x ' + ui.size.height + 'px)');
                        // When resizable is at the bottom of the window we need to make the window scroll as you try and stretch the div down
                        // this basically says 'if the bottom of the div is 10px from the bottom of the viewport then
                        // scroll the viewport so that the bottom of the div is visible.
                        if((window.scrollY + document.documentElement.clientHeight) - (ui.size.height + this.offsetTop) < 10 ) {
                            $(window).scrollTo((ui.size.height + this.offsetTop + 10) - document.documentElement.clientHeight);
                        }
                    },
                    create: function (event) {
                        this.getElementsByClassName('dx-placeholder-dimensions')[0].innerHTML = '(' + event.target.offsetWidth + 'px x ' + event.target.offsetHeight + 'px)';
                    },
                    start: function () {
                        self.resizing = true;
                    },
                    stop: function (event, ui) {
                        self.resizing = false;
                        // Tell parent what just happened so we can persist the placeholder height
                        onResizePlaceholderFinished(ui.size, ui.element.data('dx-preview-hover-uuid'));

                        //TODO re-init match heights.
                        $.fn.matchHeight._update();
                    }
                });
            });
        }

        function handleBreakpointIndicator() {
            // if we are not popped out, we need to prevent the breakpoint indicator module from showing unnecessarily.
            if (drupalSettings && (!drupalSettings.path.currentQuery || drupalSettings.path.currentQuery.popped !== 'true')) {
                delete Drupal.behaviors.cohesionBreakpointIndicator;
            }
        }

        function init() {
            self.el.body.style.minHeight = '240px';
            if(self.el.colContainer) {
                if(!self.el.colContainer.children.length) {
                    self.el.colContainer.innerText = 'No content';
                    self.el.colContainer.classList.add('dx-no-content');
                }
            }

            if (navigator.userAgent.match(/(iPhone|iPod|iPad)/i)) {
                self.el.html.classList.add('dx-is-ios');
            }

            initGridMask();
            initHighlight();
            if(self.el.container) {
                self.el.container.style.transition = 'width ' + self.animSpeed + 'ms ease-in-out';
                self.disableLinks();
            }
            handleBreakpointIndicator();
            self.bindEvents();
            onResizeWindowFinished();
            window.parent.postMessage(JSON.stringify({
                type: 'ready',
            }), '*');

            initResizable();
        }

        this.init();
    };

    let dxToolbar = new dxPreviewControls();

})(jQuery);
