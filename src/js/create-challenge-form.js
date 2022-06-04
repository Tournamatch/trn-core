/**
 * Handles the asynchronous behavior for the create a new challenge form.
 *
 * @link       https://www.tournamatch.com
 * @since      3.20.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        const options = trn_create_challenge_form_options;
        const challengeButton = document.getElementById('trn-challenge-button');
        const matchTimeInput = document.getElementById('match_time');
        const challengerField = document.getElementById('trn-challenge-form-challenger');
        const challengeeSelect = document.getElementById('trn-challenge-form-challengee');
        const challengerGroup = document.getElementById('trn-challenge-form-challenger-group');
        const challengeeGroup = document.getElementById('trn-challenge-form-challengee-group');
        const challengeForm = document.getElementById('trn-create-challenge-form');
        let ladderId = options.ladder_id;
        let challengeeId = options.challengee_id;
        let ladder = options.ladder;

        $.event('ladder').addEventListener('changed', function(ladder) {
            getChallengeBuilder(ladder);
        });

        $.event('challenge-builder').addEventListener('changed', function(challengeBuilder) {
            renderChallengeForm(challengeBuilder.detail);
        });

        function getChallengeBuilder(ladderId) {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', `${options.api_url}challenge-builder/${ladderId}`);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    $.event('challenge-builder').dispatchEvent(new CustomEvent('changed', { detail: JSON.parse(xhr.response) } ));
                } else {
                    $.event('challenge-builder').dispatchEvent(new CustomEvent('failed', { detail: response.message } ));
                }
            };

            xhr.send();
        }

        function renderChallengeForm(challengeBuilder) {
            console.log(challengeBuilder);
            renderChallengeeList(challengeBuilder.competitors);
            renderChallengerField(challengeBuilder.challenger);
            challengeeGroup.classList.remove('d-none');
            challengerGroup.classList.remove('d-none');
            challengeButton.removeAttribute('disabled');
            matchTimeInput.removeAttribute('disabled');
            ladderId = challengeBuilder.ladder_id;
        }

        function renderChallengerField(challenger) {
            if ( 1 === challenger.length ) {
                challengerField.setAttribute('data-competitor-id', challenger[0].competitor_id);
                const p = document.createElement('p');
                p.innerText = challenger[0].competitor_name;
                p.classList.add('form-control-static');
                while (challengerField.firstChild) {challengerField.removeChild(challengerField.firstChild); }
                challengerField.appendChild(p);
            } else {
                const challengerSelect = document.createElement('select');
                challenger.forEach((challenger) => {
                    const opt = document.createElement('option');
                    opt.value = challenger.competitor_id;
                    opt.innerHTML = challenger.competitor_name;
                    challengerSelect.appendChild(opt);
                });
                while (challengerField.firstChild) {challengerField.removeChild(challengerField.firstChild); }
                challengerField.appendChild(challengerSelect);
                challengerSelect.addEventListener('change', function(event) {
                    challengerField.setAttribute('data-competitor-id', event.target.value);
                });
                challengerField.setAttribute('data-competitor-id', challenger[0].competitor_id);
            }
        }

        function renderChallengeeList(challengees) {
            const length = challengeeSelect.length;
            for (let i = length - 1; i >= 0; i--) {
                challengeeSelect.remove(i);
            }
            challengees.forEach((challengee) => {
                const opt = document.createElement('option');
                opt.value = challengee.competitor_id;
                opt.innerHTML = challengee.competitor_name;
                if (challengee.competitor_id === challengeeId) {
                    opt.setAttribute('selected', true);
                }
                challengeeSelect.appendChild(opt);
            });
        }

        // if there is no ladder set, respond to changes in the ladder drop down.
        if (ladder === null) {
            const ladderSelect = document.getElementById(`ladder_id`);

            ladderSelect.addEventListener('change', (event) => getChallengeBuilder(event.target.value));
            challengeButton.setAttribute('disabled', true);
            matchTimeInput.setAttribute('disabled', true);
            getChallengeBuilder(ladderSelect.value);
        } else {
            // get ladder id details
            getChallengeBuilder(ladderId);
        }

        challengeForm.addEventListener('submit', function (event) {
            event.preventDefault();

            let xhr = new XMLHttpRequest();
            xhr.open('POST', `${options.api_url}challenges`);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                console.log(xhr.response);
                if (xhr.status === 201) {
                    const response = JSON.parse(xhr.response);
                    window.location.href = response.link;
                } else {
                    $.event('challenge').dispatchEvent(new CustomEvent('error', { detail: xhr.response } ));
                }
            };

            xhr.send($.param({
                ladder_id: ladderId,
                challenger_id: challengerField.getAttribute('data-competitor-id'),
                challengee_id: challengeeSelect.value,
                match_time: matchTimeInput.value,
            }));
        });
    }, false);
})(trn);