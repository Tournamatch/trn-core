/**
 * Adds support for confirmation dialogs.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        document.addEventListener('trn-html-updated', prepareClickModal);

        prepareClickModal();
    }, false);

    function openModalCallback(event) {
        event.preventDefault();

        const clickListener = () => {
            this.dispatchEvent(new Event('trn.confirmed.action'));
            jQuery('#trn-confirm-modal').modal('hide');
        };

        document.getElementById('trn-confirm-title').innerHTML = this.dataset.confirmTitle;
        document.getElementById('trn-confirm-message').innerHTML =  this.dataset.confirmMessage;
        document.getElementById('trn-confirm-action-yes').addEventListener('click', clickListener );

        jQuery('#trn-confirm-modal').modal('show');
        jQuery('#trn-confirm-modal').on('hidden.bs.modal', function() {
            document.getElementById('trn-confirm-action-yes').removeEventListener('click', clickListener);
        });
    }

    function prepareClickModal() {
        let links = document.getElementsByClassName('trn-confirm-action-link');

        if ( links.length > 0 ) {
            const modal = document.getElementById('trn-confirm-modal');

            if ( null === modal ) {
                let content = ``;

                content += `<div class="modal trn-modal fade" id="trn-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="trn-confirm-title" aria-hidden="true">`;
                content += `  <div class="modal-dialog trn-modal-dialog trn-modal-dialog-centered" role="document">`;
                content += `    <div class="trn-modal-content">`;
                content += `      <div class="trn-modal-header">`;
                content += `        <h5 class="trn-modal-title" id="trn-confirm-title"></h5>`;
                content += `        <button type="button" class="trn-close" data-dismiss="modal" aria-label="Close">`;
                content += `          <span aria-hidden="true">&times;</span>`;
                content += `        </button>`;
                content += `      </div>`;
                content += `      <div class="trn-modal-body" id="trn-confirm-message"></div>`;
                content += `      <div class="trn-modal-footer">`;
                content += `        <button type="button" class="trn-button" id="trn-confirm-action-yes">${trn_confirm_action_options.language.yes}</button>`;
                content += `        <button type="button" class="trn-button trn-button-secondary" data-dismiss="modal">${trn_confirm_action_options.language.no}</button>`;
                content += `      </div>`;
                content += `    </div>`;
                content += `  </div>`;
                content += `</div>`;

                document.body.insertAdjacentHTML('beforeend', content);
            }

            Array.prototype.forEach.call(links, function (link) {
                link.addEventListener('click', openModalCallback );
            });
        }
    }

})(trn);