/**
 * Join ladder form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.28.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const options = trn_ladder_join_options;

        let form = document.getElementById('trn-ladder-join-form');
        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                let xhr = new XMLHttpRequest();
                xhr.open('POST', options.api_url + `ladder-competitors`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        document.getElementById('trn-ladder-join-response').innerHTML = `<div class="alert alert-success"><strong>${options.language.success}</strong>: ${options.language.petition}</div>`;
                        form.remove();
                    } else if (xhr.status === 201) {
                        window.location.href = options.redirect_link;
                    } else {
                        const response = JSON.parse(xhr.response);
                        document.getElementById('trn-ladder-join-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send($.param({
                    ladder_id: document.getElementById('ladder_id').value,
                    competitor_id: document.getElementById('competitor_id').value,
                    competitor_type: document.getElementById('competitor_type').value
                }));
            }, false);
        }
    }, false);
})(trn);