/**
 * Competitor trophy table.
 *
 * @link       https://www.tournamatch.com
 * @since      3.27.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function (jQuery, $) {
    'use strict';

    let options = trn_competitor_trophy_table_options;

    window.addEventListener('load', function () {
        let columnDefs = [
            {
                targets: 0,
                name: 'trophy',
                className: 'trn-competitor-trophy-table-place',
                render: function(data, type, row) {
                    switch (row.finish_place) {
                        case 1:
                            return `<span style="font-size: 1.5rem"><i class="fa fa-trophy trn-trophy-color-1" title="First place in ${row._embedded.tournament[0].name}"></i> 1<sup>st</sup> Place</span>`;
                            break;
                        case 2:
                            return `<span style="font-size: 1.5rem"><i class="fa fa-trophy trn-trophy-color-2" title="Second place in ${row._embedded.tournament[0].name}"></i> 2<sup>nd</sup> Place</span>`;
                            break;
                        case 3:
                            return `<span style="font-size: 1.5rem"><i class="fa fa-trophy trn-trophy-color-3" title="Third place in ${row._embedded.tournament[0].name}"></i> 3<sup>rd</sup> Place</span>`;
                            break;
                    }

                    return ``;
                },
            },
            {
                targets: 1,
                name: 'competition',
                className: 'trn-competitor-trophy-table-competition',
                render: function(data, type, row) {
                    return `<a href="${row._embedded.tournament[0].link}">${row._embedded.tournament[0].name}</a>`;
                },
            },
            {
                targets: 2,
                name: 'date',
                className: 'trn-competitor-trophy-table-date',
                render: function(data, type, row) {
                    return '';//return row.date.rendered;
                },
            },
            {
                targets: 3,
                name: 'game',
                className: 'trn-competitor-trophy-table-game',
                render: function (data, type, row) {
                    if (0 !== row._embedded.tournament[0].gid) {
                        return `<img class="rounded" width="40px" height="40px" alt="Thumbnail for ${row._embedded.game[0].name}" title="${row._embedded.game[0].name}" src="${options.image_source}${row._embedded.game[0].image}"/>`;
                    } else {
                        return ``;
                    }
                },
            },
        ];

        jQuery('#trn_competitor_trophy_table')
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
                info: false,
                pagingType: 'simple',
                ajax: {
                    url: `${options.api_url}trophies/?_wpnonce=${options.rest_nonce}&_embed&competitor_id=${options.competitor_id}&competitor_type=${options.competitor_type}`,
                    type: 'GET',
                    data: function(data) {
                        return {
                            draw: data.draw,
                            page: Math.floor(data.start / data.length),
                            per_page: data.length,
                            search: data.search.value,
                            orderby: `${data.columns[data.order[0].column].name}.${data.order[0].dir}`
                        };
                    }
                },
                order: [[ 1, "ASC" ]],
                columnDefs: columnDefs,
            });

    }, false);
})(jQuery, trn);