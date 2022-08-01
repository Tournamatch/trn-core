/**
 * Report new match form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.21.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    const options = trn_report_new_match_options;

    window.addEventListener('load', function () {
        let form = document.getElementById('trn-report-match-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const inputs = form.getElementsByTagName('input');

            for (let i = 0; i < inputs.length; i++) {
                let input = inputs[i];

                if (input.name && !input.value) {
                    input.name = '';
                }
            }

            let xhr = new XMLHttpRequest();
            if (form.match_id && form.match_id.value) {
                xhr.open('POST', options.api_url + `matches/${form.match_id.value}`);
            } else {
                xhr.open('POST', options.api_url + 'matches');
            }
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                let response = JSON.parse(xhr.response);
                console.log(response);
                if ([201,200].includes(xhr.status)) {
                    window.location.href = `${response.link}`;
                } else {
                    document.getElementById('trn-report-match-form-message').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                }
            };

            xhr.send(new FormData(form));
        }, false);
    }, false);
})(trn);