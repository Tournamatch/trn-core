<?php
/**
 * Manages email messages sent by Tournamatch.
 *
 * @link       https://www.tournamatch.com
 * @since      3.12.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Tournamatch_Email' ) ) {

	/**
	 * Manages email messages sent by Tournamatch.
	 *
	 * @since      3.12.0
	 * @since      3.21.0 Removed special email handling; You should use a plugin to prettify email markup.
	 *
	 * @package    Tournamatch
	 * @author     Tournamatch <support@tournamatch.com>
	 */
	class Tournamatch_Email {

		/**
		 * Sets up actions and filters for email messages and components.
		 *
		 * @since 3.12.0
		 */
		public function __construct() {
			$email_messages = array(
				'admin_test',
				'admin_match_disputed',
				'challenge_accepted',
				'challenge_declined',
				'challenge_received',
				'match_upcoming',
				'match_disputed',
				'match_reported',
				'membership_invitation_accepted',
				'membership_invitation_declined',
				'membership_request_accepted',
				'membership_request_declined',
				'membership_invited',
				'membership_requested',
				'tournament_eliminated',
				'tournament_matched',
				'tournament_opened',
				'tournament_started',
			);

			$email_messages = apply_filters( 'trn_core_email_messages', $email_messages );

			foreach ( $email_messages as $message ) {
				$message_filter = 'trn_' . $message . '_message';

				if ( method_exists( $this, $message ) ) {
					add_filter( $message_filter, array( $this, $message ), 10, 2 );
				}

				add_action(
					'trn_notify_' . $message,
					function ( $to, $subject, $data ) use ( $message_filter ) {
							return $this->send_email( $message_filter, $to, $subject, $data );
					},
					10,
					3
				);
			}
		}

		/**
		 * Sends an email message.
		 *
		 * @since 3.12.0
		 *
		 * @param string       $message_filter  The message filter to apply to retrieve the message to send to the user.
		 * @param string|array $to              Array or comma-separated list of email addresses to send message.
		 * @param string       $subject         Email subject.
		 * @param array        $data            Collection of variables necessary for building the email message.
		 *
		 * @return bool True if email sent successfully, false otherwise.
		 */
		public function send_email( $message_filter, $to, $subject, $data ) {

			// Determine actual destination.
			if ( is_array( $to ) ) {
				$to = sprintf( '%s <%s>', $to['name'], $to['email'] );
			}

			$email = array(
				'to'      => $to,
				'subject' => $subject,
				'headers' => '',
			);

			$email = apply_filters( $message_filter, $email, $data );

			$silence_email = false;
			if ( file_exists( __TRNPATH . 'tournamatch.dev.php' ) ) {
				include __TRNPATH . 'tournamatch.dev.php';
			}

			return $silence_email ?: wp_mail( $email['to'], $email['subject'], $email['message'], $email['headers'] );
		}

		/**
		 * Body for the admin test email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function admin_test( $email, $data ) {
			$email['message'] = esc_html__(
				'Hello,

This is a test message sent from ###SITENAME###. If you are reading this, than it looks like your email settings are fine.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_admin_test_email', $email, $data );

			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the admin match disputed notification.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function admin_match_disputed( $email, $data ) {
			$email['message'] = esc_html__(
				'Competitor ###DISPUTER_NAME### has disputed the result reported by ###DISPUTEE_NAME###.

Click the link below to manage match disputes:

###MANAGE_DISPUTES_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_admin_match_disputed_email', $email, $data );

			$email['message'] = str_replace( '###DISPUTER_NAME###', $data['disputer'], $email['message'] );
			$email['message'] = str_replace( '###DISPUTEE_NAME###', $data['disputee'], $email['message'] );
			$email['message'] = str_replace( '###MANAGE_DISPUTES_URL###', $data['disputes_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user challenge accepted email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function challenge_accepted( $email, $data ) {
			$email['message'] = esc_html__(
				'Competitor ###COMPETITOR_NAME### has accepted your challenge.

Click the link below to view challenge details:

###CHALLENGE_DETAILS_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_challenge_accepted_email', $email, $data );

			$email['message'] = str_replace( '###COMPETITOR_NAME###', $data['opponent'], $email['message'] );
			$email['message'] = str_replace( '###CHALLENGE_DETAILS_URL###', $data['challenge_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user challenge declined email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function challenge_declined( $email, $data ) {
			$email['message'] = esc_html__(
				'Competitor ###COMPETITOR_NAME### has declined your challenge.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_challenge_declined_email', $email, $data );

			$email['message'] = str_replace( '###COMPETITOR_NAME###', $data['opponent'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user challenge received email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function challenge_received( $email, $data ) {
			$email['message'] = esc_html__(
				'Competitor ###CHALLENGER_NAME### has challenged you to a ###LADDER_NAME### match on ###CHALLENGE_DATE###.

Click the link below to view the challenge details and respond:

###CHALLENGE_DETAILS_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_challenge_received_email', $email, $data );

			$email['message'] = str_replace( '###CHALLENGER_NAME###', $data['challenger'], $email['message'] );
			$email['message'] = str_replace( '###LADDER_NAME###', $data['ladder_name'], $email['message'] );
			$email['message'] = str_replace( '###CHALLENGE_DATE###', $data['challenge_date'], $email['message'] );
			$email['message'] = str_replace( '###CHALLENGE_DETAILS_URL###', $data['challenge_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user upcoming match email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function match_upcoming( $email, $data ) {
			$email['message'] = esc_html__(
				'You have an upcoming match against ###OPPONENT_NAME### on ###MATCH_DATE###.

Click the link below to view the match details:

###MATCH_DETAILS_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_match_upcoming_email', $email, $data );

			$email['message'] = str_replace( '###OPPONENT_NAME###', $data['opponent'], $email['message'] );
			$email['message'] = str_replace( '###MATCH_DATE###', $data['match_date'], $email['message'] );
			$email['message'] = str_replace( '###MATCH_DETAILS_URL###', $data['match_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user match disputed email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function match_disputed( $email, $data ) {
			$email['message'] = esc_html__(
				'Competitor ###DISPUTER_NAME### has disputed a result you reported.

If you reported the result incorrectly, please delete the existing report and report the correct result.
If you reported the match correctly, no further action is required at this time.
An admin will advise soon and will reach out to you if necessary.

Click the link below to view the reported result:

###RESULTS_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_match_disputed_email', $email, $data );

			$email['message'] = str_replace( '###DISPUTER_NAME###', $data['disputer'], $email['message'] );
			$email['message'] = str_replace( '###RESULTS_URL###', $data['results_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user match reported email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function match_reported( $email, $data ) {
			$email['message'] = esc_html__(
				'Competitor ###REPORTER_NAME### reported ###RESULT### on ###COMPETITION_TYPE### ###COMPETITION_NAME###.

Click the link below to confirm the result:

###CONFIRM_URL###

If this match was reported incorrectly, please login and visit your results dashboard to dispute the result.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_match_reported_email', $email, $data );

			$email['message'] = str_replace( '###REPORTER_NAME###', $data['opponent'], $email['message'] );
			$email['message'] = str_replace( '###RESULT###', $data['result'], $email['message'] );
			$email['message'] = str_replace( '###COMPETITION_TYPE###', $data['competition_type'], $email['message'] );
			$email['message'] = str_replace( '###COMPETITION_NAME###', $data['competition_name'], $email['message'] );
			$email['message'] = str_replace( '###CONFIRM_URL###', $data['confirm_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user team membership invitation accepted email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function membership_invitation_accepted( $email, $data ) {
			$email['message'] = esc_html__(
				'Your invitation to user ###USER_NAME### to join team ###TEAM_NAME### has been accepted.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_membership_invitation_accepted_email', $email, $data );

			$email['message'] = str_replace( '###USER_NAME###', $data['user_name'], $email['message'] );
			$email['message'] = str_replace( '###TEAM_NAME###', $data['team_name'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;

		}

		/**
		 * Body for the user team membership invitation declined email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function membership_invitation_declined( $email, $data ) {
			$email['message'] = esc_html__(
				'Your invitation to user ###USER_NAME### to join team ###TEAM_NAME### has been declined.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_membership_invitation_declined_email', $email, $data );

			$email['message'] = str_replace( '###USER_NAME###', $data['user_name'], $email['message'] );
			$email['message'] = str_replace( '###TEAM_NAME###', $data['team_name'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user team membership request accepted email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function membership_request_accepted( $email, $data ) {
			$email['message'] = esc_html__(
				'Your request to join team ###TEAM_NAME### has been accepted.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_membership_request_accepted_email', $email, $data );

			$email['message'] = str_replace( '###TEAM_NAME###', $data['team_name'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user team membership declined email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function membership_request_declined( $email, $data ) {
			$email['message'] = esc_html__(
				'Your request to join team ###TEAM_NAME### has been declined.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_membership_request_declined_email', $email, $data );

			$email['message'] = str_replace( '###TEAM_NAME###', $data['team_name'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user team membership invitation email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function membership_invited( $email, $data ) {
			$email['message'] = esc_html__(
				'You have been invited to join team ###TEAM_NAME###. If you do not have an account, you should first register for one.

Click the link below to accept this invitation:

###ACCEPT_URL###

Click the link below to create an account:

###REGISTER_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_membership_invited_email', $email, $data );

			$email['message'] = str_replace( '###TEAM_NAME###', $data['team_name'], $email['message'] );
			$email['message'] = str_replace( '###ACCEPT_URL###', $data['accept_link'], $email['message'] );
			$email['message'] = str_replace( '###REGISTER_URL###', site_url( '/wp-login.php?action=register' ), $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user team membership requested email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function membership_requested( $email, $data ) {
			$email['message'] = esc_html__(
				'User ###USER_NAME### has requested to join team ###TEAM_NAME###.

Click the link below to view your team profile and accept or decline the request:

###PROFILE_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_membership_requested_email', $email, $data );

			$email['message'] = str_replace( '###USER_NAME###', $data['display_name'], $email['message'] );
			$email['message'] = str_replace( '###TEAM_NAME###', $data['team_name'], $email['message'] );
			$email['message'] = str_replace( '###PROFILE_URL###', $data['team_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user tournament elimination email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function tournament_eliminated( $email, $data ) {
			$email['message'] = esc_html__(
				'You have been eliminated from tournament ###TOURNAMENT_NAME###.

Click the link below to view the tournament:

###TOURNAMENT_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_tournament_eliminated_email', $email, $data );

			$email['message'] = str_replace( '###TOURNAMENT_NAME###', $data['tournament_name'], $email['message'] );
			$email['message'] = str_replace( '###TOURNAMENT_URL###', $data['tournament_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the user tournament match is set email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function tournament_matched( $email, $data ) {
			$email['message'] = esc_html__(
				'Your next tournament match is set.

Click the link below to view the tournament brackets:

###TOURNAMENT_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_tournament_matched_email', $email, $data );

			$email['message'] = str_replace( '###TOURNAMENT_URL###', $data['brackets_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the tournament check ins have started email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function tournament_opened( $email, $data ) {
			$email['message'] = esc_html__(
				'Check ins for tournament ###TOURNAMENT_NAME### have opened and are required.

Click the link below and then click Check In to confirm you are present and ready to play:

###CHECKIN_URL###

Failure to check in will cause you not to be added to the tournament brackets or be eligible to compete.

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_tournament_opened_email', $email, $data );

			$email['message'] = str_replace( '###TOURNAMENT_NAME###', $data['tournament_name'], $email['message'] );
			$email['message'] = str_replace( '###CHECKIN_URL###', $data['checkin_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}

		/**
		 * Body for the tournament has started email.
		 *
		 * @since 3.12.0
		 *
		 * @param array $email Array of email data.
		 * @param array $data Array of data for this instance.
		 *
		 * @return mixed
		 */
		public function tournament_started( $email, $data ) {
			$email['message'] = esc_html__(
				'Tournament ###TOURNAMENT_NAME### has started.

Click the link below to view the tournament brackets:

###BRACKETS_URL###

Regards,
All at ###SITENAME###
###SITEURL###',
				'tournamatch'
			);

			$email = apply_filters( 'trn_tournament_started_email', $email, $data );

			$email['message'] = str_replace( '###TOURNAMENT_NAME###', $data['tournament_name'], $email['message'] );
			$email['message'] = str_replace( '###BRACKETS_URL###', $data['brackets_link'], $email['message'] );
			$email['message'] = str_replace( '###SITENAME###', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $email['message'] );
			$email['message'] = str_replace( '###SITEURL###', esc_url_raw( home_url() ), $email['message'] );

			return $email;
		}
	}

	new Tournamatch_Email();
}
