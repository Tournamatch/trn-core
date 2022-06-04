/**
 * Handles asynchronously updating a player profile.
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
        let options = trn_player_profile_options;

        let form = document.getElementById('trn-edit-player-profile-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'players/' + form.dataset.playerId);
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                let response = JSON.parse(xhr.response);
                if (xhr.status === 200) {
                    document.getElementById('trn-update-response').innerHTML = `<div class="alert alert-success"><strong>${options.language.success}!</strong> ${options.language.success_message}</div>`;

                    // Update the player profile avatar.
                    if (response.avatar.length > 0) {
                        let avatarPreview = form.getElementsByClassName('profile-picture');
                        if (avatarPreview.length === 1) {
                            avatarPreview[0].setAttribute('src', options.avatar_upload_path + response.avatar);
                        }
                    }
                } else {
                    document.getElementById('trn-update-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}:</strong> ${options.language.failure_message}</div>`;
                }
            };

            xhr.send(new FormData(form));
        });
    }, false);
})(trn);