=== Tournamatch ===
Contributors: tournamatch
Tags: tournament, ladder, standings, bracket, leaderboard, bracket-generator, esports
Requires at least: 4.7
Tested up to: 6.4.0
Stable tag: 4.6.0
Requires PHP: 5.6.20
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A ladder and tournament plugin for eSports, physical sports, board games, and other online gaming leagues.

== Description ==

Use this plugin to create a gaming community with your own tournaments, ladders, competitor challenges, and more. Ladder standings (or "leaderboard") track points, wins, losses, [optionally] draws, games played, win streak, and win percent. Tournament brackets support single elimination head-to-head competitions. Tournamatch also includes match challenges, player profiles, team profiles, ladder and tournament rule pages, match lists, and a player dashboard for managing everything.

Using Tournamatch, you create ladder or tournament events and allow registered users to self-serve. Registered users can create their own teams, join other teams, accept or decline join requests, drop team members, and send email or user team invitations; users can send match challenges; users or admins report, confirm, or dispute match results; ladders and tournaments will update automatically when match results are confirmed.

This plugin includes many pages and shortcodes with user-facing components. You should expect to restyle those using the WordPress Backend -> Appearance, Customize -> Additional CSS page.

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
2. An 8 competitor tournament. You can advance competitors through the brackets by viewing the brackets while authenticated as an admin. Hover the cursor over the gear icon to see the match drop down menu.
3. A team roster page for team owners to manage who is on their team. Owners can send invites or directly invite other users, and users can also request to join a team.  
4. The WordPress backend **All Tournaments** list. Here you can find actions for **Registration** (for manually registering competitors), **Start**, **Reset**, **Finish**, **Edit**, and **Clone**.
5. The WordPress backend tournament matches screen. You can manually advance competitors in the tournament, confirm reported matches, or clear match disputes. Users can also report their own matches which the opposite competitors can then confirm.
6. The WordPress backend **Create new ladder** form. You can choose how many points to reward for wins, losses, and draws.
7. The WordPress backend **Manage Games** page. You can upload game thumbnails, create new games, edit existing games, and delete games on this page.

== Changelog ==

= 4.6.0 =
* New: Added support for admins to add competitors to ladder events.
* Fix: Match confirmation links 404 after 4.5 update.
* Fix: The competitor dropdown select for tournament registrations is not styled correctly.

= 4.5.0 =
* New: Added a filter to rename or move routes to a sub-directory.
* New: Added support for the Trophies extension.
* New: Added numerous actions and filters to Tournamatch REST classes and services.
* Tweak: Added thumbnail and banner image details to game REST route.
* Fix: Users can (but should not be able to) report new matches for inactive ladders.
* Fix: Tournament started email does not send.
* Fix: Tournament eliminated email does not send.
* Fix: Display name is not updated when editing player profile.

= 4.4.2 =
* Fix: Fixed an issue that causes a NULL player name on some versions of MySql.

= 4.4.1 =
* Fix: Social icon fields are missing from REST queries.
* Fix: WordPress backend search for tournaments, ladders, and games now works as expected.

= 4.4.0 =
* Tweak: Updated the HTML structure and CSS for the single page templates.
* New: Added support for the [Profile Social Icons](https://www.tournamatch.com/extensions/trn-profile-social-icons/) plugin add on.
* New: Added support for the [Custom Team Ranks](https://www.tournamatch.com/extensions/trn-team-ranks/) plugin add on.
* New: Added support for the [Manually Seed Tournaments](https://www.tournamatch.com/extensions/trn-manual-seeding/) plugin add on.
* New: Added support for the [Auto-Start Tournaments](https://www.tournamatch.com/extensions/trn-auto-start/) plugin add on.

= 4.3.5 =
* Fix: Ladder competitor streak is not working correctly.
* Fix: If the first competitor chosen when reporting a match on the backend is recorded as a loss, both competitors incorrectly appear as a loss.
* Fix: The magic links in team invitation and match confirmation emails do not work.
* Fix: Clear data under tools does not work as expected.
* Fix: The number of ladder competitors displayed on the ladder archive and ladder single templates is not accurate.
* Fix: Email magic links are now backwards compatible to version 3 to now.
* Fix: Tab navigation does not work correctly if the URL contains a pound sign.
* Fix: Confirmation windows can leak into other confirmation-required actions on the front end.

= 4.3.4 =
* Fix: Banner images are now correctly displayed in full size.

= 4.3.3 =
* Critical: Saving permalinks or changing templates causes you to lose Tournamatch custom rewrite rules.

= 4.3.2 =
* Fix: You are now redirected to the All Ladders screen after successfully creating a new ladder.
* Fix: Flush URL rewrite should only be called on plugin activation and deactivation.
* Fix: Profiles for new users are not created as expected.
* Fix: All new users since 4.3.1 will correctly have their profiles created.

= 4.3.1 =
* Fix: Game archive should not have hand on hover.
* Fix: Player profiles should not display location icon if field is empty.
* Fix: Pending challenges incorrectly uses local time instead of UTC for retrieving challenges.
* Fix: The challenge date used in emails is not formatted in the website's designated time zone and should be.
* Fix: Challenges are incorrectly stored in local time instead of UTC time.
* Fix: Scheduled matches should not display a result on the single match page.

= 4.3.0 =
* Redesigned the following templates: single player, single team, single ladder, single tournament, and single match. These pages now feature a header banner and better organized information.
* Touched up the following templates: archive games, archive ladders, and archive tournaments. These pages have minor appearance tweaks, more information, and less action buttons.
* Added support for player and team banner images for profile pages.
* Added support for dynamic ladder, tournament, and ladder competitor fields.
* Replaced the thumbnail upload in the Games backend with the WordPress media selector, and also added a field for selecting a banner image.
* Advancing a competitor or clearing a reported result while viewing the brackets will correctly redirect you back to the brackets.
* Updated plugin description.

= 4.2.2 =
* Fixed spelling error of the South African flag. It is now displayed correctly as 'South Africa'.
* The tournament registered page will now reload when clicking on the 'Unregister' button.
* Fixed several occurrences of alerts missing the 'trn-' CSS prefix. Those are fixed and are now styled correctly.
* When the file extension of user-submitted avatars for player and team profiles is not allowed, an error message is now displayed.

= 4.2.1 =
* Added appropriate redirects to several user-facing pages if the current visitor is unauthenticated or has insufficient privileges.
* Fixed a failed redirect bug on the matches report screen if the current visitor is unauthenticated or has insufficient privileges.

= 4.2.0 =
* Refactored all styles to be prefixed with 'trn-'. If you previously styled Tournamatch with additional CSS, you should prefix your CSS with 'trn-'. Tournamatch no longer depends on Bootstrap and should look consistent from theme to theme.
* Added support for upgrading from 3.x to 4.x.
* Added support for dynamic player and team fields.
* Fixed several occurrences of PHP warning messages for missing options fields.

= 4.1.0 =
* **Fixed a critical bug in 4.0.x that caused the players list and new teams to fail.**
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
- Fixed an issue causing the challenger and challengee team name to be blank for team challenges.
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
