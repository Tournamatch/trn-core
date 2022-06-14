/**
 * Admin manage tournament bulk registration page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.17.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './../tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let options = trn_tournament_registration_options;

        // intialize auto complete
        $.autocomplete(document.getElementById('competitor_id'), function(val) {
            return new Promise((resolve, reject) => {
                /* need to query server for names here. */
                let xhr = new XMLHttpRequest();
                xhr.open('GET', options.api_url + 'players/?search=' + val + '&per_page=5');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        // resolve(JSON.parse(xhr.response).map((player) => {return { 'value': player.id, 'text': player.name };}));
                        resolve(JSON.parse(xhr.response).map((player) => {return player.name;}));
                    } else {
                        reject();
                    }
                };
                xhr.send();
            });
        });

        // toggle new or select
        function toggleTeams() {
            let teamSelection = document.getElementById('new_or_existing');
            let selectedValue = teamSelection.options[teamSelection.selectedIndex].value;

            if (selectedValue === 'new') {
                document.getElementById('tag_row').style.display = 'table-row';
                document.getElementById('team_tag').required = true;
                document.getElementById('name_row').style.display = 'table-row';
                document.getElementById('team_name').required = true;
                document.getElementById('existing_row').style.display = 'none';
                document.getElementById('existing_team').required = false;
            } else {
                document.getElementById('tag_row').style.display = 'none';
                document.getElementById('team_tag').required = false;
                document.getElementById('name_row').style.display = 'none';
                document.getElementById('team_name').required = false;
                document.getElementById('existing_row').style.display = 'table-row';
                document.getElementById('existing_team').required = true;
            }
        }

        // new or existing drop down
        if (options.competition === 'teams') {
            let teamSelection = document.getElementById('new_or_existing');

            teamSelection.addEventListener('change', function (event) {
                event.preventDefault();
                toggleTeams();
            }, false);

            toggleTeams();

            document.getElementById('competitor_id').addEventListener('change', function(e) {
                console.log(`value changed to ${this.value}`);
                let p = new Promise((resolve, reject) => {
                    let xhr = new XMLHttpRequest();
                    xhr.open('GET', options.api_url + 'players/?search=' + this.value);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                    xhr.onload = function () {
                        console.log(xhr.response);
                        if (xhr.status === 200) {
                            const players = JSON.parse(xhr.response);

                            if (players.length > 0) {
                                resolve(players[0]['user_id']);
                                document.getElementById('trn-tournament-register-response').innerHTML = ``;
                            } else {
                                document.getElementById('trn-tournament-register-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${options.language.no_competitor}</p>`;
                            }
                        } else {
                            reject();
                        }
                    };
                    xhr.send();
                });
                p.then((user_id) => {
                    getTeams(user_id);
                });
            }, false);
        }

        // get teams for single player
        function getTeams(user_id) {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + `players/${user_id}/teams`);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                let content = ``;
                if (xhr.status === 200) {
                    let teams = JSON.parse(xhr.response);

                    if (teams !== null && teams.length > 0) {
                        for (let i = 0; i < teams.length; i++) {
                            let team = teams[i];

                            content += `<option value="${team.team_id}">${team.name}</option>`;
                        }
                    } else {
                        content += `<option value="-1">(${options.language.zero_teams})</option>`;
                    }
                } else {
                    content += `<option value="-1">(${options.language.zero_teams})</option>`;
                }

                document.getElementById('existing_team').innerHTML = content;
            };

            xhr.send();
        }

        function registerCompetitor(competitionId, competitorId, competitorType) {
            console.log(`registering ${competitorType} with id ${competitorId} to competition with id ${competitionId}`);

            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'tournament-registrations');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr);
                if (xhr.status === 201) {
                    document.getElementById('trn-tournament-register-response').innerHTML = `<p class="notice notice-success"><strong>${options.language.success}:</strong> ${options.language.success_message}</p>`;
                    document.getElementById('trn-tournament-register-form').reset();
                } else {
                    document.getElementById('trn-tournament-register-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${JSON.parse(xhr.response).message}</p>`;
                }
            };

            xhr.send($.param({
                tournament_id: competitionId,
                competitor_id: competitorId,
                competitor_type: competitorType,
            }));
        }

        document.getElementById('trn-tournament-register-form').addEventListener('submit', function(event) {
            event.preventDefault();

            let p = new Promise((resolve, reject) => {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', options.api_url + 'players/?search=' + document.getElementById('competitor_id').value);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    console.log(xhr.response);
                    if (xhr.status === 200) {
                        const players = JSON.parse(xhr.response);

                        if (players.length > 0) {
                            resolve(players[0]['user_id']);
                            document.getElementById('trn-tournament-register-response').innerHTML = ``;
                        } else {
                            document.getElementById('trn-tournament-register-response').innerHTML = `<p class="notice notice-error"><strong>${options.language.failure}:</strong> ${options.language.no_competitor}</p>`;
                        }
                    } else {
                        reject();
                    }
                };
                xhr.send();
            });
            p.then((userId) => {
                if (options.competition === 'teams') {
                    let teamSelection = document.getElementById('new_or_existing');

                    if ( teamSelection.value === 'new' ) {
                        let q = new Promise((resolve, reject) => {
                            let xhr = new XMLHttpRequest();
                            xhr.open('POST', options.api_url + 'teams');
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                            xhr.onload = function () {
                                if (xhr.status === 200) {
                                    resolve(JSON.parse(xhr.response).data.team_id);
                                } else {
                                    reject(xhr);
                                }
                            };

                            xhr.send($.param({
                                user_id: userId,
                                name: document.getElementById('team_name').value,
                                tag: document.getElementById('team_tag').value
                            }));
                        });
                        q.then((teamId) => {
                            registerCompetitor(options.tournament_id, teamId, 'teams');
                        });
                    } else {
                        registerCompetitor(options.tournament_id, document.getElementById('existing_team').value, 'teams');
                    }
                } else {
                    registerCompetitor(options.tournament_id, userId, 'players');
                }
            });

            console.log('submitted');
        }, false);

    }, false);
})(trn);