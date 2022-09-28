/**
 * Handles the leave ladder action.
 *
 * @link       https://www.tournamatch.com
 * @since      3.26.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    let options = trn_leave_ladder_options;

    window.addEventListener('load', function () {
        let leaveLinks = document.getElementsByClassName('trn-leave-ladder-link');
        Array.prototype.forEach.call(leaveLinks, function (link) {
            link.addEventListener('trn.confirmed.action.leave-ladder', function (event) {
                event.preventDefault();

                console.log(`modal was confirmed for link ${link.dataset.competitorId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', `${options.api_url}ladder-competitors/${link.dataset.competitorId}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        window.location.reload();
                    } else {
                        let response = JSON.parse(xhr.response);
                        document.getElementById('trn-remove-competitor-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send();
            }, false);
        });
    }, false);
})(trn);