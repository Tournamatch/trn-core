/**
 * Handles client scripting for the competitor tournament shortcode.
 *
 * @link       https://www.tournamatch.com
 * @since      3.25.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($, trn) {
    let options = trn_competitor_tournaments_options;

    window.addEventListener('load', function () {
        let columnDefs = [
            {
                targets: 0,
                name: 'name',
                className: 'trn-tournament-competitions-table-name',
                render: function (data, type, row) {
                    return `<a href="${row._embedded.tournament[0].link}">${row._embedded.tournament[0].name}</a>`;
                },
            }
        ];

        let target = 1;
        if ('players' === options.competitor_type) {
            columnDefs.push({
                targets: target++,
                name: 'team',
                className: 'trn-tournament-competitions-table-team',
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
                className: 'trn-tournament-competitions-table-joined',
                render: function (data, type, row) {
                    return row.joined_date.rendered;
                },
            },
        );

        $('#trn-tournament-competitions-table')
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
                    url: `${options.api_url}tournament-competitors/?${options.slug}=${options.competitor_id}&_wpnonce=${options.rest_nonce}&_embed`,
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