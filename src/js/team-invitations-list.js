/**
 * Handles events for the list that displays invitations to join a team.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 * @since      3.10.0 Added support for displaying invitations to registered users.
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const options = trn_team_invitations_list_options;

        $.event('team-invitations').addEventListener('changed', function() {
            getTeamInvitations();
        });

        function deleteTeamInvitation(invitation_id) {
            let xhr = new XMLHttpRequest();
            xhr.open('DELETE', options.api_url + 'team-invitations/' + invitation_id);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                if (xhr.status === 204) {
                    getTeamInvitations();
                } else {
                    console.log(xhr.response);
                    // display error somewhere
                }
            };

            xhr.send();
        }

        function handleDeleteClick(event) {
            deleteTeamInvitation(this.dataset.invitationId)
        }

        // Accept join team invitation links.
        function addListeners() {
            let declineLinks = document.getElementsByClassName('trn-delete-team-invitations-link');
            Array.prototype.forEach.call(declineLinks, function(deleteLink) {
                deleteLink.addEventListener('click', handleDeleteClick);
            });
        }

        function removeListeners() {
            let deleteLinks = document.getElementsByClassName('trn-delete-team-invitations-link');
            Array.prototype.forEach.call(deleteLinks, function(deleteLink) {
                deleteLink.removeEventListener('click', handleDeleteClick);
            });
        }

        function getTeamInvitations() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + 'team-invitations/?_embed&' + $.param({team_id: options.team_id}));
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                // console.log(xhr);
                let content = ``;
                if (xhr.status === 200) {
                    let invitations = JSON.parse(xhr.response);

                    if ( invitations !== null && invitations.length > 0 ) {
                        content += `<ul class="trn-list-unstyled" id="trn-team-invitations-list">`;

                        Array.prototype.forEach.call(invitations, function(invitation) {
                            content += `<li class="trn-text-center" id="trn-join-team-invitations-${invitation.team_member_invitation_id}">`;
                            if ( 'email' === invitation.invitation_type ) {
                                content += `${invitation.user_email}`;
                            } else {
                                content += `<a href="${invitation._embedded.player[0].link}">${invitation._embedded.player[0].name}</a>`;
                            }
                            content += ` <a class="trn-delete-team-invitations-link" data-invitation-id="${invitation.team_member_invitation_id}"><i class="fa fa-times trn-text-danger"></i></a>`;
                            content += `</li>`;
                        });

                        content += `</ul>`;
                    } else {
                        content += `<p class="trn-text-center">${options.language.zero_invitations}</p>`;
                    }
                } else {
                    content += `<p class="trn-text-center">${options.language.error}</p>`;
                }

                removeListeners();
                document.getElementById('trn-team-invitations-section-header').nextSibling.remove();
                document.getElementById('trn-team-invitations-section').innerHTML += content;
                addListeners();
            };

            xhr.send();
        }
        getTeamInvitations();
    }, false);
})(trn);