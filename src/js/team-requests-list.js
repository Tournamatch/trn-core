/**
 * Handles events for the list that displays requests to join a team.
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
        const options = trn_team_requests_list_options;

        function acceptTeamRequest(request_id) {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'team-requests/' + request_id + '/accept');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                let response = JSON.parse(xhr.response);
                if (xhr.status === 200) {
                    getTeamRequests();
                    $.event('team-members').dispatchEvent(new Event('changed'));
                } else {
                    console.log(xhr.response);
                    document.getElementById('trn-team-requests-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}:</strong> ${response.message}</div>`;
                }
            };

            xhr.send($.param({
                request_id: request_id
            }));
        }

        function declineTeamRequest(request_id) {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'team-requests/' + request_id + '/decline');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    getTeamRequests();
                } else {
                    console.log(xhr.response);
                    // display error somewhere
                }
            };

            xhr.send($.param({
                request_id: request_id
            }));
        }

        function handleAcceptClick(event) {
            acceptTeamRequest(this.dataset.requestId)
        }

        function handleDeclineClick(event) {
            declineTeamRequest(this.dataset.requestId)
        }

        // Accept join team request links.
        function addListeners() {
            let acceptLinks = document.getElementsByClassName('trn-accept-team-request-link');
            Array.prototype.forEach.call(acceptLinks,  function(acceptLink) {
                acceptLink.addEventListener('click', handleAcceptClick);
            });

            let declineLinks = document.getElementsByClassName('trn-decline-team-request-link');
            Array.prototype.forEach.call(declineLinks, function(declineLink) {
                declineLink.addEventListener('click', handleDeclineClick);
            });
        }

        function removeListeners() {
            let acceptLinks = document.getElementsByClassName('trn-accept-team-request-link');
            Array.prototype.forEach.call(acceptLinks,  function(acceptLink) {
                acceptLink.removeEventListener('click', handleAcceptClick);
            });

            let declineLinks = document.getElementsByClassName('trn-decline-team-request-link');
            Array.prototype.forEach.call(declineLinks, function(declineLink) {
                declineLink.removeEventListener('click', handleDeclineClick);
            });
        }

        function getTeamRequests() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + 'team-requests/?_embed&' + $.param({team_id: options.team_id}));
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                // console.log(xhr);

                let content = '';
                if (xhr.status === 200) {
                    let requests = JSON.parse(xhr.response);

                    if ( requests !== null && requests.length > 0) {
                        content += `<ul class="list-unstyled" id="trn-team-requests-list">`;

                        Array.prototype.forEach.call(requests, function(request) {
                            content += `<li class="text-center" id="trn-join-team-request-${request.team_member_request_id}">`;
                            content += `<a href="${request._embedded.player[0].link}">${request._embedded.player[0].name}</a> `;
                            content += `<a class="trn-accept-team-request-link" data-request-id="${request.team_member_request_id}"><i class="fa fa-check text-success"></i></a> `;
                            content += `<a class="trn-decline-team-request-link" data-request-id="${request.team_member_request_id}"><i class="fa fa-times text-danger"></i></a>`;
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
                document.getElementById('trn-team-requests-section-header').nextSibling.remove();
                document.getElementById('trn-team-requests-section').innerHTML += content;
                addListeners();
            };

            xhr.send();
        }
        getTeamRequests();
    }, false);
})(trn);