/**
 * Player teams list page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.25.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function (jQuery, $) {
    'use strict';

    let options = trn_player_teams_list_table_options;

    window.addEventListener('load', function () {
        let columnDefs = [
            {
                targets: 0,
                name: 'name',
                className: 'trn-player-team-table-name',
                render: function(data, type, row) {
                    return `<a href="${row._embedded.team[0].link}">${row._embedded.team[0].name}</a>`;
                },
            },
            {
                targets: 1,
                name: 'rank',
                className: 'trn-player-team-table-contact',
                render: function (data, type, row) {
                    console.log(row);
                    return row._embedded.rank[0].title;
                },
            },
            {
                targets: 2,
                name: 'joined_date',
                className: 'trn-player-team-table-created',
                render: function(data, type, row) {
                    return row.joined_date.rendered;
                },
            },
            {
                targets: 3,
                name: 'members',
                className: 'trn-player-team-table-members',
                render: function(data, type, row) {
                    return row._embedded.team[0].members;
                },
            },
        ];

        jQuery('#trn-player-teams-table')
            .on('xhr.dt', function( e, settings, json, xhr ) {
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
                language: options['table_language'],
                autoWidth: false,
                searching: false,
                lengthChange: false,
                ajax: {
                    url: `${options.api_url}team-members/?player_id=${options.player_id}&_wpnonce=${options.rest_nonce}&_embed`,
                    type: 'GET',
                    data: function(data) {
                        let sent = {
                            draw: data.draw,
                            page: Math.floor(data.start / data.length),
                            per_page: data.length,
                            search: data.search.value,
                            orderby: `${data.columns[data.order[0].column].name}.${data.order[0].dir}`
                        };
                        //console.log(sent);
                        return sent;
                    }
                },
                order: [[ 1, 'asc' ]],
                columnDefs: columnDefs,
                drawCallback: function( settings ) {
                    document.dispatchEvent( new CustomEvent( 'trn-html-updated', { 'detail': 'The table html has updated.' } ));
                },
            });

    }, false);
})(jQuery, trn);