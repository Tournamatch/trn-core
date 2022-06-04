/**
 * Handles the click event for accepting a challenge.
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
        let options = trn_accept_challenge_button_options;

        let targets = document.getElementsByClassName('trn-accept-challenge-button');
        Array.prototype.forEach.call(targets, function (target) {
            target.addEventListener('click', function (event) {
                event.preventDefault();

                console.log(`accept challenge was clicked for link ${target.dataset.challengeId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('POST', `${options.api_url}challenges/${target.dataset.challengeId}/accept`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        $.event('challenge').dispatchEvent(new CustomEvent('accepted', { detail: target.dataset.challengeId } ));
                    } else {
                        $.event('challenge').dispatchEvent(new CustomEvent('error', { detail: xhr.response } ));
                    }
                };

                xhr.send();
            });
        }, false);
    }, false);
})(trn);