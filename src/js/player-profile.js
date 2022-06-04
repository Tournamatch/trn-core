/**
 * Player profile page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.25.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function($){
    'use strict';

    window.addEventListener('load', function () {
        $.tabs(document.getElementById('tournamatch-player-views'));
    }, false);

}( trn ));
