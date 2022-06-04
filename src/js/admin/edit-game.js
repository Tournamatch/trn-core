/**
 * Admin edit game.
 *
 * @link       https://www.tournamatch.com
 * @since      3.18.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './../tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let options = trn_edit_game_options;

         let form = document.getElementById('trn-edit-game-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (form.checkValidity() === true) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', options.api_url + 'games/' + options.id);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    console.log(xhr);
                    if (xhr.status === 200) {
                        document.getElementById('trn-edit-game-response').innerHTML = `<p class="notice notice-success"><strong>${options.language.success}:</strong> ${options.language.update_message}</p>`;
                    } else {
                        console.log(xhr.response);
                        document.getElementById('trn-edit-game-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${response.message}</p>`;
                    }
                };

                xhr.send($.param({
                    name: document.getElementById('trn-game-name').value,
                    platform: document.getElementById('trn-game-platform').value,
                    thumbnail: document.getElementById('trn-game-thumbnail').value
                }));
            }
        }, false);

    }, false);
})(trn);