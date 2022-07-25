/**
 * Admin manage ladder form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.18.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './../tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const options = trn_admin_ladder_form_options;

        const form = document.getElementById('trn-ladder-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (form.checkValidity() === true) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', options.api_url);
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    console.log(xhr);
                    if (xhr.status === 201) {
                        // reset form, trigger ladder list change in the future.
                        window.location.href = window.location.href + `&new_ladder_id=${JSON.parse(xhr.response).ladder_id}`;
                    } else if ( xhr.status === 200 ) {
                        // updated, do nothing except update message
                        document.getElementById('trn-admin-manage-ladder-response').innerHTML = `<p class="notice notice-success"><strong>${options.language.success}:</strong> ${options.language.success_message}</p>`;
                        window.scrollTo(0, 0);
                    } else {
                        document.getElementById('trn-admin-manage-ladder-response').innerHTML = `<p class="notice notice-success"><strong>${options.language.failure}:</strong> ${response.message}</p>`;
                    }
                };

                xhr.send(new FormData(form));
            }
        }, false);


        // Team size select
        let competition      = document.getElementById('competitor_type');
        let competitionGroup = document.getElementsByClassName('trn_team_size_row');

        function toggleTeamSize() {
            if (competition.value === 'teams') {
                Array.from(competitionGroup).forEach((element) => {
                    element.style.display = 'table-row';
                });
            } else {
                Array.from(competitionGroup).forEach((element) => {
                    element.style.display = 'none';
                });
            }
        }
        competition.addEventListener('change', () => toggleTeamSize());
        toggleTeamSize();

    }, false);
})(trn);