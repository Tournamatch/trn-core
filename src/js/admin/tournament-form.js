/**
 * Admin manage tournament form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.18.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './../tournamatch.js';

(function($){
    'use strict';

    // date time picker
    let startDateTimeField = document.getElementById('start_date_field');
    let startDateTime = document.getElementById('start_date');

    // Team size select
    let competition      = document.getElementById('competitor_type');
    let competitionGroup = document.getElementById('team_size_group');

    function toggleTeamSize() {
        if (competition.value === 'teams') {
            competitionGroup.style.display = 'table-row';
        } else {
            competitionGroup.style.display = 'none';
        }
    }
    competition.addEventListener('change', () => toggleTeamSize());
    toggleTeamSize();

    // submit form
    let options = trn_admin_tournament_form_options;
    let form = document.getElementById('trn_tournament_form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        let d = new Date(`${startDateTimeField.value}`);
        startDateTime.value = d.toISOString().slice(0, 19).replace('T', ' ');

        let url = options.api_url + 'tournaments/';

        if (options.id) {
            url += options.id;
        }

        if (form.checkValidity() === true) {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                if (xhr.status === 201) {
                    // reset form, trigger ladder list change in the future.
                    window.location.href = options.redirect_url + `&new_tournament_id=${JSON.parse(xhr.response).tournament_id}`;
                } else if ( xhr.status === 200 ) {
                    // updated, do nothing except update message
                    document.getElementById('trn-admin-manage-tournament-response').innerHTML = `<p class="notice notice-success"><strong>${options.language.success}:</strong> ${options.language.success_message}</p>`;
                    window.scrollTo(0, 0);
                } else {
                    document.getElementById('trn-admin-manage-tournament-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${response.message}</p>`;
                }
            };

            xhr.send(new FormData(form));
        }
    });
}( trn ));