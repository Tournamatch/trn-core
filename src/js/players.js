/**
 * Player list page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.21.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function(jQuery, $){
    'use strict';

    const options = trn_table_options;

    window.addEventListener('load', function () {
        let columnDefs = [
            {
                targets: 0,
                name: 'display_name',
                className: 'trn-players-table-name',
                render: function (data, type, row) {
                    return `<a href="${row.link}">${row.name}</a>`;
                },
            },
            {
                targets: 1,
                name: 'joined_date',
                className: 'trn-players-table-joined',
                render: function (data, type, row) {
                    return row.joined_date.rendered
                },
            },
            {
                targets: 2,
                name: 'location',
                className: 'trn-players-table-location',
                render: function (data, type, row) {
                    return row.location;
                },
            },
            {
                targets: 3,
                name: 'teams',
                className: 'trn-players-table-teams',
                render: function (data, type, row) {
                    return row.teams;
                },
            },
            {
                targets: 4,
                name: 'contact',
                className: 'trn-players-table-contact',
                render: function (data, type, row) {
                    let links = [];

                    for (const property in options.social_links) {
                        if ( row[property] && row[property].length > 0 ) {
                            links.push(options.social_links[property].format(row[property]));
                        }
                    }

                    if (links.length > 0) {
                        return links.join(' ');
                    } else {
                        return ``;
                    }
                },
                orderable: false,
            },
        ];
        if (options.user_capability) {
            columnDefs.push(
                {
                    targets: 5,
                    name: 'actions',
                    className: 'trn-players-table-admin',
                    render: function (data, type, row) {
                        return `<a href="${row.link}/edit" title="${options.language.edit_player}"><i class="fa fa-edit"></i></a>`;
                    },
                    orderable: false,
                }
            );
        }

        jQuery('#trn_players_list_table')
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
                language: options['table_language'],
                autoWidth: false,
                ajax: {
                    url: `${options.api_url}players/?_wpnonce=${options.rest_nonce}`,
                    type: 'GET',
                    data: function (data) {
                        console.log(data);
                        let sent = {
                            draw: data.draw,
                            page: Math.floor(data.start / data.length),
                            per_page: data.length,
                            search: data.search.value,
                            orderby: `${data.columns[data.order[0].column].name}.${data.order[0].dir}`
                        };
                        console.log(sent);
                        return sent;
                    }
                },
                order: [[0, 'asc']],
                columnDefs: columnDefs,
            });

    }, false);
}( jQuery, trn ));
