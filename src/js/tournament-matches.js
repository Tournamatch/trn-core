/**
 * Handles client scripting for the tournament matches page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.17.0
 * @since      3.21.0 Added support for server side DataTables.
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($, trn) {
    let options = trn_tournament_matches_options;

    window.addEventListener('load', function () {
        let columnDefs = [
            {
                targets: 0,
                name: 'name',
                className: 'trn-tournament-matches-table-competitors',
                render: function (data, type, row) {
                    let html = ` vs `;
                    if (row._embedded.one_competitor[0].name) {
                        html = `<a href="${row._embedded.one_competitor[0].link}">${row._embedded.one_competitor[0].name}</a>` + html;
                    }
                    if (row._embedded.two_competitor[0].name) {
                        html += `<a href="${row._embedded.two_competitor[0].link}">${row._embedded.two_competitor[0].name}</a>`;
                    } else {
                        html += options.undecided;
                    }
                    return html;
                },
                orderable: false,
            },
            {
                targets: 1,
                name: 'result',
                className: 'trn-tournament-matches-table-result',
                render: function (data, type, row) {
                    return row.match_result;
                },
                orderable: false,
            },
            {
                targets: 2,
                name: 'match_date',
                className: 'trn-tournament-matches-table-date',
                render: function (data, type, row) {
                    return row.match_date.rendered;
                },
            },
            {
                targets: 3,
                name: 'details',
                className: 'trn-tournament-matches-table-link',
                render: function (data, type, row) {
                    return `<a href="${row.link}" title="${options.language.view_match_details}"><i class="fa fa-info"></i></a>`;
                },
                orderable: false,
            },
        ];

        $('#trn-tournament-matches-table')
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
                    url: `${options.api_url}matches/?competition_type=tournaments&competition_id=${options.tournament_id}&_wpnonce=${options.rest_nonce}&_embed`,
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
                order: [[2, 'desc']],
                columnDefs: columnDefs,
            });
    }, false);
}(jQuery, trn));