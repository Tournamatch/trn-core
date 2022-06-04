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
                    thumbnail: document.getElementById('trn-game-thumbnail').value
                }));
            }
        }, false);

        let upload_form = document.getElementById('trn-upload-game-image-form');
        upload_form.addEventListener('submit', function (event) {
            event.preventDefault();

            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'game-images/');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                if (xhr.status === 201) {
                    $.event('game-images').dispatchEvent(new Event('changed'));
                    document.getElementById('trn-upload-game-image-response').innerHTML = `<p class="notice notice-success"><strong>${options.language.success}:</strong> ${options.language.upload_success_message}</p>`;
                    upload_form.reset();
                } else {
                    console.log(xhr.response);
                    document.getElementById('trn-upload-game-image-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${response.message}</p>`;
                }
            };

            xhr.send(new FormData(upload_form));
        }, false);

        // Populate the games list.
        function getGameImages() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + 'game-images/');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                if (xhr.status === 200) {
                    let select = document.getElementById( 'trn-game-thumbnail' );
                    let images = JSON.parse(xhr.response);

                    if (images !== null && images.length > 0) {
                        while (select.options.length > 0) select.remove(0);
                        for (let i = 0; i < images.length; i++) {
                            let newImageOption = new Option( images[i], images[i] );
                            select.add(newImageOption, undefined);
                        }
                    }
                }
            };

            xhr.send();
        }

        $.event('game-images').addEventListener('changed', function() {
            getGameImages();
        });

    }, false);
})(trn);