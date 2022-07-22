/**
 * Handles asynchronously editing a competitor.
 *
 * @link       https://www.tournamatch.com
 * @since      3.23.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    // add listener for roster changed event
    window.addEventListener('load', function () {
        let options = trn_edit_competitor_options;

        let form = document.getElementById('trn-edit-competitor-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'ladder-competitors/' + form.dataset.ladderCompetitorId);
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                if (xhr.status === 200) {
                    document.getElementById('trn-update-response').innerHTML = `<div class="trn-alert trn-alert-success"><strong>${options.language.success}!</strong> ${options.language.success_message}</div>`;
                } else {
                    document.getElementById('trn-update-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}:</strong> ${options.language.failure_message}</div>`;
                }
            };

            xhr.send(new FormData(form));
        });
    }, false);
})(trn);