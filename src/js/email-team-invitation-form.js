/**
 * Handles events for sending email invitations to join a team.
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
        const options = trn_email_team_invitation_form_options;

        let form = document.getElementById('trn-email-team-invitation-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            event.stopPropagation();

            // Reset this on each submit.
            document.getElementById('trn-email-invite-address').setCustomValidity('');

            if (form.checkValidity() === true) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', options.api_url + 'team-invitations/');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                     console.log(xhr);
                    if (xhr.status === 200) {
                        $.event('team-invitations').dispatchEvent(new Event('changed'));
                        document.getElementById('trn-email-team-invitation-form').reset();
                        form.classList.remove('was-validated');
                    } else {
                        document.getElementById('trn-email-invite-address').setCustomValidity(options.language.email_required);
                    }
                };

                xhr.send($.param({
                    team_id: options.team_id,
                    invitation_type: 'email',
                    email: document.getElementById('trn-email-invite-address').value,
                }));
            }

            form.classList.add('was-validated');
        }, false);
    }, false);
})(trn);