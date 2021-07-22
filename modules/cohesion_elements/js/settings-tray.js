(function ($) {
    var dx8SettingsTray;
    var Dx8SettingTray = function($iFrame) {
        this.els = {
            $iFrame: $iFrame,
            $loader: $('<div class="dx8-context-loading"></div>'),
            $modalCloseBtn: $iFrame.closest('.ui-dialog').find('.ui-dialog-titlebar-close')
        };

        this.init();
    };

    Dx8SettingTray.prototype.init = function() {
        window.addEventListener('message', this.onMessageReceived, false);

        this.els.$iFrame.addClass('dx8-hidden');

        this.els.$loader.insertBefore(this.els.$iFrame);

    };

    Dx8SettingTray.prototype.onMessageReceived = function(event) {
        var data = event.data.split('.');
        if(data[0] !== 'dx8') {
            return;
        }
        switch (data[1]) {
        case 'componentformloaded':
            dx8SettingsTray.els.$loader.remove();
            dx8SettingsTray.els.$iFrame.removeClass('dx8-hidden');
            break;
        case 'componentformajaxstart':
            dx8SettingsTray.els.$iFrame.addClass('dx8-hidden');
            dx8SettingsTray.els.$loader.insertBefore(dx8SettingsTray.els.$iFrame);
            break;
        case 'componentformajaxend':
            dx8SettingsTray.els.$loader.remove();
            dx8SettingsTray.els.$iFrame.removeClass('dx8-hidden');
            break;
            case 'componentformapplied':
            // reload the page, appending the parameter to re-scroll to the edited component.
            var params = new URLSearchParams(window.location.search);
            params.set('dx8ContextScroll', data[2]);
            window.location.href = window.location.origin + window.location.pathname + '?' + params.toString();
            dx8SettingsTray.els.$modalCloseBtn.trigger('click');
            break;
        case 'componentformcancelled':
            dx8SettingsTray.els.$modalCloseBtn.trigger('click');
            break;
        default:
            return;
        }
    };

    $(window).on('dialog:aftercreate', function() {
        dx8SettingsTray = new Dx8SettingTray($('iframe.dx8-contextual'));
        $('body').addClass('dx8-contextual-tray-open');
    });

    $(window).on('dialog:afterclose', function() {
        $('body').removeClass('dx8-contextual-tray-open');
    });

    // autoscroll to edited element on page load.
    $(function () {
        var params = new URLSearchParams(window.location.search);
        var uuidToScroll = params.get('dx8ContextScroll');
        var $component = $('.coh-component-instance-' + uuidToScroll);

        if($component.length) {
            $('html, body').animate({
                scrollTop: $component.offset().top
            }, 350);
        }
    });

})(jQuery);

/**
 * URLSearchParams Polyfill
 * https://github.com/jerrybendy/url-search-params-polyfill
 * @author Jerry Bendy <jerry@icewingcc.com>
 * @licence MIT
 *
 */
!function(t){"use strict";var n=t.URLSearchParams?t.URLSearchParams:null,r=n&&"a=1"===new n({a:1}).toString(),e=n&&"+"===new n("s=%2B").get("s"),o="__URLSearchParams__",i=u.prototype,a=!(!t.Symbol||!t.Symbol.iterator);if(!(n&&r&&e)){i.append=function(t,n){v(this[o],t,n)},i.delete=function(t){delete this[o][t]},i.get=function(t){var n=this[o];return t in n?n[t][0]:null},i.getAll=function(t){var n=this[o];return t in n?n[t].slice(0):[]},i.has=function(t){return t in this[o]},i.set=function(t,n){this[o][t]=[""+n]},i.toString=function(){var t,n,r,e,i=this[o],a=[];for(n in i)for(r=c(n),t=0,e=i[n];t<e.length;t++)a.push(r+"="+c(e[t]));return a.join("&")};var s=!!e&&n&&!r&&t.Proxy;t.URLSearchParams=s?new Proxy(n,{construct:function(t,n){return new t(new u(n[0]).toString())}}):u;var f=t.URLSearchParams.prototype;f.polyfill=!0,f.forEach=f.forEach||function(t,n){var r=p(this.toString());Object.getOwnPropertyNames(r).forEach(function(e){r[e].forEach(function(r){t.call(n,r,e,this)},this)},this)},f.sort=f.sort||function(){var t,n,r,e=p(this.toString()),o=[];for(t in e)o.push(t);for(o.sort(),n=0;n<o.length;n++)this.delete(o[n]);for(n=0;n<o.length;n++){var i=o[n],a=e[i];for(r=0;r<a.length;r++)this.append(i,a[r])}},f.keys=f.keys||function(){var t=[];return this.forEach(function(n,r){t.push(r)}),l(t)},f.values=f.values||function(){var t=[];return this.forEach(function(n){t.push(n)}),l(t)},f.entries=f.entries||function(){var t=[];return this.forEach(function(n,r){t.push([r,n])}),l(t)},a&&(f[t.Symbol.iterator]=f[t.Symbol.iterator]||f.entries)}function u(t){((t=t||"")instanceof URLSearchParams||t instanceof u)&&(t=t.toString()),this[o]=p(t)}function c(t){var n={"!":"%21","'":"%27","(":"%28",")":"%29","~":"%7E","%20":"+","%00":"\0"};return encodeURIComponent(t).replace(/[!'\(\)~]|%20|%00/g,function(t){return n[t]})}function h(t){return decodeURIComponent(t.replace(/\+/g," "))}function l(n){var r={next:function(){var t=n.shift();return{done:void 0===t,value:t}}};return a&&(r[t.Symbol.iterator]=function(){return r}),r}function p(t){var n={};if("object"==typeof t)for(var r in t)t.hasOwnProperty(r)&&v(n,r,t[r]);else{0===t.indexOf("?")&&(t=t.slice(1));for(var e=t.split("&"),o=0;o<e.length;o++){var i=e[o],a=i.indexOf("=");-1<a?v(n,h(i.slice(0,a)),h(i.slice(a+1))):i&&v(n,h(i),"")}}return n}function v(t,n,r){var e="string"==typeof r?r:null!==r&&"function"==typeof r.toString?r.toString():JSON.stringify(r);n in t?t[n].push(e):t[n]=[e]}}("undefined"!=typeof global?global:"undefined"!=typeof window?window:this);
