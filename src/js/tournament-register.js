/**
 * Tournament registration form.
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
        const options = trn_tournament_register_options;

        let form = document.getElementById('trn-tournament-join-form');
        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                let xhr = new XMLHttpRequest();
                xhr.open('POST', options.api_url + `tournament-competitors`);
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        document.getElementById('trn-tournament-join-response').innerHTML = `<div class="alert alert-success"><strong>${options.language.success}</strong>: ${options.language.petition}</div>`;
                        form.remove();
                    } else if (xhr.status === 201) {
                        window.location.href = options.redirect_link;
                    } else {
                        const response = JSON.parse(xhr.response);
                        document.getElementById('trn-tournament-join-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send(new FormData(form));
            }, false);
        }
    }, false);
})(trn);