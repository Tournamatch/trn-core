/**
 * Admin match competitors form.
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

    const options = trn_match_competitors_options;
    const form = document.getElementById('trn-select-competitors');
    const one_id = document.getElementById('one_id');
    const two_id = document.getElementById('two_id');

    form.addEventListener('submit', (e) => {
        if (one_id.value === two_id.value) {
            two_id.setCustomValidity(options.unique_message);
            form.reportValidity();
            e.preventDefault();
        }

        console.log(one_id.value);
        console.log(two_id.value);
    });

    function removeCustomValidation() {
        two_id.setCustomValidity('');
    }

    one_id.addEventListener('change', removeCustomValidation);
    two_id.addEventListener('change', removeCustomValidation);

})(trn);