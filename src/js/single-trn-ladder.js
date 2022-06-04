/**
 * Single Ladder page.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        trn.tabs(document.getElementById('tournamatch-ladder-views'));
    }, false);
})(trn);