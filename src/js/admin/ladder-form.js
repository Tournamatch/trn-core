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


        // Team size group
        const competition_field = document.getElementById('competitor_type');
        const team_size_group = document.getElementById('team_size_group');

        function toggleTeamSize() {
            const team_size_fields = document.getElementById('team_size');
            if (competition_field.value === 'players') {
                team_size_fields.required = false;
                team_size_group.style.display = 'none';
                team_size_fields.disabled = true;
            } else {
                team_size_group.style.display = 'table-row';
                team_size_fields.required = true;
                team_size_fields.disabled = false;
            }
        }
        competition_field.addEventListener('change', function () {
            toggleTeamSize();
        });
        toggleTeamSize();

    }, false);
})(trn);