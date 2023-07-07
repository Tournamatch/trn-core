/**
 * Admin settings page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.23.0
 *
 * @package    Tournamatch
 *
 */
(function () {
    'use strict';

    window.addEventListener('load', function () {

        const tabs = document.getElementsByClassName('nav-tab');
        const pages = document.getElementsByClassName('tab-pane');

        Array.prototype.forEach.call(tabs, (tab) => {
            tab.addEventListener('click', function(e) {
                Array.prototype.forEach.call(tabs, tab => tab.className = 'nav-tab');
                tab.className = 'nav-tab nav-tab-active';

                Array.prototype.forEach.call(pages, page => page.className = 'tab-pane');
                document.getElementById(tab.dataset.tab).className = 'tab-pane active';
            });
        });

        if (location.hash) {
            const target = location.hash.substr(1);
            const targetTab = document.querySelector('a[href="#' + target + '"].nav-tab');
            const targetPaneId = targetTab && targetTab.dataset && targetTab.dataset.tab || false;

            if (targetPaneId) {
                Array.prototype.forEach.call(tabs, tab => tab.className = 'nav-tab');
                targetTab.className = 'nav-tab nav-tab-active';

                Array.prototype.forEach.call(pages, page => page.className = 'tab-pane');
                document.getElementById(targetTab.dataset.tab).className = 'tab-pane active';
                // targetTab.classList.add('trn-nav-active');
                // targetTab.ariaSelected = true;

                // document.getElementById(targetPaneId).classList.add('trn-tab-active');
            }
        }
    }, false);
})();