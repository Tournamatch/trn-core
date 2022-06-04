/**
 * Handles the click events for the invite player to team dropdown.
 *
 * @link       https://www.tournamatch.com
 * @since      3.10.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let options = trn_invite_player_to_team_options;

        let buttons = document.getElementsByClassName('trn-invite-player-to-team');
        Array.prototype.forEach.call(buttons, function(button) {
            button.addEventListener('click', function(event) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', options.api_url + 'team-invitations/');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    let response = JSON.parse(xhr.response);
                    console.log(response);
                    if (xhr.status === 200) {
                        document.getElementById('trn-send-invite-response').innerHTML = `<div class="alert alert-success"><strong>${options.language.success}</strong>! ${response.message}</div>`;
                    } else {
                        document.getElementById('trn-send-invite-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send($.param({
                    team_id: button.dataset.teamId,
                    invitation_type: 'user',
                    user_id: options.user_id,
                }));
            }, false);
        }, false);
    }, false);
})(trn);