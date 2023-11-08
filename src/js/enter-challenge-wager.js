/**
 * Handles the wager field on the challenge form
 *
 * @link       https://www.tournamatch.com
 * @since      4.5.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const wagerGroup = document.getElementById('trn-challenge-form-mycred-wager-group');
        const wagerField = document.getElementById('mycred_wager_amount');

        $.event('challenge-builder').addEventListener('changed', function(e) {
            const challengeBuilder = e.detail;

            if ( 'enabled' === challengeBuilder.mycred_wager ) {
                wagerGroup.classList.remove('d-none');
                wagerField.removeAttribute('disabled');
            } else {
                wagerGroup.classList.add('d-none');
                wagerField.setAttribute('disabled', "");
            }
        });

    }, false);
})(trn);