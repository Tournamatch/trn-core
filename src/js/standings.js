/**
 * Ladder standings page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    let options = trn_ladder_standings_options;

    function handlePromoteLink() {
        let promoteLinks = document.getElementsByClassName('trn-promote-competitor-link');
        Array.prototype.forEach.call(promoteLinks, function (promoteLink) {
            promoteLink.addEventListener('click', function (event) {
                event.preventDefault();

                let xhr = new XMLHttpRequest();
                xhr.open('POST', `${options.api_url}ladder-competitors/${promoteLink.dataset.competitorId}/promote`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 303) {
                        window.location.reload();
                    } else {
                        let response = JSON.parse(xhr.response);
                        document.getElementById('trn-promote-competitor-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send();
            }, false);
        });
    }

    function handleDeleteConfirm() {
        let links = document.getElementsByClassName('trn-remove-competitor-link');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('trn.confirmed.action', function (event) {
                event.preventDefault();

                console.log(`modal was confirmed for link ${link.dataset.competitorId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', `${options.api_url}ladder-competitors/${link.dataset.competitorId}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        window.location.reload();
                    } else {
                        let response = JSON.parse(xhr.response);
                        document.getElementById('trn-remove-competitor-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send();
            }, false);
        });
    }

    window.addEventListener('load', function () {
        document.addEventListener('trn-html-updated', function (e) {
            handleDeleteConfirm();
            handlePromoteLink();
        });
        handleDeleteConfirm();
        handlePromoteLink();

        let default_target = options.default_target;
        let target = 0;
        let columnDefs = [
            {
                targets: target++,
                name: 'number',
                className: 'trn-ladder-standings-table-number',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
            },
            {
                targets: target++,
                name: 'name',
                className: 'trn-ladder-standings-table-name',
                render: function (data, type, row) {
                    return `<img src="${options.flag_path}${row._embedded.competitor[0].flag}" width="18" height="12" title="${row._embedded.competitor[0].flag}"> <a href="${row._embedded.competitor[0].link}">${row._embedded.competitor[0].name}</a>`;
                },
            },
            {
                targets: target++,
                name: default_target,
                className: 'trn-ladder-standings-table-rating rating',
                render: function (data, type, row) {
                    return row[default_target];
                },
            },
            {
                targets: target++,
                name: 'games_played',
                className: 'trn-ladder-standings-table-games-played',
                render: function (data, type, row) {
                    return row.games_played;
                },
            },
            {
                targets: target++,
                name: 'wins',
                className: 'trn-ladder-standings-table-wins wins',
                render: function (data, type, row) {
                    return row.wins;
                },
            },
            {
                targets: target++,
                name: 'losses',
                className: 'trn-ladder-standings-table-losses losses',
                render: function (data, type, row) {
                    return row.losses;
                },
            },
        ];

        if (options.uses_draws) {
            columnDefs.push({
                targets: target++,
                name: 'draws',
                className: 'trn-ladder-standings-table-draws ties',
                render: function (data, type, row) {
                    return row.draws;
                },
            });
        }

        if (options.uses_scores) {
            columnDefs.push(
                {
                    targets: target++,
                    name: 'goals_for',
                    className: 'trn-ladder-standings-table-goals-for',
                    render: function (data, type, row) {
                        return row.goals_for;
                    },
                },
                {
                    targets: target++,
                    name: 'goals_against',
                    className: 'trn-ladder-standings-table-goals-against',
                    render: function (data, type, row) {
                        return row.goals_against;
                    },
                },
                {
                    targets: target++,
                    name: 'goals_difference',
                    className: 'trn-ladder-standings-table-goals-difference',
                    render: function (data, type, row) {
                        return row.goals_delta;
                    },
                }
            );
        }

        columnDefs.push(
            {
                targets: target++,
                name: 'win_percent',
                className: 'trn-ladder-standings-table-win-percent',
                render: function (data, type, row) {
                    return row.win_percent;
                },
            },
            {
                targets: target++,
                name: 'streak',
                className: 'trn-ladder-standings-table-streak',
                render: function (data, type, row) {
                    let streakClass;
                    if (0 > row.streak) {
                        streakClass = `negative-streak`;
                    } else if (0 < row.streak) {
                        streakClass = `positive-streak`;
                    } else {
                        streakClass = ``;
                    }
                    return `<span class="${streakClass}">${row.streak}</span>`;
                },
            },
            {
                targets: target++,
                name: 'idle',
                className: 'trn-ladder-standings-table-idle',
                render: function (data, type, row) {
                    let idleClass;
                    if (7 >= row.days_idle) {
                        idleClass = `trn-ladder-active-last-7`;
                    } else if (14 >= row.days_idle) {
                        idleClass = `trn-ladder-active-last-14`;
                    } else if (21 >= row.days_idle) {
                        idleClass = `trn-ladder-active-last-21`;
                    } else {
                        idleClass = `trn-ladder-inactive`;
                    }
                    return `<span class="${idleClass}">${row.days_idle}</span>`;
                },
            }
        );

        if (options.can_challenge || options.is_admin) {
            columnDefs.push({
                targets: target,
                name: 'actions',
                className: 'trn-ladder-standings-table-actions',
                render: function (data, type, row) {
                    let links = [];

                    if (options.can_challenge) {
                        links.push(`<a href="${options.challenge_url}${row.competitor_id}" title="${options.language.challenge_link_title}"><i class="fa fa-crosshairs" aria-hidden="true"></i></a>`);
                    }

                    if (options.is_admin) {
                        links.push(`<a href="${row.edit_link}" title="${options.language.edit_link_title}"><i class="fa fa-edit" aria-hidden="true"></i></a>`);
                        let competitor_name = ``;
                        if ('player' === row.competitor_type) {
                            competitor_name = options.language.confirm_delete_message.format(row._embedded.competitor[0].name);
                        } else {
                            competitor_name = options.language.confirm_delete_message.format(row._embedded.competitor[0].name);
                        }
                        links.push(`<a class="trn-remove-competitor-link trn-confirm-action-link" href="#" title="${options.language.remove_link_title}" data-competitor-id="${row.ladder_competitor_id}" data-confirm-title="${options.language.confirm_delete_title}" data-confirm-message="${competitor_name}"><i class="fa fa-trash" aria-hidden="true"></i></a>`);
                        if (options.can_promote && 1 !== row.rank) {
                           links.push(`<a class="trn-promote-competitor-link" href="#" title="${options.language.promote_link_title}" data-competitor-id="${row.ladder_competitor_id}"><i class="fa fa-long-arrow-alt-up" aria-hidden="true"></i></a>`);
                        }
                    }

                    if (links.length > 0) {
                        return links.join(' ');
                    } else {
                        return ``;
                    }
                },
                orderable: false,
            });
        }

        const default_direction = 'desc';
        let standings = jQuery('#ladder-standings-table')
            .on('xhr.dt', function (e, settings, json, xhr) {
                json.data = JSON.parse(JSON.stringify(json));
                json.recordsTotal = xhr.getResponseHeader('X-WP-Total');
                json.recordsFiltered = xhr.getResponseHeader('TRN-Filtered');
                json.length = xhr.getResponseHeader('X-WP-TotalPages');
                json.draw = xhr.getResponseHeader('TRN-Draw');
            })
            .DataTable({
                processing: true,
                serverSide: true,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
                language: options.table_language,
                autoWidth: false,
                ajax: {
                    url: `${options.api_url}ladder-competitors/?_wpnonce=${options.rest_nonce}&_embed&ladder_id=${options.ladder_id}`,
                    type: 'GET',
                    data: function (data) {
                        console.log(data)
                        let sent = {
                            draw: data.draw,
                            page: Math.floor(data.start / data.length),
                            per_page: data.length,
                            search: data.search.value,
                            orderby: `${data.columns[data.order[0].column].name}.${data.order[0].dir}`
                        };
                        console.log(sent)
                        return sent;
                    }
                },
                order: [[2, default_direction]],
                columnDefs: columnDefs,
                drawCallback: function( settings ) {
                    document.dispatchEvent( new CustomEvent( 'trn-html-updated', { 'detail': 'The table html has updated.' } ));
                },
            });
    }, false);
})(trn);