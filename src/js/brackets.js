/**
 * Handles rendering the content for tournament brackets.
 *
 * @link       https://www.tournamatch.com
 * @since      4.0.0
 *
 * @package    Tournamatch
 *
 */
(function () {
    'use strict';

    const options = trn_brackets_options;

    function get_competitors(tournament_id) {
        return fetch(`${options.site_url}/wp-json/tournamatch/v1/tournament-competitors/?tournament_id=${tournament_id}&_embed`, {
            headers: {"Content-Type": "application/json; charset=utf-8"},
        })
            .then(response => response.json());
    }

    function get_matches(tournament_id) {
        return fetch(`${options.site_url}/wp-json/tournamatch/v1/matches/?competition_type=tournaments&competition_id=${tournament_id}&_embed`, {
            headers: {"Content-Type": "application/json; charset=utf-8"},
        })
            .then(response => response.json());
    }

    function clear(tournament_id, match_id) {
        return fetch(`${options.site_url}/wp-json/tournamatch/v1/matches/clear`, {
            headers: {
                "Content-Type": "application/json; charset=utf-8",
                "X-WP-Nonce": options.rest_nonce,
            },
            method: 'POST',
            body: JSON.stringify({
                id: match_id,
                tournament_id: tournament_id,
            })
        })
            .then(response => response.json());
    }

    function advance(tournament_id, match_id, winner_id) {
        return fetch(`${options.site_url}/wp-json/tournamatch/v1/matches/advance`, {
            headers: {
                "Content-Type": "application/json; charset=utf-8",
                "X-WP-Nonce": options.rest_nonce,
            },
            method: 'POST',
            body: JSON.stringify({
                id: match_id,
                tournament_id: tournament_id,
                winner_id: winner_id,
            })
        })
            .then(response => response.json());
    }

    window.addEventListener(
        'load',
        function () {

            function competitorMouseOver(event) {
                const className = `competitor-${event.target.dataset.competitorId}`;
                Array.from(document.getElementsByClassName(className))
                    .forEach(
                        item => {
                            item.classList.add('tournamatch-competitor-highlight');
                        }
                    );
            }

            function competitorMouseLeave(event) {
                const className = `competitor-${event.target.dataset.competitorId}`;
                Array.from(document.getElementsByClassName(className))
                    .forEach(
                        item => {
                            item.classList.remove('tournamatch-competitor-highlight');
                        }
                    );
            }

            function calculateProgress(tournament) {
                const totalGames = tournament.size - 1;
                let finishedGames = 0;

                for (let i = 1; i <= tournament.size - 1; i++) {
                    if (tournament.matches[i]) {
                        if (tournament.matches[i].match_status === 'confirmed') finishedGames++;
                    }
                }
                return (finishedGames / totalGames);
            }

            function renderProgress(float) {
                return `<div class="tournamatch-progress" style="width: ${100 * float}%;">&nbsp;</div> `;
            }

            function renderDropDown(tournament, tournament_id, spot_id) {
                let content = ``;
                const is_first_round = spot_id < (tournament.size / 2);

                if (tournament.matches[spot_id] && ((tournament.matches[spot_id].one_competitor_id !== null) || (tournament.matches[spot_id].two_competitor_id !== null))) {
                    const match_id = tournament.matches[spot_id].match_id;
                    content += `<div class="dropdown">`;
                    content += `<span class="more-details dashicons dashicons-admin-generic"></span>`;
                    content += `<div class="dropdown-content" >`;
                    if (tournament.matches[spot_id] && tournament.matches[spot_id].one_competitor_id !== null && tournament.matches[spot_id].one_competitor_id !== 0) {
                        const one_id = tournament.matches[spot_id].one_competitor_id;
                        const advance_url = options.advance_url.replace('{ID}', match_id).replace('{WINNER_ID}', one_id).replace('{NONCE}', options.advance_nonce);
                        const replace_url = options.replace_url.replace('{TOURNAMENT_ID}', tournament_id).replace('{MATCH_ID}', match_id).replace('{COMPETITOR_ID}', one_id).replace('{NONCE}', options.replace_nonce);
                        content += `<a href="${advance_url}" class="advance-competitor" data-tournament-id="${tournament_id}" data-match-id="${spot_id}" data-competitor-id="${one_id}">${options.language.advance.replace('{NAME}', tournament.competitors[one_id].name)}</a>`;
                        content += `<a href="${replace_url}" class="replace-competitor" data-tournament-id="${tournament_id}" data-match-id="${spot_id}" data-competitor-id="${one_id}">${options.language.replace.replace('{NAME}', tournament.competitors[one_id].name)}</a>`;
                    }
                    if (tournament.matches[spot_id] && tournament.matches[spot_id].two_competitor_id !== null && tournament.matches[spot_id].two_competitor_id !== 0) {
                        const two_id = tournament.matches[spot_id].two_competitor_id;
                        const advance_url = options.advance_url.replace('{ID}', match_id).replace('{WINNER_ID}', two_id).replace('{NONCE}', options.advance_nonce);
                        const replace_url = options.replace_url.replace('{TOURNAMENT_ID}', tournament_id).replace('{MATCH_ID}', match_id).replace('{COMPETITOR_ID}', two_id).replace('{NONCE}', options.replace_nonce);
                        content += `<a href="${advance_url}" class="advance-competitor" data-tournament-id="${tournament_id}" data-match-id="${spot_id}" data-competitor-id="${two_id}">${options.language.advance.replace('{NAME}', tournament.competitors[two_id].name)}</a>`;
                        content += `<a href="${replace_url}" class="replace-competitor" data-tournament-id="${tournament_id}" data-match-id="${spot_id}" data-competitor-id="${two_id}">${options.language.replace.replace('{NAME}', tournament.competitors[two_id].name)}</a>`;
                    }
                    if ( !is_first_round) {
                        content += `<a href="#" class="clear-competitors" data-tournament-id="${tournament_id}" data-match-id="${spot_id}">${options.language.clear}</a>`;

                    }
                    content += `</div>`;
                    content += `</div>`;
                }

                return content;
            }

            function renderMatch(tournament, tournament_id, match_id, flow, can_edit_matches) {
                let content = ``;
                content += `<div class="tournamatch-match">`;
                content += `<div class="horizontal-line"></div>`;
                content += `<div class="tournamatch-match-body">`;

                if (tournament.matches[match_id] && tournament.matches[match_id].one_competitor_id !== null) {
                    const one_id = tournament.matches[match_id].one_competitor_id;
                    const one_name = tournament.competitors[one_id] ? tournament.competitors[one_id].name : '&nbsp;';
                    const one_url = tournament.competitors[one_id] ? `${options.site_url}/${tournament.competitors[one_id].competitor_type}/${one_id}` : "#";
                    content += `<span id="trn_spot_${match_id}_one" class="tournamatch-competitor competitor-${one_id}" data-competitor-id="${one_id}"><a href="${one_url}">${one_name}</a></span>`;
                } else {
                    content += `<span id="trn_spot_${match_id}_one" class="tournamatch-competitor">&nbsp;</span>`;
                }

                if (tournament.matches[match_id] && tournament.matches[match_id].two_competitor_id !== null) {
                    const two_id = tournament.matches[match_id].two_competitor_id;
                    const two_name = tournament.competitors[two_id] ? tournament.competitors[two_id].name : '&nbsp;';
                    const two_url = tournament.competitors[two_id] ? `${options.site_url}/${tournament.competitors[two_id].competitor_type}/${two_id}` : "#";
                    content += `<span id="trn_spot_${match_id}_two" class="tournamatch-competitor competitor-${two_id}" data-competitor-id="${two_id}"><a href="${two_url}">${two_name}</a></span>`;
                } else {
                    content += `<span id="trn_spot_${match_id}_two" class="tournamatch-competitor">&nbsp;</span>`;
                }

                content += `</div>`;

                if (flow) {
                    if (0 === match_id % 2) {
                        content += `<div class="bottom-half">`;
                    } else {
                        content += `<div class="top-half">`;
                    }

                    if (can_edit_matches) {
                        content += renderDropDown(tournament, tournament_id, match_id);
                    }

                    content += `</div>`;
                }
                content += `</div>`;

                return content;
            }

            function renderBrackets(tournament, container, tournament_id) {
                let content = ``;
                let numberOfGames;
                let matchPaddingCount;

                container.dataset.trnTotalRounds = tournament.rounds;

                content += `<div class="tournamatch-round-header-container">`;
                for (let i = 0; i <= tournament.rounds; i++) {
                    content += `<span class="tournamatch-round-header">${options.language.rounds[i]}</span>`;
                }
                content += `</div>`;
                content += renderProgress(calculateProgress(tournament));

                content += `<div class="tournamatch-round-body-container">`;
                let spot = 1;
                let sumOfGames = 0;
                for (let round = 1; round <= tournament.rounds; round++) {
                    numberOfGames = Math.ceil(tournament.size / (Math.pow(2, round)));
                    matchPaddingCount = Math.pow(2, round) - 1;

                    content += `<div class="tournamatch-round-body">`;

                    for (spot; spot <= (numberOfGames + sumOfGames); spot++) {
                        for (let padding = 0; padding < matchPaddingCount; padding++) {
                            if (1 === spot % 2) {
                                content += `<div class="match-half">&nbsp;</div> `;
                            } else {
                                content += `<div class="vertical-line">&nbsp;</div> `;
                            }
                        }
                        content += renderMatch(tournament, tournament_id, spot, round !== tournament.rounds, options.can_edit_matches);
                        for (let padding = 0; padding < matchPaddingCount; padding++) {
                            if ((round !== tournament.rounds) && (1 === spot % 2)) {
                                content += `<div class="vertical-line">&nbsp;</div> `;
                            } else {
                                content += `<div class="match-half">&nbsp;</div> `;
                            }
                        }
                    }
                    content += `</div>`;
                    sumOfGames += numberOfGames;
                }

                // Display the last winner's spot.
                content += `<div class="tournamatch-round-body">`;
                for (let padding = 0; padding < matchPaddingCount; padding++) {
                    content += `<div class="match-half">&nbsp;</div> `;
                }
                content += `<div class="tournamatch-match">`;
                content += `<div class="winners-line">`;
                if (options.can_edit_matches) {
                    content += renderDropDown(tournament, tournament_id, spot - 1);
                }
                content += `</div>`;
                content += `<div class="tournamatch-match-body">`;
                content += `<span class="tournamatch-competitor"><strong>${options.language.winner}</strong></span>`;
                if (tournament.matches[spot - 1] && tournament.matches[spot - 1].match_status === 'confirmed') {
                //if (tournament.matches[spot] && tournament.matches[spot].one_competitor_id !== null) {
                    const winner_id = tournament.matches[spot -1].one_result === 'won' ? tournament.matches[spot -1].one_competitor_id : tournament.matches[spot -1].two_competitor_id;
                    content += `<span class="tournamatch-competitor competitor-${winner_id}" data-competitor-id="${winner_id}">${tournament.competitors[winner_id].name}</span>`;
                } else {
                    content += `<span class="tournamatch-competitor">&nbsp;</span>`;
                }
                content += `</div>`;
                content += `</div>`;
                for (let padding = 0; padding < matchPaddingCount; padding++) {
                    content += `<div class="match-half">&nbsp;</div> `;
                }
                content += `</div>`;
                // End of display last winner's spot.

                content += `</div>`;

                container.innerHTML = content;

                Array.from(document.getElementsByClassName('tournamatch-competitor'))
                    .forEach(
                        (item) => {
                            item.addEventListener('mouseover', competitorMouseOver);
                            item.addEventListener('mouseleave', competitorMouseLeave);
                        }
                    );

                // Array.from(document.getElementsByClassName('advance-competitor'))
                //     .forEach(
                //         (item) => {
                //             item.addEventListener('click', (e) => {
                //                 e.preventDefault();
                //                 advance(e.target.dataset.tournamentId, e.target.dataset.matchId, e.target.dataset.competitorId)
                //                     .then(() => {
                //                         location.reload();
                //                     });
                //             });
                //         }
                //     );
                //
                // Array.from(document.getElementsByClassName('clear-competitors'))
                //     .forEach(
                //         (item) => {
                //             item.addEventListener('click', (e) => {
                //                 e.preventDefault();
                //                 clear(e.target.dataset.tournamentId, e.target.dataset.matchId)
                //                     .then(() => {
                //                         location.reload();
                //                     });
                //             });
                //         }
                //     );
            }

            Array.from(document.getElementsByClassName('tournamatch-brackets'))
                .forEach(
                    (item) => {
                        const tournamentId = item.dataset.tournamentId;
                        const tournamentSize = item.dataset.tournamentSize;

                        Promise.all([get_matches(tournamentId), get_competitors(tournamentId)])
                            .then(([matches, competitors]) => {
                                const rounds = Math.round(Math.log(tournamentSize) / Math.log(2));

                                console.log(competitors);
                                competitors = competitors.reduce((competitors, competitor) => (
                                        competitor.name = competitor._embedded.competitor[0].name,
                                        competitors[competitor.competitor_id] = competitor,
                                        competitors
                                ), {});
                                console.log(competitors);

                                console.log(matches);
                                matches = matches.reduce((matches, match) => (matches[match.spot] = match, matches), {});
                                console.log(matches);

                                const tournament = {
                                    matches: matches,
                                    competitors: competitors,
                                    rounds: rounds,
                                    size: tournamentSize,
                                };

                                console.log(tournament);

                                renderBrackets(tournament, item, tournamentId);
                            });
                    }
                );

        },
        false
    );
})();
