/**
 * Handles events for the tournament list that displays in the upcoming tournaments shortcode.
 *
 * @link       https://www.tournamatch.com
 * @since      3.13.0
  *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const options = trn_upcoming_tournament_list_options;
        let start = 0;

        function handleNextClick(event) {
            start += options.paginate;
            getUpcomingTournaments();
        }
        function handlePreviousClick(event) {
            start = Math.max(0, start - options.paginate);
            getUpcomingTournaments();
        }

        // Accept join team invitation links.
        function addListeners() {
            let nextButton = document.getElementById('trn-upcoming-tournaments-next-button');
            let previousButton = document.getElementById('trn-upcoming-tournaments-previous-button');

            if (nextButton) {
                nextButton.addEventListener('click', handleNextClick);
            }
            if (previousButton) {
                previousButton.addEventListener('click', handlePreviousClick);
            }
        }

        function removeListeners() {
            let nextButton = document.getElementById('trn-upcoming-tournaments-next-button');
            let previousButton = document.getElementById('trn-upcoming-tournaments-previous-button');

            if (nextButton) {
                nextButton.removeEventListener('click', handleNextClick);
            }
            if (previousButton) {
                previousButton.removeEventListener('click', handlePreviousClick);
            }
        }

        function getUpcomingTournaments() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + 'tournaments/?' + $.param({game_id: options.game_id, start: start, length: options.paginate}));
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                //console.log(xhr.response);
                let content = ``;
                if (xhr.status === 200) {
                    let tournaments = JSON.parse(xhr.response);

                    if ( tournaments !== null && tournaments.length > 0 ) {
                        content += `<div class="items-wrapper"`;

                        Array.prototype.forEach.call(tournaments, function(tournament) {

                            content += `<div class="item-wrapper">`;
                            content += `  <div class="item-avatar">`;
                            content += `    <a href="${tournament.link}" title="${options.language.view_tournament_info}">`;
                            content += `      <img src="${tournament.avatar}" alt="${tournament.game}">`;
                            content += `    </a>`;
                            content += `  </div>`;
                            content += `  <div class="item-info">`;
                            content += `    <span class="item-title">${tournament.name}</span>`;
                            content += `    <span class="item-meta">${tournament.start_date}</span>`;
                            content += `    <span class="item-meta">`;
                            if ( '1' === tournament.elimination_mode ) {
                                content += `    <span class="item-meta">${options.language.one_loss}</span>`;
                            } else {
                                content += `    <span class="item-meta">${options.language.double_elimination}</span>`;
                            }
                            content += `    </span>`;
                            content += `    <span class="item-meta">`;
                            if ( '0' === tournament.from_ladder ) {
                                content += `<a href="${tournament.registered_link}">${tournament.competitors}</a>/`;
                                if ( 0 < tournament.bracket_size ) {
                                    content += `${tournament.bracket_size}`;
                                } else {
                                    content += `&infin;`;
                                }
                            } else {
                                content += `<a href="${tournament.current_seeding_link}">${options.language.current_seeding}</a>`;
                            }
                            content += `    </span>`;
                            content += `    <ul class="list-inline">`;
                            content += `      <li class="list-inline-item"><a href="${tournament.link}" class="trn-button trn-button-sm">${options.language.more_info}</a></li>`;
                            content += `      <li class="list-inline-item"><a href="${tournament.register_link}" class="trn-button trn-button-sm" >${options.language.register}</a></li>`;
                            content += `    </ul>`;
                            content += `  </div>`;
                            content += `  <div class="trn-clearfix"></div>`;
                            content += `</div>`;
                        });

                        content += `</div>`;
                        content += `<div id="trn-upcoming-tournaments-buttons">`;
                        content += `<button id="trn-upcoming-tournaments-previous-button">&#60;</button>`;
                        content += `<button id="trn-upcoming-tournaments-next-button">&#62;</button>`;
                        content += `</div>`;
                    } else {
                        content += `<p class="trn-text-center">${options.language.zero_tournaments}</p>`;
                    }
                } else {
                    content += `<p class="trn-text-center">${options.language.error}</p>`;
                }

                removeListeners();
                document.getElementById('trn-tournament-list-shortcode').innerHTML = content;
                addListeners();
            };

            xhr.send();
        }

        getUpcomingTournaments();
    }, false);
})(trn);