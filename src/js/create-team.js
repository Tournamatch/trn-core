/**
 * Create team form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let form = document.getElementById('trn-create-team-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            event.stopPropagation();

            // Reset this on each submit.
            document.getElementById('trn-team-name').setCustomValidity('');
            document.getElementById('trn-team-name-error').innerText = trn_create_team_options.team_name_required_message;

            if (form.checkValidity() === true) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', trn_create_team_options.api_url);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', trn_create_team_options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        window.location.href = JSON.parse(xhr.response).data.redirect_link;
                    } else {
                        console.log(xhr.response);
                        document.getElementById('trn-team-name').setCustomValidity(trn_create_team_options.team_name_duplicate_message);
                        document.getElementById('trn-team-name-error').innerText = trn_create_team_options.team_name_duplicate_message;
                    }
                };

                xhr.send($.param({
                    name: document.getElementById('trn-team-name').value,
                    tag: document.getElementById('trn-team-tag').value
                }));
            }
            form.classList.add('was-validated');
        }, false);
    }, false);
})(trn);