/**
 * Tournament list page.
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
        const filters = document.querySelectorAll('.tournament-filter li');
        const anchors = document.querySelectorAll('.tournament-filter li a');
        const tournaments = document.querySelectorAll('.tournament');

        const filterBy = (event) => {
            const filterItem = event.currentTarget;
            const filter = filterItem.dataset.filter;
            const anchor = filterItem.querySelector('li a');

            Array.prototype.forEach.call(anchors, (anchor) => {
                anchor.classList.remove('tournamatch-nav-active')
            });
            anchor.classList.add('tournamatch-nav-active');

            Array.prototype.forEach.call(tournaments, (tournament) => {
                if ('all' === filter) {
                    tournament.style.display = 'block';
                } else if (filter === tournament.dataset.filter) {
                    tournament.style.display = 'block';
                } else {
                    tournament.style.display = 'none';
                }
            });
        };

        Array.prototype.forEach.call(filters, (filter) => {
            filter.addEventListener('click', filterBy);
        });
    }, false);

}( trn ));