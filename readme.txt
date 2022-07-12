=== Tournamatch ===
Contributors: tournamatch
Tags: tournament, ladder, standings, bracket, leaderboard, bracket-generator, esports
Requires at least: 4.7
Tested up to: 6.0.0
Stable tag: 4.1.0
Requires PHP: 5.6.20
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A ladder and tournament plugin for eSports, physical sports, board games, and other online gaming leagues.

== Description ==

**Important**: Do not upgrade from version 3.x to 4.x of this plugin at this time. This is a major version change, and you should reach out to support@tournamatch.com to upgrade from 3.x to 4.0.

Use this plugin to create tournaments and ladders (leader boards) on your website. Ladders track points, wins, losses, [optionally] draws, games played, win streak, and win percent. Tournaments support single elimination head-to-head competitions. It includes match challenges, player profiles, team profiles, ladder and tournament rule pages, match lists, and a player dashboard for managing everything.

This plugin is for active gaming communities! Registered users can create their own teams, join other teams, accept or decline join requests, drop team members, and send email or user team invitations; users can send match challenges; users or admins report, confirm, or dispute match results; ladders and tournaments will update automatically when match results are confirmed.

**Important**: This plugin is designed to work with BootStrap themes and will not look good without BootStrap. Template files are included in the plugin. You should consider copying those template files to a child theme. Block themes **are not** supported at this time.   

Need support or have a feature request? Please reach out to us here on the WordPress.org forums, on Twitter at [@tournamatch](https://twitter.com/Tournamatch), on Facebook at [Tournamatch](https://www.facebook.com/tournamatch), or visit our website at [www.tournamatch.com](https://www.tournamatch.com).

== Frequently Asked Questions ==

= What theme do you recommend using with Tournamatch? =

Any theme that is designed to with BootStrap should look okay. You will most likely need to make minor CSS modifications for colors, margins, and styles. CSS changes should be made in the WordPress backend, Appearance -> Customize, Additional CSS.

= How do I get to the individual pages? =

You should add links to your menu for the following pages (replace example.com with your URL):  

- example.com/challenges
- example.com/games
- example.com/ladders
- example.com/matches
- example.com/players
- example.com/players/dashboard
- example.com/report
- example.com/teams
- example.com/tournaments

== Screenshots ==

1. A ladder standings page showing 8 competitors, total points, games played, wins, losses, draws, win percent, streak, and days idle. Draw match results can be disabled in settings. There are also two icons in the Action column for editing a competitor's standing and removing a competitor.
2. An 8 competitor tournament. You can advance competitors through the brackets by viewing the brackets while authenticated as an admin. Hover the cursor over the gear icon to see the match dropdown menu.
3. A team roster page for team owners to manage who is on their team. Owners can send invites or directly invite other users, and users can also request to join a team.  
4. The WordPress backend **All Tournaments** list. Here you can find actions for **Registration** (for manually registering competitors), **Start**, **Reset**, **Finish**, **Edit**, and **Clone**.
5. The WordPress backend tournament matchs screen. You can manually advance competitors in the tournament, confirm reported matches, or clear match disputes. Users can also report their own matches which the opposite competitors can then confirm. 
6. The WordPress backend **Create new ladder** form. You can choose how many points to reward for wins, losses, and draws.
7. The WordPress backend **Manage Games** page. You can upload game thumnails, create new games, edit existing games, and delete games on this page.

== Changelog ==

= 4.1.0 =
* Added a list of page shortcuts to the WordPress Backend -> Tournamatch page.
* Updated the single player, single team, single tournament, and single ladder template pages to dynamically aggregate tabbed views.
* Added four new filters for single page template views.
* Added view links to the WordPress Backend Ladder list table and Tournament list table.
* Refactored the rest classes to dynamically prepare responses based on item schema.
* Refactored the tournament and ladder admin form to dynamically build form elements.
* Changed where Tournamatch scripts are enqueued so that shortcuts can work on non-Tournamatch pages.
* Fixed a bug causing complete tournaments to display the message "The tournament has not started."
* Added a check to the brackets shortcode to verify the given tournament exists.
* Fixed an incorrectly named primary key on the team members table.
* Fixed an incorrectly named key on the tournaments entries table.
* Fixed a broken link to the competitor flag on the newest members widget.
- Removed missing goals and delta fields from edit ladder competitor page.
- Removed redundant info button on ladder archive page.
- Fixed an issue preventing teams from creating challenges.
- Fixed an issue preventing non-admins from editing their own user profiles.
- Fixed an issue when attempting to create a challenge with no supported ladders or no competitors.
- Fixed occurrences of null dates for scheduled matches and the backend match list table.
- Reporting an unscheduled new match from the results dashboard works as expected.
* Removed commented out code.
* Removed several unused classes and assets.

= 4.0.3 =
* Fixed date localization.
* Fixed a bug cause observed when registering for a ladder. It looks like it is successful and correctly redirects to the ladder standings now.
* Fixed the competitor rank displayed on player and team profiles. It no longer displays as undefinedth.
* Removed a PHP debug notice on the new tournaments screen.
* Added a competitor not found message for backend tournament registration when the entered text is not a registered user.

= 4.0.2 =
* Fixed a PHP 8 compatibility issue with class Match.
* Fixed a bug that could cause a PHP warning in trn_route.

= 4.0.1 =
* Fixed broken flag, blank avatar, and blank game thumbnail path.

= 4.0 =
* The initial release to WordPress.org.
