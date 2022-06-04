/**
 * Single challenge page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 * @since      3.15.0 Updated to handle async events from shortcodes.
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let options = trn_single_challenge_options;

        function removeAcceptButton() {
            let targets = document.getElementsByClassName('trn-accept-challenge-button');
            Array.prototype.forEach.call(targets, function (target) {
                target.parentNode.removeChild(target);
            }, false);
        }

        function removeDeclineButton() {
            let targets = document.getElementsByClassName('trn-decline-challenge-button');
            Array.prototype.forEach.call(targets, function (target) {
                target.parentNode.removeChild(target);
            }, false);
        }

        $.event('challenge').addEventListener('deleted', function(event) {
            window.location.href = options.challenge_list_link;
        });

        $.event('challenge').addEventListener('accepted', function(event) {
            removeAcceptButton();
            removeDeclineButton();
            document.getElementById('trn-challenge-status').innerText = options.language.accepted;
            document.getElementById('trn-challenge-success-response').innerHTML = `<div class="alert alert-success"><strong>${options.language.success}</strong>: ${options.language.acceptedMessage}</div>`;

            let xhr = new XMLHttpRequest();
            xhr.open('GET', `${options.api_url}challenges/${event.detail}/?_embed`);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const challenge = JSON.parse(xhr.response);
                    document.getElementById('trn-challenge-challenger').innerText = challenge._embedded.challenger[0].name;
                    document.getElementById('trn-challenge-challengee').innerText = challenge._embedded.challengee[0].name;
                }
            };
            xhr.send();
        });

        $.event('challenge').addEventListener('declined', function(event) {
            removeAcceptButton();
            removeDeclineButton();
            document.getElementById('trn-challenge-status').innerText = options.language.declined;
            document.getElementById('trn-challenge-success-response').innerHTML = `<div class="alert alert-success"><strong>${options.language.success}</strong>: ${options.language.declinedMessage}</div>`;
        });

        $.event('challenge').addEventListener('error', function(event) {
            let response = JSON.parse(event.detail);
            document.getElementById('trn-challenge-failure-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
        });

    }, false);
})(trn);