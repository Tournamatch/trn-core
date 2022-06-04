/**
 * Confirm a match result.
 *
 * @link       https://www.tournamatch.com
 * @since      3.19.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let options = trn_confirm_match_options;
        let form = document.getElementById('trn-confirm-match-form');

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'matches/' + options.match_id);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                if (xhr.status === 200) {
                    window.location.href = options.redirect_link + `?confirmed_match=${options.match_id}`;
                } else {
                    console.log(xhr.response);
                }
            };

            let prefix = ('one' === options.side_to_confirm) ? 'one' : 'two';
            let data = [];

            data[`${prefix}_result`] = options.result_to_confirm;
            data[`${prefix}_comment`] = document.getElementById('comment').value;

            console.log(data);
            xhr.send($.param(data));
        }, false);
    }, false);
})(trn);
