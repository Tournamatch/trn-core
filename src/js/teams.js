/**
 * Team list page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 * @since      3.21.0 Added support for server side DataTables.
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function (jQuery, $) {
    'use strict';

    let options = trn_teams_list_table_options;

    function handleDeleteConfirm() {
        let links = document.getElementsByClassName('trn-confirm-action-link');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('trn.confirmed.action.delete-team', function (event) {
                event.preventDefault();

                console.log(`modal was confirmed for link ${link.dataset.teamId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', `${options.api_url}teams/${link.dataset.teamId}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        window.location.reload();
                    } else {
                        let response = JSON.parse(xhr.response);
                        document.getElementById('trn-delete-team-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send();
            }, false);
        });
    }

    window.addEventListener('load', function () {
        document.addEventListener('trn-html-updated', function(e) {
            handleDeleteConfirm();
        });
        handleDeleteConfirm();

        let columnDefs = [
            {
                targets: 0,
                name: 'name',
                className: 'trn-teams-table-name',
                render: function(data, type, row) {
                    return `<a href="${row.link}">${row.name}</a>`;
                },
            },
            {
                targets: 1,
                name: 'joined_date',
                className: 'trn-teams-table-created',
                render: function(data, type, row) {
                    return row.joined_date.rendered;
                },
            },
            {
                targets: 2,
                name: 'members',
                className: 'trn-teams-table-members',
                render: function(data, type, row) {
                    return row.members;
                },
            },
        ];

        if (options.user_capability) {
            columnDefs.push(
                {
                    targets: 3,
                    name: 'admin',
                    render: function(data, type, row) {
                        const message = options.language.delete_confirm.format(row.name);
                        return `<a href="${row.link}/edit"><i class="fa fa-edit"></i></a> ` +
                            `<a class="trn-delete-team-action trn-confirm-action-link" data-team-id="${row.team_id}" data-modal-id="delete-team" data-confirm-title="${options.language.delete_team}" data-confirm-message="${message}" href="#" title="${options.language.delete_team}"><i class="fa fa-trash"></i></a>`;
                    },
                    orderable: false,
                },
            );
        }

        jQuery('#trn-teams-table')
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
                ajax: {
                    url: `${options.api_url}teams/?_wpnonce=${options.rest_nonce}&_embed`,
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