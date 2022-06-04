/**
 * Match details form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.20.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    const options = trn_match_details_options;

    const one_result = document.getElementById(`one_result`);
    const two_result = document.getElementById(`two_result`);
    const two_result_string = document.getElementById(`two_result_string`);

    // Make sure we get valid data for the second field.
    function toggleTwoResult() {
        if (one_result.value === 'won') {
            two_result.value = 'lost';
            two_result_string.innerHTML = options.lost;
        } else if (one_result.value === 'lost') {
            two_result.value = 'won';
            two_result_string.innerHTML = options.won;
        } else {
            two_result.value = 'draw';
            two_result_string.innerHTML = options.draw;
        }
    }

    one_result.addEventListener('change', () => {
        toggleTwoResult();
    });

    toggleTwoResult();
})(trn);