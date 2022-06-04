/**
 * Admin seed tournament form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.20.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function($){
    'use strict';

    const seeds = document.getElementsByClassName('seed-spot');
    Array.prototype.forEach.call(seeds, function(seed) {
        seed.dataset.previous = seed.value;

        seed.addEventListener('change', function() {
            const changedSpot = this;

            changedSpot.classList.remove(`danger`);
            changedSpot.classList.add(`success`);

            Array.prototype.forEach.call(seeds, function(clearSeed) {
                if ((changedSpot !== clearSeed) && (changedSpot.value === clearSeed.value)) {
                    clearSeed.value = '';
                    clearSeed.classList.remove(`success`);
                    clearSeed.classList.add(`danger`);
                    clearSeed.dataset.previous = '';
                }
            });

            const competitorId = `competitor_${changedSpot.value}`;
            const competitorSeed = document.getElementById(competitorId);

            if ( null !== competitorSeed ) {
                competitorSeed.classList.remove(`unused`);
                competitorSeed.classList.add(`used`);
            }

            const previousId = `competitor_${changedSpot.dataset.previous}`;
            const previousSeed = document.getElementById(previousId);

            if ( null !== previousSeed ) {
                previousSeed.classList.remove(`used`);
                previousSeed.classList.add(`unused`);
            }

            changedSpot.dataset.previous = changedSpot.value;
        });

    }, false);
}( trn ));