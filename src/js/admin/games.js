/**
 * Admin manage games.
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
        let options = trn_manage_games_options;

        let form = document.getElementById('trn-new-game-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (form.checkValidity() === true) {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', options.api_url + 'games/');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    console.log(xhr);
                    if (xhr.status === 201) {
                        let new_game = JSON.parse(xhr.response);
                        $.event('games').dispatchEvent(new Event('changed'));
                        document.getElementById('trn-create-game-response').innerHTML = `<p class="notice notice-success"><strong>${options.language.success}:</strong> ${options.language.success_message.format(new_game.name)}</p>`;
                        form.reset();
                    } else {
                        console.log(xhr.response);
                        document.getElementById('trn-create-game-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${response.message}</p>`;
                    }
                };

                xhr.send($.param({
                    name: document.getElementById('trn-game-name').value,
                    platform: document.getElementById('trn-game-platform').value,
                    thumbnail_id: document.getElementById('trn-game-thumbnail').value || 0,
                    banner_id: document.getElementById('trn-game-banner').value || 0,
                }));
            }
        }, false);


        let old_games = document.getElementsByClassName('trn-admin-game-warning');
        if (old_games && ( 0 < old_games.length)) {
            document.getElementById('trn-game-thumbnail-warning').classList.remove('d-none');
        } else {
            document.getElementById('trn-game-thumbnail-warning').classList.add('d-none');
        }
        Array.prototype.forEach.call(old_games, function(old_game) {
            old_game.parentNode.classList.add('trn-admin-game-thumbnail-warning');
        });
    }, false);
})(trn);