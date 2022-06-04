<?php
/**
 * Manages tracking users and guests currently online.
 *
 * @link       https://www.tournamatch.com
 * @since      3.16.0
 *
 * @package    Tournamatch
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Tournamatch_Online_Users' ) ) {

	/**
	 * Inspired by https://github.com/lesterchan/wp-useronline and https://gearside.com/online-users-wordpress-currently-active-last-seen/.
	 *
	 * See https://github.com/lesterchan/wp-useronline for a much more detailed users online statistics plugin.
	 */

	/**
	 * Manages tracking users and guests currently online.
	 *
	 * @since      3.16.0
	 *
	 * @package    Tournamatch
	 * @author     Tournamatch <support@tournamatch.com>
	 */
	class Tournamatch_Online_Users {

		/**
		 * Initializes the Tournamatch admin components.
		 *
		 * @since 3.16.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'update_online_users' ) );
			add_action( 'admin_init', array( $this, 'update_online_users' ) );
		}

		/**
		 * Maintains currently online users using a transient.
		 *
		 * @since 3.16.0
		 *
		 * @return bool
		 */
		public function update_online_users() {
			$user_agent = $this->get_user_agent();

			// Do nothing if this is a bot.
			if ( $this->is_bot( $user_agent ) ) {
				return false;
			}

			$online_users = get_transient( 'trn_online_users' );
			if ( ! $online_users ) {
				$online_users = array(
					'users'       => array(),
					'guests'      => array(),
					'last_online' => 0,
				);
			}

			$user = wp_get_current_user();

			if ( $user->ID ) {
				$user_id   = $user->ID;
				$user_type = 'users';
			} else {
				$user_id   = '0-' . $this->get_ip() . '-' . $user_agent;
				$user_type = 'guests';
			}

			// Set the current last seen time.
			$online_users[ $user_type ][ $user_id ] = time();

			// Filter out any user or guest that hasn't been seen recently.
			$online_users['users']       = array_filter( $online_users['users'], array( $this, 'is_recent_user_time' ) );
			$online_users['guests']      = array_filter( $online_users['guests'], array( $this, 'is_recent_user_time' ) );
			$online_users['last_online'] = $user->ID ?: $online_users['last_online'];

			set_transient( 'trn_online_users', $online_users );

			return true;
		}

		/**
		 * Evaluates whether a user time is considered recent.
		 *
		 * @since 3.16.0
		 *
		 * @param integer $user_time A time() value.
		 *
		 * @return bool
		 */
		public function is_recent_user_time( $user_time ) {
			return ( $user_time + 600 ) > time();
		}

		/**
		 * Retrieves the user agent.
		 *
		 * @since 3.16.0
		 *
		 * @return string
		 */
		public function get_user_agent() {
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$user_agent = wp_strip_all_tags( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
			} else {
				$user_agent = '';
			}

			return $user_agent;
		}

		/**
		 * Retrieves the IP address of this request.
		 *
		 * @since 3.16.0
		 *
		 * @return mixed
		 */
		public function get_ip() {
			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			}

			list( $ip_address ) = explode( ',', $ip_address );

			return $ip_address;
		}

		/**
		 * Evaluates whether the current request is a bot.
		 *
		 * @since 3.16.0
		 *
		 * @param string $user_agent Name of the user agent.
		 *
		 * @return bool
		 */
		public function is_bot( $user_agent ) {
			foreach ( $this->get_bot_list() as $name => $bot_agent ) {
				if ( stristr( $user_agent, $bot_agent ) !== false ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Retrieves a list of known bots.
		 *
		 * @since 3.16.0
		 *
		 * @return array
		 */
		public function get_bot_list() {
			$bots = array(
				'360Spider'                 => '360spider',
				'AddThis'                   => 'addthis',
				'AdScanner'                 => 'adscanner',
				'AHC'                       => 'AHC',
				'Ahrefs'                    => 'ahrefsbot',
				'Alex'                      => 'ia_archiver',
				'AllTheWeb'                 => 'fast-webcrawler',
				'Altavista'                 => 'scooter',
				'Amazon'                    => 'amazonaws.com',
				'Anders Pink'               => 'anderspinkbot',
				'Apple'                     => 'applebot',
				'Archive.org'               => 'archive.org_bot',
				'Ask Jeeves'                => 'jeeves',
				'Aspiegel'                  => 'AspiegelBot',
				'Axios'                     => 'axios',
				'Baidu'                     => 'baidu',
				'Become.com'                => 'become.com',
				'Bing'                      => 'bingbot',
				'Bing Preview'              => 'bingpreview',
				'Blackboard'                => 'Blackboard',
				'BLEXBot'                   => 'blexbot',
				'Bloglines'                 => 'bloglines',
				'Blog Search Engine'        => 'blogsearch',
				'BUbiNG'                    => 'bubing',
				'Buck'                      => 'Buck',
				'CCBot'                     => 'ccbot',
				'CFNetwork'                 => 'cfnetwork',
				'CheckMarkNetwork'          => 'CheckMarkNetwork',
				'Cliqzbot'                  => 'cliqzbot',
				'Coccoc'                    => 'coccocbot',
				'Crawl'                     => 'crawl',
				'Curl'                      => 'Curl',
				'Cyotek'                    => 'Cyotek',
				'Daum'                      => 'Daum',
				'Dispatch'                  => 'Dispatch',
				'DomainCrawler'             => 'domaincrawler',
				'DotBot'                    => 'dotbot',
				'DuckDuckGo'                => 'duckduckbot',
				'EveryoneSocialBot'         => 'everyonesocialbot',
				'Exalead'                   => 'exabot',
				'Facebook'                  => 'facebook',
				'Facebook Preview'          => 'facebookexternalhit',
				'faceBot'                   => 'facebot',
				'Feedfetcher'               => 'Feedfetcher',
				'Findexa'                   => 'findexa',
				'Flipboard Preview'         => 'FlipboardProxy',
				'Gais'                      => 'gaisbo',
				'Gigabot'                   => 'gigabot',
				'Gluten Free'               => 'gluten free crawler',
				'Go-http-client'            => 'Go-http-client',
				'Goforit'                   => 'GOFORITBOT',
				'Google'                    => 'google',
				'Grid'                      => 'gridbot',
				'GroupHigh'                 => 'grouphigh',
				'Heritrix'                  => 'heritrix',
				'IA Archiver'               => 'ia_archiver',
				'Inktomi'                   => 'slurp@inktomi',
				'IPS Agent'                 => 'ips-agent',
				'James'                     => 'james bot',
				'Jobboerse'                 => 'Jobboerse',
				'KomodiaBot'                => 'komodiabot',
				'Konqueror'                 => 'konqueror',
				'Lindex'                    => 'linkdexbot',
				'Linguee'                   => 'Linguee',
				'Linkfluence'               => 'linkfluence',
				'Lycos'                     => 'lycos',
				'Maui'                      => 'mauibot',
				'Mediatoolkit'              => 'mediatoolkitbot',
				'MegaIndex'                 => 'MegaIndex',
				'MetaFeedly'                => 'MetaFeedly',
				'MetaURI'                   => 'metauri',
				'MJ12bot'                   => 'mj12bot',
				'MojeekBot'                 => 'mojeekBot',
				'Moreover'                  => 'moreover',
				'MSN'                       => 'msnbot',
				'NBot'                      => 'nbot',
				'Node-Fetch'                => 'node-fetch',
				'oBot'                      => 'oBot',
				'NextLinks'                 => 'findlinks',
				'Panscient'                 => 'panscient.com',
				'PaperLiBot'                => 'paperliBot',
				'PetalBot'                  => 'PetalBot',
				'PhantomJS'                 => 'phantomjs',
				'Picsearch'                 => 'picsearch',
				'Proximic'                  => 'proximic',
				'PubSub'                    => 'pubsub',
				'Radian6'                   => 'radian6',
				'RadioUserland'             => 'userland',
				'RyteBot'                   => 'RyteBot',
				'Moz'                       => 'rogerbot',
				'Qwantify'                  => 'Qwantify',
				'Scoutjet'                  => 'Scoutjet',
				'Screaming Frog SEO Spider' => 'Screaming Frog SEO Spider',
				'SEOkicks'                  => 'seokicks-robot',
				'SemrushBot'                => 'semrushbot',
				'SerendeputyBot'            => 'serendeputybot',
				'Seznam'                    => 'seznam',
				'SirdataBot '               => 'SirdataBot ',
				'SiteExplorer'              => 'siteexplorer',
				'Sixtrix'                   => 'SIXTRIX',
				'Slurp'                     => 'slurp',
				'SMTBot'                    => 'SMTBot',
				'Sogou'                     => 'Sogou',
				'OpenLinkProfiler.org'      => 'spbot',
				'SurveyBot'                 => 'surveybot',
				'Syndic8'                   => 'syndic8',
				'Technorati'                => 'technorati',
				'TelegramBot'               => 'telegrambot',
				'Thither'                   => 'thither',
				'TraceMyFile'               => 'tracemyfile',
				'Trendsmap'                 => 'trendsmap',
				'Turnitin.com'              => 'turnitinbot',
				'The Tweeted Times'         => 'tweetedtimes',
				'TweetmemeBot'              => 'tweetmemeBot',
				'Twingly'                   => 'twingly',
				'Twitter'                   => 'twitterbot',
				'VoilaBot'                  => 'VoilaBot',
				'Wget'                      => 'wget',
				'WhatsApp'                  => 'whatsapp',
				'WhoisSource'               => 'surveybot',
				'WiseNut'                   => 'zyborg',
				'Wotbox'                    => 'wotbox',
				'Xenu Link Sleuth'          => 'xenu link sleuth',
				'XoviBot'                   => 'xoviBot',
				'Yahoo'                     => 'yahoo',
				'Yandex'                    => 'yandex',
				'YisouSpider'               => 'yisouspider',
			);

			return apply_filters( 'trn_bot_list', $bots );
		}
	}
}

new Tournamatch_Online_Users();
