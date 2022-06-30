/**
 * Team profile page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.8.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    // add listener for roster changed event
    window.addEventListener('load', function () {
        let options = trn_team_profile_options;
        const joinTeamButton = document.getElementById('trn-join-team-button');
        const leaveTeamButton = document.getElementById('trn-leave-team-button');
        const deleteTeamButton = document.getElementById('trn-delete-team-button');
        const editTeamButton = document.getElementById('trn-edit-team-button');

        function canJoin(userId, members) {
            let isMember = false;
            if ((members !== null) && (members.length > 0)) {
                members.forEach((member) => {
                    isMember = isMember || (member.user_id === userId);
                });
            }
            return !isMember;
        }


        function canLeave(userId, members) {
            let isMember = false;
            let isOwner = false;
            if ((members !== null) && (members.length > 0)) {
                members.forEach((member) => {
                    if (member.user_id === userId) {
                        isMember = true;
                        if (member.rank_id === 1) {
                            isOwner = true;
                        }
                    }
                });
            }
            return isMember && !isOwner;
        }


        function canDelete(userId, members) {
            let isMember = false;
            if ((members !== null) && (members.length > 0)) {
                members.forEach((member) => {
                    isMember = (member.user_id === userId) || isMember;
                });
            }
            return isMember && (members.length === 1);
        }

        function canEdit(userId, members) {
            let isOwner = false;
            if ((members !== null) && (members.length > 0)) {
                members.forEach((member) => {
                    isOwner = ((member.user_id === userId) && (member.rank_id === 1)) || isOwner;
                });
            }
            return isOwner;
        }

        function getCurrentUserTeamMemberId(userId, members) {
            let teamMemberId = null;
            if ((members !== null) && (members.length > 0)) {
                members.forEach((member) => {
                    if (member.user_id === userId) {
                        teamMemberId = member.team_member_id;
                    }
                });
            }
            return teamMemberId;
        }

        function evaluateButtonStates(members) {
            const userId = parseInt(options.current_user_id);

            if (canDelete(userId, members)) {
                deleteTeamButton.style.display = 'inline';
                deleteTeamButton.dataset.teamMemberId = getCurrentUserTeamMemberId(userId, members);
            } else {
                deleteTeamButton.style.display = 'none';
            }

            if (canJoin(userId, members)) {
                joinTeamButton.style.display = 'inline';
            } else {
                joinTeamButton.style.display = 'none';
            }

            if (canLeave(userId, members)) {
                leaveTeamButton.style.display = 'inline';
                leaveTeamButton.dataset.teamMemberId = getCurrentUserTeamMemberId(userId, members);
            } else {
                leaveTeamButton.style.display = 'none';
            }

            if (options.can_edit || canEdit(userId, members)) {
                editTeamButton.style.display = 'inline';
            } else{
                editTeamButton.style.display = 'none';
            }
        }

        function getMembers() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', options.api_url + 'team-members/?_embed&' + $.param({team_id: options.team_id}));
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                let content = '';
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.response);
                    let memberLinks = [];

                    if (response !== null && response.length > 0) {
                        Array.prototype.forEach.call(response, function (member) {
                            memberLinks.push(`<a href="../players/${member.user_id}">${member._embedded.player[0].name}</a>`);
                        });

                        content += memberLinks.join(', ');
                    } else {
                        content += `<p class="text-center">${options.language.zero_members}</p>`;
                    }
                    if (options.is_logged_in) {
                        evaluateButtonStates(response);
                    }
                } else {
                    content += `<p class="text-center">${options.language.error_members}</p>`;
                }

                document.getElementById('trn-team-members-list').innerHTML = content;
            };

            xhr.send();
        }
        getMembers();

        $.event('team-members').addEventListener('changed', function() {
            getMembers();
        });

        function joinTeam() {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'team-requests');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                let response = JSON.parse(xhr.response);
                if (xhr.status === 201) {
                    document.getElementById('trn-join-team-response').innerHTML = `<div class="alert alert-success"><strong>${options.language.success}!</strong> ${options.language.success_message}</div>`;
                } else {
                    document.getElementById('trn-join-team-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}:</strong> ${response.message}</div>`;
                }
            };

            xhr.send($.param({
                team_id: document.getElementById('trn-join-team-button').dataset.teamId,
                user_id: document.getElementById('trn-join-team-button').dataset.userId,
            }));
        }

        function handleJoinTeam(event) {
            joinTeam();
        }

        function leaveTeam() {
            let xhr = new XMLHttpRequest();
            xhr.open('DELETE', options.api_url + 'team-members/' + leaveTeamButton.dataset.teamMemberId);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                if (xhr.status === 204) {
                    $.event('team-members').dispatchEvent(new CustomEvent('changed', { detail: { team_member_id: leaveTeamButton.dataset.teamMemberId } } ));
                } else {
                    let response = JSON.parse(xhr.response);
                    document.getElementById('trn-leave-team-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}:</strong> ${response.message}</div>`;
                }
            };

            xhr.send();
        }

        function deleteTeam() {
            let xhr = new XMLHttpRequest();
            xhr.open('DELETE', options.api_url + 'team-members/' + deleteTeamButton.dataset.teamMemberId);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                if (xhr.status === 204) {
                    window.location.href = options.teams_url;
                } else {
                    let response = JSON.parse(xhr.response);
                    document.getElementById('trn-leave-team-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}:</strong> ${response.message}</div>`;
                }
            };

            xhr.send();
        }

        if (options.is_logged_in) {
            joinTeamButton.addEventListener('click', handleJoinTeam, false);
            leaveTeamButton.addEventListener('click', leaveTeam);
            deleteTeamButton.addEventListener('click', deleteTeam);
        }

        /*the autocomplete function takes two arguments,
         the text field element and an array of possible autocompleted values:*/
        const addForm = document.getElementById('trn-add-player-form');
        const nameInput = document.getElementById('trn-add-player-input');

        let currentFocus;

        if (options.can_add === '1') {
            addForm.addEventListener('submit', function(event) {
                event.preventDefault();

                console.log('submitted');

                let p = new Promise((resolve, reject) => {
                    let xhr = new XMLHttpRequest();
                    xhr.open('GET', options.api_url + 'players/?name=' + nameInput.value);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                    xhr.onload = function () {
                        console.log(JSON.parse(xhr.response)[0]['user_id']);
                        if (xhr.status === 200) {
                            resolve(JSON.parse(xhr.response)[0]['user_id']);
                        } else {
                            reject();
                        }
                    };
                    xhr.send();
                });
                p.then((user_id) => {
                    let xhr = new XMLHttpRequest();
                    xhr.open('POST', options.api_url + 'team-members/');
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                    xhr.onload = function () {
                        console.log(xhr);
                        if (xhr.status === 201) {
                            $.event('team-members').dispatchEvent(new CustomEvent('changed', { } ));
                        } else {
                            const message = ( xhr.status === 403 ) ? JSON.parse(xhr.response).message : options.language.failure_message;
                            document.getElementById('trn-add-player-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}:</strong> ${message}</div>`;
                        }
                    };
                    xhr.send($.param({
                        team_id: options.team_id,
                        user_id: user_id,
                    }));
                });

            }, false);

            /*execute a function when someone writes in the text field:*/
            nameInput.addEventListener("input", function(e) {
                let a, b, i, val = this.value;
                let parent = this.parentNode;

                let p = new Promise((resolve, reject) => {
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
                p.then((data) => {
                    console.log(data);

                    /*close any already open lists of autocompleted values*/
                    closeAllLists();
                    if (!val) { return false;}
                    currentFocus = -1;

                    /*create a DIV element that will contain the items (values):*/
                    a = document.createElement("DIV");
                    a.setAttribute("id", this.id + "-autocomplete-list");
                    a.setAttribute("class", "autocomplete-items");

                    /*append the DIV element as a child of the autocomplete container:*/
                    parent.appendChild(a);

                    /*for each item in the array...*/
                    for (i = 0; i < data.length; i++) {
                        let text, value;

                        /* Which format did they give us. */
                        if (typeof data[i] === 'object') {
                            text = data[i]['text'];
                            value = data[i]['value'];
                        } else {
                            text = data[i];
                            value = data[i];
                        }

                        /*check if the item starts with the same letters as the text field value:*/
                        if (text.substr(0, val.length).toUpperCase() === val.toUpperCase()) {
                            /*create a DIV element for each matching element:*/
                            b = document.createElement("DIV");
                            /*make the matching letters bold:*/
                            b.innerHTML = "<strong>" + text.substr(0, val.length) + "</strong>";
                            b.innerHTML += text.substr(val.length);

                            /*insert a input field that will hold the current array item's value:*/
                            b.innerHTML += "<input type='hidden' value='" + value + "'>";

                            b.dataset.value = value;
                            b.dataset.text = text;

                            /*execute a function when someone clicks on the item value (DIV element):*/
                            b.addEventListener("click", function (e) {

                                /* insert the value for the autocomplete text field: */
                                nameInput.value = this.dataset.text;
                                nameInput.dataset.selectedId = this.dataset.value;

                                /* close the list of autocompleted values, (or any other open lists of autocompleted values:*/
                                closeAllLists();
                            });
                            a.appendChild(b);
                        }
                    }
                });
            });

            /*execute a function presses a key on the keyboard:*/
            nameInput.addEventListener("keydown", function(e) {
                let x = document.getElementById(this.id + "-autocomplete-list");
                if (x) x = x.getElementsByTagName("div");
                if (e.keyCode === 40) {
                    /*If the arrow DOWN key is pressed,
                     increase the currentFocus variable:*/
                    currentFocus++;
                    /*and and make the current item more visible:*/
                    addActive(x);
                } else if (e.keyCode === 38) { //up
                    /*If the arrow UP key is pressed,
                     decrease the currentFocus variable:*/
                    currentFocus--;
                    /*and and make the current item more visible:*/
                    addActive(x);
                } else if (e.keyCode === 13) {
                    /*If the ENTER key is pressed, prevent the form from being submitted,*/
                    e.preventDefault();
                    if (currentFocus > -1) {
                        /*and simulate a click on the "active" item:*/
                        if (x) x[currentFocus].click();
                    }
                }
            });

            /*execute a function when someone clicks in the document:*/
            document.addEventListener("click", function (e) {
                closeAllLists(e.target);
            });
        }

        function addActive(x) {
            /*a function to classify an item as "active":*/
            if (!x) return false;
            /*start by removing the "active" class on all items:*/
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            /*add class "autocomplete-active":*/
            x[currentFocus].classList.add("autocomplete-active");
        }

        function removeActive(x) {
            /*a function to remove the "active" class from all autocomplete items:*/
            for (let i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
            }
        }

        function closeAllLists(elmnt) {
            console.log("close all lists");
            /*close all autocomplete lists in the document,
             except the one passed as an argument:*/
            let x = document.getElementsByClassName("autocomplete-items");
            for (let i = 0; i < x.length; i++) {
                if (elmnt !== x[i] && elmnt !== nameInput) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }

    }, false);
})(trn);
