/**
 * Handles the competitor match list page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.25.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($, trn) {
    let options = trn_competitor_match_list_table_options;

    window.addEventListener('load', function () {
        let columnDefs = [
            {
                targets: 0,
                name: 'competition_type',
                className: 'trn-match-history-event',
                render: function (data, type, row) {
                    return trn.ucfirst(row.competition_type);
                },
            },
            {
                targets: 1,
                name: 'name',
                className: 'trn-match-history-name',
                render: function (data, type, row) {
                    return `<a href="${row._embedded.competition[0].link}">${row._embedded.competition[0].name}</a>`;
                },
            },
            {
                targets: 2,
                name: 'result',
                className: 'trn-match-history-result',
                render: function (data, type, row) {
                    return row.match_result;
                },
                orderable: false,
            },
            {
                targets: 3,
                name: 'match_date',
                className: 'trn-match-history-date',
                render: function (data, type, row) {
                    return row.match_date.rendered;
                },
            },
            {
                targets: 4,
                name: 'details',
                className: 'trn-match-history-details',
                render: function (data, type, row) {
                    return `<a href="${row.link}"><i class="fa fa-info"></i></a>`;
                },
                orderable: false,
            },
        ];

        $('#trn-competitor-match-list-table')
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
                    url: `${options.api_url}matches/?&competitor_type=${options.competitor_type}&competitor_id=${options.competitor_id}_wpnonce=${options.rest_nonce}&_embed`,
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
                order: [[3, 'desc']],
                columnDefs: columnDefs,
                drawCallback: function( settings ) {
                    document.dispatchEvent( new CustomEvent( 'trn-html-updated', { 'detail': 'The table html has updated.' } ));
                },
            });
    }, false);
}(jQuery, trn));