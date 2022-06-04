/**
 * Handles events for the list that displays a user's sent invitations to join a team.
 *
 * @link       https://www.tournamatch.com
 * @since      3.15.0
  *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const options = trn_my_team_requests_list_options;

        $.event('my-team-requests').addEventListener('changed', function() {
            getTeamRequests();
        });

        function deleteTeamRequest(request_id) {
            console.log('delete');
            let xhr = new XMLHttpRequest();
            xhr.open('DELETE', options.api_url + 'team-requests/' + request_id);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr.response);
                if (xhr.status === 204) {
                    $.event('my-team-requests').dispatchEvent(new Event('changed'));
                } else {
                    console.log(xhr.response);
                    // display error somewhere
                }
            };

            xhr.send();
        }

        function handleDeleteClick(event) {
            deleteTeamRequest(this.dataset.requestId)
        }

        function addListeners() {
            console.log('adding handlers for team requests.');
            let deleteLinks = document.getElementsByClassName('trn-delete-team-request-link');
            Array.prototype.forEach.call(deleteLinks,  function(deleteLink) {
                console.log('add');
                deleteLink.addEventListener('click', handleDeleteClick);
            });
        }

        function removeListeners() {
            console.log('removing handlers for team requests.');
            let deleteLinks = document.getElementsByClassName('trn-decline-team-request-link');
            Array.prototype.forEach.call(deleteLinks, function(deleteLink) {
                console.log('remove');
                deleteLink.removeEventListener('click', handleDeleteClick);
            });
        }

        function getTeamRequests() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + 'team-requests/?_embed&' + $.param({user_id: options.user_id}));
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                //console.log(xhr);
                let content = ``;
                if (xhr.status === 200) {
                    let requests = JSON.parse(xhr.response);

                    if ( requests !== null && requests.length > 0 ) {
                        content += `<ul class="list-unstyled" id="trn-my-team-requests-list">`;

                        Array.prototype.forEach.call(requests, function(request) {
                            content += `<li class="text-center" id="trn-join-team-request-${request.team_member_request_id}">`;
                            content += `<a href="${request._embedded.team[0].link}">${request._embedded.team[0].name}</a> `;
                            content += `<a class="trn-delete-team-request-link" data-request-id="${request.team_member_request_id}"><i class="fa fa-times text-danger"></i></a>`;
                            content += `</li>`;
                        });

                        content += `</ul>`;
                    } else {
                        content += `<p class="text-center">${options.language.zero_requests}</p>`;
                    }
                } else {
                    content += `<p class="text-center">${options.language.error}</p>`;
                }

                removeListeners();
                document.getElementById('trn-my-team-requests-response').nextSibling.remove();
                document.getElementById('trn-my-team-requests-section').innerHTML += content;
                addListeners();
            };

            xhr.send();
        }
        getTeamRequests();
    }, false);
})(trn);