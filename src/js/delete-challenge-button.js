/**
 * Handles the click event for deleting a challenge.
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
        let options = trn_delete_challenge_button_options;

        let targets = document.getElementsByClassName('trn-delete-challenge-button');
        Array.prototype.forEach.call(targets, function (target) {
            target.addEventListener('trn.confirmed.action.delete-challenge', function (event) {
                event.preventDefault();

                console.log(`modal was confirmed for link ${target.dataset.challengeId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', `${options.api_url}challenges/${target.dataset.challengeId}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        $.event('challenge').dispatchEvent(new CustomEvent('deleted', { detail: { challenge_id: target.dataset.challengeId } } ));
                    } else {
                        $.event('challenge').dispatchEvent(new CustomEvent('error', { detail: xhr.response } ));
                    }
                };

                xhr.send();
            });
        }, false);
    }, false);
})(trn);