/**
 * Handles events for the list that displays a user's received invitations to join a team.
 *
 * @link       https://www.tournamatch.com
 * @since      3.13.0
  *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const options = trn_my_team_invitations_list_options;

        $.event('my-team-invitations').addEventListener('changed', function() {
            getTeamInvitations();
        });

        function acceptTeamInvitation(invitation_id) {
            console.log('accept');
            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'team-invitations/' + invitation_id + '/accept');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                let response = JSON.parse(xhr.response);
                if (xhr.status === 200) {
                    $.event('my-team-invitations').dispatchEvent(new Event('changed'));
                } else {
                    console.log(xhr.response);
                    document.getElementById('trn-my-team-invitations-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}:</strong> ${response.message}</div>`;
                }
            };

            xhr.send($.param({
                invitation_id: invitation_id
            }));
        }

        function declineTeamInvitation(invitation_id) {
            console.log('decline');
            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'team-invitations/' + invitation_id + '/decline');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr.response);
                if (xhr.status === 200) {
                    $.event('my-team-invitations').dispatchEvent(new Event('changed'));
                } else {
                    console.log(xhr.response);
                    // display error somewhere
                }
            };

            xhr.send($.param({
                invitation_id: invitation_id
            }));
        }

        function handleAcceptClick(event) {
            acceptTeamInvitation(this.dataset.invitationId)
        }

        function handleDeclineClick(event) {
            declineTeamInvitation(this.dataset.invitationId)
        }

        function addListeners() {
            console.log('adding handlers for team invitations.')
            let acceptLinks = document.getElementsByClassName('trn-accept-team-invitation-link');
            Array.prototype.forEach.call(acceptLinks,  function(acceptLink) {
                console.log('add');
                acceptLink.addEventListener('click', handleAcceptClick);
            });

            let declineLinks = document.getElementsByClassName('trn-decline-team-invitation-link');
            Array.prototype.forEach.call(declineLinks, function(declineLink) {
                console.log('add');
                declineLink.addEventListener('click', handleDeclineClick);
            });
        }

        function removeListeners() {
            console.log('removing handlers for team invitations.')
            let acceptLinks = document.getElementsByClassName('trn-accept-team-invitation-link');
            Array.prototype.forEach.call(acceptLinks,  function(acceptLink) {
                console.log('remove');
                acceptLink.removeEventListener('click', handleAcceptClick);
            });

            let declineLinks = document.getElementsByClassName('trn-decline-team-invitation-link');
            Array.prototype.forEach.call(declineLinks, function(declineLink) {
                console.log('remove');
                declineLink.removeEventListener('click', handleDeclineClick);
            });
        }

        function getTeamInvitations() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + 'team-invitations/?_embed&' + $.param({user_id: options.user_id}));
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                //console.log(xhr);
                let content = ``;
                if (xhr.status === 200) {
                    let invitations = JSON.parse(xhr.response);

                    if ( invitations !== null && invitations.length > 0 ) {
                        content += `<ul class="list-unstyled" id="trn-my-team-invitations-list">`;

                        Array.prototype.forEach.call(invitations, function(invitation) {
                            content += `<li class="text-center" id="trn-join-team-invitation-${invitation.team_member_invitation_id}">`;
                            content += `<a href="${invitation._embedded.team[0].link}">${invitation._embedded.team[0].name}</a> `;
                            content += `<a class="trn-accept-team-invitation-link" data-invitation-id="${invitation.team_member_invitation_id}"><i class="fa fa-check text-success"></i></a> `;
                            content += `<a class="trn-decline-team-invitation-link" data-invitation-id="${invitation.team_member_invitation_id}"><i class="fa fa-times text-danger"></i></a>`;
                            content += `</li>`;
                        });

                        content += `</ul>`;
                    } else {
                        content += `<p class="text-center">${options.language.zero_invitations}</p>`;
                    }
                } else {
                    content += `<p class="text-center">${options.language.error}</p>`;
                }

                removeListeners();
                document.getElementById('trn-my-team-invitations-response').nextSibling.remove();
                document.getElementById('trn-my-team-invitations-section').innerHTML += content;
                addListeners();
            };

            xhr.send();
        }
        getTeamInvitations();
    }, false);
})(trn);