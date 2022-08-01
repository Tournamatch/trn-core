/**
 * Handles asynchronously updating a team profile.
 *
 * @link       https://www.tournamatch.com
 * @since      3.16.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    // add listener for roster changed event
    window.addEventListener('load', function () {
        let options = trn_team_profile_options;

        let form = document.getElementById('trn-edit-team-profile-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'teams/' + form.dataset.teamId);
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                let response = JSON.parse(xhr.response);
                if (xhr.status === 200) {
                    document.getElementById('trn-update-response').innerHTML = `<div class="trn-alert trn-alert-success"><strong>${options.language.success}!</strong> ${options.language.success_message}</div>`;

                    // Update the team profile avatar.
                    if (response.avatar.length > 0) {
                        let avatarPreview = form.getElementsByClassName('profile-picture');
                        if (avatarPreview.length === 1) {
                            avatarPreview[0].setAttribute('src', options.avatar_upload_path + response.avatar);
                        }
                    }
                } else if (xhr.status === 409) {
                    document.getElementById('trn-update-response').innerHTML = `<div class="trn-alert trn-alert-warning"><strong>${options.language.failure}:</strong> ${response.message}</div>`;
                } else {
                    document.getElementById('trn-update-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}:</strong> ${options.language.failure_message}</div>`;
                }
            };

            xhr.send(new FormData(form));
        });
    }, false);
})(trn);