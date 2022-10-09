/**
 * Team roster table page.
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

    let options = trn_team_roster_table_options;

    function confirmRemove(event) {
        event.preventDefault();
        const teamMemberId = this.dataset.teamMemberId;

        console.log(`modal was confirmed for link ${this.dataset.teamMemberId}`);
        let xhr = new XMLHttpRequest();
        xhr.open('DELETE', `${options.api_url}team-members/${this.dataset.teamMemberId}`);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
        xhr.onload = () => {
            if (xhr.status === 204) {
                $.event('team-members').dispatchEvent(new CustomEvent('changed'));
            } else {
                let response = JSON.parse(xhr.response);
                document.getElementById('trn-delete-team-member-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
            }
        };

        xhr.send();
    }

    function updateRank(teamMemberId, newRankId) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', options.api_url + 'team-members/' + teamMemberId);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
        xhr.onload = function () {
            if (xhr.status === 200) {
                $.event('team-members').dispatchEvent(new CustomEvent('changed'));
            } else {
                let response = JSON.parse(xhr.response);
                document.getElementById('trn-team-roster-response').innerHTML = `<div class="trn-alert trn-alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
            }
        };

        xhr.send($.param({
            team_rank_id: newRankId,
        }));
    }

    function rankChanged(event) {
        const newRankId = event.target.value;
        const newRankWeight = event.target.querySelector(`option[value="${newRankId}"]`).dataset.rankWeight;
        const oldRankId = this.dataset.currentRankId;
        const teamMemberId = this.dataset.teamMemberId;

        if (newRankId !== oldRankId) {
            if (('1' === newRankWeight) && confirm(options.language.confirm_new_owner)) {
                updateRank(teamMemberId, newRankId);
            } else {
                updateRank(teamMemberId, newRankId);
            }
        }
    }

    function attachListeners() {
        let links = document.getElementsByClassName('trn-drop-player-action');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('trn.confirmed.action.drop-player', confirmRemove);
        });
        let ranks = document.getElementsByClassName('trn-change-rank-dropdown');
        Array.prototype.forEach.call(ranks, function (rank) {
            rank.addEventListener('change', rankChanged);
        });
    }

    window.addEventListener('load', function () {
        document.addEventListener('trn-html-updated', attachListeners);

        $.event('team-members').addEventListener('changed', function() {
            jQuery('#trn-team-roster-table').DataTable().ajax.reload()
        });

        let target = 0;
        let columnDefs = [
            {
                targets: target++,
                name: 'player',
                className: 'trn-team-roster-name',
                render: function(data, type, row) {
                    return `<img src="${options.flag_directory}${row._embedded.player[0].flag}" width="18" height="12" title="${row._embedded.player[0].flag}"> <a href="${row._embedded.player[0].link}">${row._embedded.player[0].name}</a>`;
                },
            },
            {
                targets: target++,
                name: 'title',
                className: 'trn-team-roster-title',
                render: function (data, type, row) {
                    if ((options.can_edit_roster) && (row._embedded.rank[0].weight != '1')) {
                        let html = `<select class="trn-form-control trn-form-control-sm trn-change-rank-dropdown" data-current-rank-id="${row.team_rank_id}" data-team-member-id="${row.team_member_id}" >`;

                        Array.prototype.forEach.call(options.ranks, (rank) => {
                            if (rank.team_rank_id == row.team_rank_id) {
                                html += `<option value="${rank.team_rank_id}" selected data-rank-weight="${rank.weight}">${rank.title}</option>`;
                            } else {
                                html += `<option value="${rank.team_rank_id}" data-rank-weight="${rank.weight}">${rank.title}</option>`;
                            }
                        });

                        html += `</select>`;

                        return html;
                    } else {
                        return row._embedded.rank[0].title;
                    }
                },
            },
        ];

        if (options.display_record) {
            columnDefs.push(
                {
                    targets: target++,
                    name: 'wins',
                    className: 'trn-team-roster-wins',
                    render: function (data, type, row) {
                        return row.wins;
                    }
                },
                {
                    targets: target++,
                    name: 'losses',
                    className: 'trn-team-roster-losses',
                    render: function (data, type, row) {
                        return row.losses;
                    }
                },
            );

            if (options.uses_draws) {
                columnDefs.push(
                    {
                        targets: target++,
                        name: 'draws',
                        className: 'trn-team-roster-draws',
                        render: function (data, type, row) {
                            return row.draws;
                        }
                    },
                );
            }
        }

        columnDefs.push(
            {
                targets: target++,
                name: 'joined_date',
                className: 'trn-team-roster-joined',
                render: function(data, type, row) {
                    return row.joined_date.rendered;
                },
            },
            {
                targets: target++,
                name: 'options',
                className: 'trn-team-roster-options',
                render: function(data, type, row) {
                    if ((options.can_edit_roster) && (row._embedded.rank[0].weight != '1')) {
                        return `<a class="trn-drop-player-action trn-button trn-button-sm trn-button-secondary trn-confirm-action-link" data-team-member-id="${row.team_member_id}" data-confirm-title="${options.language.drop_team_member}" data-confirm-message="${options.language.drop_confirm.format(row._embedded.player[0].name)}" data-modal-id="drop-player" href="#">${options.language.drop_player}</a>`;
                    } else {
                        return '';
                    }
                },
            },
        );

        jQuery('#trn-team-roster-table')
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
                paging: false,
                ajax: {
                    url: `${options.api_url}team-members/?team_id=${options.team_id}&_wpnonce=${options.rest_nonce}&_embed`,
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