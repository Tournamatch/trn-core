/**
 * Handles the click events for the match list page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 * @since      3.21.0 Added support for server side DataTables.
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($, trn) {
    let options = trn_match_list_options;

    function handleDeleteConfirm() {
        let links = document.getElementsByClassName('trn-confirm-action-link');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('trn.confirmed.action', function (event) {
                event.preventDefault();

                console.log(`modal was confirmed for link ${link.dataset.matchId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', `${options.api_url}matches/${link.dataset.matchId}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        window.location.reload();
                    } else {
                        let response = JSON.parse(xhr.response);
                        document.getElementById('trn-delete-match-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
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
                name: 'competition_type',
                className: 'trn-matches-table-event',
                render: function (data, type, row) {
                    return trn.ucfirst(row.competition_type);
                },
            },
            {
                targets: 1,
                name: 'name',
                className: 'trn-matches-table-name',
                render: function (data, type, row) {
                    return `<a href="${row._embedded.competition[0].link}">${row._embedded.competition[0].name}</a>`;
                },
            },
            {
                targets: 2,
                name: 'result',
                className: 'trn-matches-table-result',
                render: function (data, type, row) {
                    return row.match_result;
                },
                orderable: false,
            },
            {
                targets: 3,
                name: 'match_date',
                className: 'trn-matches-table-date',
                render: function (data, type, row) {
                    return row.match_date.rendered;
                },
            },
            {
                targets: 4,
                name: 'details',
                className: 'trn-challenges-table-status',
                render: function (data, type, row) {
                    let links = [];

                    links.push(`<a href="${row.link}" title="${options.language.view_match_details}"><i class="fa fa-info"></i></a>`);
                    if (options.user_capability) {
                        if (row.competition_type === 'ladders') {
                            links.push(`<a href="${options.ladder_edit}${row.match_id}" title="${options.language.edit_match}"><i class="fa fa-edit"></i></a>`);
                            links.push(`<a class="trn-confirm-action-link trn-delete-match-action" data-match-id="${row.match_id}" data-confirm-title="${options.language.delete_match}" data-confirm-message="${options.language.delete_confirm.format(row.match_id)}" href="#" title="${options.language.delete_match}"><i class="fa fa-times"></i></a>`);
                        }
                    }

                    return links.join(' ');
                },
                orderable: false,
            },
        ];

        $('#match-list-table')
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
                    url: `${options.api_url}matches/?_wpnonce=${options.rest_nonce}&_embed`,
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