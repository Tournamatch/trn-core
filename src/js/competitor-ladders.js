/**
 * Handles client scripting for the competitor ladder shortcode.
 *
 * @link       https://www.tournamatch.com
 * @since      3.25.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($, trn) {
    let options = trn_competitor_ladders_options;

    window.addEventListener('load', function () {
        let columnDefs = [
            {
                targets: 0,
                name: 'name',
                className: 'trn-ladder-competitions-table-name',
                render: function (data, type, row) {
                    return `<a href="${row._embedded.ladder[0].link}">${row._embedded.ladder[0].name}</a>`;
                },
            }
        ];

        let target = 1;
        if ('players' === options.competitor_type) {
            columnDefs.push({
                targets: target++,
                name: 'team',
                className: 'trn-ladder-competitions-table-team',
                render: function (data, type, row) {
                    if ('teams' === row.competitor_type) {
                        return `<a href="${row._embedded.competitor[0].link}">${row._embedded.competitor[0].name}</a>`;
                    } else {
                        return `-`;
                    }
                }
            });
        }

        columnDefs.push(
            {
                targets: target++,
                name: 'joined',
                className: 'trn-ladder-competitions-table-joined',
                render: function (data, type, row) {
                    return row.joined_date.rendered;
                },
            },
            {
                targets: target++,
                name: 'position',
                className: 'trn-ladder-competitions-table-position',
                render: function (data, type, row) {
                    if ('points' === row._embedded.ladder[0].mode) {
                        return `${row.rank}${trn.ordinal_suffix(row.rank)} (${row.points})`;
                    } else if ('ratings' === row._embedded.ladder[0].mode) {
                        return `${row.rank}${trn.ordinal_suffix(row.rank)} (${row.rating})`;
                    } else {
                        return `${row.position}${trn.ordinal_suffix(row.position)}`;
                    }
                },
            },
            {
                targets: target++,
                name: 'wins',
                className: 'trn-ladder-competitions-table-wins',
                render: function (data, type, row) {
                    return `<span class="wins">${row.wins}</span>`;
                },
            },
            {
                targets: target++,
                name: 'losses',
                className: 'trn-ladder-competitions-table-losses',
                render: function (data, type, row) {
                    return `<span class="losses">${row.losses}</span>`;
                },
            }
        );

        if ('1' === options.uses_draws) {
            columnDefs.push(
                {
                    targets: target++,
                    name: 'draws',
                    className: 'trn-ladder-competitions-table-draws',
                    render: function (data, type, row) {
                        return `<span class="draws">${row.draws}</span>`;
                    },
                }
            );
        }

        columnDefs.push(
            {
                targets: target++,
                name: 'win_percent',
                className: 'trn-ladder-competitions-table-win-percent',
                render: function (data, type, row) {
                    return row.win_percent;
                },
            },
            {
                targets: target++,
                name: 'streak',
                className: 'trn-ladder-competitions-table-streak',
                render: function (data, type, row) {
                    if (0 < row.streak) {
                        return `<span class="positive-streak">${row.streak}</span>`;
                    } else if (0 > row.streak) {
                        return `<span class="negative-streak">${row.streak}</span>`;
                    } else {
                        return row.streak;
                    }
                },
            },
            {
                targets: target++,
                name: 'idle',
                className: 'trn-ladder-competitions-table-idle',
                render: function (data, type, row) {
                    if (0 === row.days_idle.length) {
                        return `-`;
                    } else {
                        return row.days_idle;
                    }
                },
            },
        );

        $('#trn-ladder-competitions-table')
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
                searching: false,
                lengthChange: false,
                ajax: {
                    url: `${options.api_url}ladder-competitors/?${options.slug}=${options.competitor_id}&_wpnonce=${options.rest_nonce}&_embed`,
                    type: 'GET',
                    data: function (data) {
                        let sent = {
                            draw: data.draw,
                            page: Math.floor(data.start / data.length),
                            per_page: data.length,
                            search: data.search.value,
                            orderby: `${data.columns[data.order[0].column].name}.${data.order[0].dir}`
                        };
                        return sent;
                    }
                },
                order: [[1, 'desc']],
                columnDefs: columnDefs,
                drawCallback: function( settings ) {
                    document.dispatchEvent( new CustomEvent( 'trn-html-updated', { 'detail': 'The table html has updated.' } ));
                },
            });
    }, false);
}(jQuery, trn));