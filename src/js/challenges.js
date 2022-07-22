/**
 * Challenge list page.
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

    let options = trn_end_scripts;

    function handleDeleteConfirm() {
        let links = document.getElementsByClassName('trn-confirm-action-link');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('trn.confirmed.action', function (event) {
                event.preventDefault();

                console.log(`modal was confirmed for link ${link.dataset.challengeId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', `${options.api_url}challenges/${link.dataset.challengeId}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        window.location.reload();
                    } else {
                        let response = JSON.parse(xhr.response);
                        document.getElementById('trn-delete-challenge-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
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
                name: 'ladder',
                className: 'trn-challenges-table-ladder',
                render: function (data, type, row) {
                    return `<a href="${row._embedded.ladder[0].link}">${row._embedded.ladder[0].name}</a>`;
                },
            },
            {
                targets: 1,
                name: 'challenger',
                className: 'trn-challenges-table-challenger',
                render: function (data, type, row) {
                    if (0 === row.challengee_id) {
                        return options.language.hidden;
                    } else {
                        return `<a href="${row._embedded.challenger[0].link}">${row._embedded.challenger[0].name}</a>`;
                    }
                },
            },
            {
                targets: 2,
                name: 'challengee',
                className: 'trn-challenges-table-challengee',
                render: function (data, type, row) {
                    if (0 === row.challengee_id) {
                        return options.language.open;
                    } else {
                        return `<a href="${row._embedded.challengee[0].link}">${row._embedded.challengee[0].name}</a>`;
                    }
                },
            },
            {
                targets: 3,
                name: 'match_time',
                className: 'trn-challenges-table-match-time',
                render: function (data, type, row) {
                    return row.match_time.rendered;
                },
            },
            {
                targets: 4,
                name: 'status',
                className: 'trn-challenges-table-status',
                render: function (data, type, row) {
                    return trn.ucfirst(row.accepted_state);
                },
            },
            {
                targets: 5,
                name: 'actions',
                className: 'trn-challenges-table-actions',
                render: function (data, type, row) {
                    let content = `<a href="${row.link}"><i class="fa fa-info" aria-hidden="true"></i></a>`;

                    if ( options.user_capability ) {
                        content += ` <a class="trn-delete-challenge-action trn-confirm-action-link" data-challenge-id="${row.challenge_id}" data-confirm-title="${options.language.delete_challenge}" data-confirm-message="${options.language.delete_confirm.format(row.challenge_id)}" href="${options.challenge_url}${row.challenge_id}" title="${options.language.delete_challenge}"><i class="fa fa-trash"></i></a>`;
                    }

                    return content;
                },
                orderable: false,
            }
        ];

        jQuery('#trn-challenge-list-table')
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
                ajax: {
                    url: `${options.api_url}challenges/?_wpnonce=${options.rest_nonce}&_embed`,
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
                order: [[3, 'asc']],
                columnDefs: columnDefs,
                drawCallback: function( settings ) {
                    document.dispatchEvent( new CustomEvent( 'trn-html-updated', { 'detail': 'The table html has updated.' } ));
                },
            });
    }, false);
}(jQuery, trn));