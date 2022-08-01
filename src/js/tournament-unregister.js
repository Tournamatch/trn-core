/**
 * Handles asynchronous tournament unregister.
 *
 * @link       https://www.tournamatch.com
 * @since      3.17.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    // add listener for roster changed event
    window.addEventListener('load', function () {
        let options = trn_tournament_unregister_options;

        let buttons = document.getElementsByClassName('trn-tournament-unregister-button');
        Array.prototype.forEach.call(buttons, function (target) {
            target.addEventListener('click', function (event) {
                event.preventDefault();

                let tournament_registration_id = target.dataset.tournamentRegistrationId;
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', options.api_url + `tournament-registrations/${tournament_registration_id}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    console.log(xhr);
                    if (xhr.status === 204) {
                        // In the future, we should refresh the registration list.
                        window.location.href = options.refresh_url;
                        window.location.reload();
                    } else {
                        document.getElementById('trn-unregister-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${options.language.failure_message}</p>`;
                    }
                };

                xhr.send($.param({
                    tournament_id: tournament_registration_id,
                }));
            }, false);
        }, false);
    }, false);
})(trn);