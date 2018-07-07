<?php
/*
Plugin Name: Topbar Countdown
Plugin URI: https://pressmaximum.com/
Description: Add a banner on the top of screen with countdown clock and custom message.
Author: PressMaximum
Author URI: https://pressmaximum.com/
Version: 0.0.1
Text Domain: topbar-countdown
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


class Topbar_Countdown {
	public $headline = '';
	public $clock_title = '';
	public $code = 'COUPON_CODE';

	function __construct() {
		add_action( 'wp_footer', array( $this, 'add_bar' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'css' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'wp_head', array( $this, 'custom_style' ), 95 );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );

		$this->headline    = __( '<strong>Special Offer:</strong> Get 20% Off on Product - Use Coupon Code', 'topbar-countdown' );
		$this->clock_title = __( 'Sale end in', 'topbar-countdown' );
	}

	function settings_link( $links ) {
		$new_link   = array();
		$new_link[] = '<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=topbar_countdown' ) ) . '">' . __( 'Go To Settings', 'topbar-countdown' ) . '</a>';

		return array_merge( $new_link, $links );
	}

	function show_on_fixed() {
		return get_theme_mod( 'topbar_countdown_type', 'fixed' ) == 'fixed';
	}

	function show_on_not_fixed() {
		return ! ( get_theme_mod( 'topbar_countdown_type', 'fixed' ) == 'fixed' );
	}

	function customize_register( $wp_customize ) {

		$wp_customize->add_section( 'topbar_countdown', array(
			'title'    => __( 'Topbar Countdown', 'topbar-countdown' ),
			'priority' => 120,
		) );

		$wp_customize->add_setting( 'topbar_countdown_enable', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'topbar_countdown_enable', array(
			'label'    => __( 'Enable', 'topbar-countdown' ),
			'section'  => 'topbar_countdown',
			'settings' => 'topbar_countdown_enable',
			'type'     => 'checkbox',
		) );


		$wp_customize->add_setting( 'topbar_countdown_type', array(
			'default'           => 'fixed',
			'sanitize_callback' => 'sanitize_text_field',
		) );


		$wp_customize->add_control( 'topbar_countdown_type', array(
			'label'    => __( 'Countdown type', 'topbar-countdown' ),
			'section'  => 'topbar_countdown',
			'settings' => 'topbar_countdown_type',
			'type'     => 'select',
			'choices'  => array(
				'fixed' => __( 'Fixed timer for all visits', 'topbar-countdown' ),
				'start' => __( 'Start the countdown when first visit for each user.', 'topbar-countdown' ),
			)
		) );


		$wp_customize->add_setting( 'topbar_countdown_endate', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_text_field',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Date_Time_Control(
			$wp_customize,
			'topbar_countdown_endate',
			array(
				'label'              => __( 'End date', 'topbar-countdown' ),
				'section'            => 'topbar_countdown',
				'settings'           => 'topbar_countdown_endate',
				'twelve_hour_format' => false,
				'active_callback'    => array( $this, 'show_on_fixed' ),
			)
		) );

		$wp_customize->add_setting( 'topbar_countdown_time', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',

		) );

		$wp_customize->add_control( 'topbar_countdown_time',
			array(
				'label'           => __( 'Time for the countdown topbar to be visible', 'topbar-countdown' ),
				'description'     => __( 'Format: DD:HH:MM, DD: number of the day, HH: number of the hours, MM: number of minute.', 'topbar-countdown' ),
				'section'         => 'topbar_countdown',
				'settings'        => 'topbar_countdown_time',
				'type'            => 'text',
				'placeholder'     => '03:23:59',
				'active_callback' => array( $this, 'show_on_not_fixed' ),
			)
		);


		$wp_customize->add_setting( 'topbar_countdown_posts', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( 'topbar_countdown_posts', array(
			'label'       => __( 'Show on post IDs', 'topbar-countdown' ),
			'description' => __( 'Display on specific page, post type. Separated by comma', 'topbar-countdown' ),
			'section'     => 'topbar_countdown',
			'settings'    => 'topbar_countdown_posts',
			'type'        => 'text',
		) );


		$wp_customize->add_setting( 'topbar_countdown_headline', array(
			'default'           => $this->headline,
			'sanitize_callback' => 'wp_kses_post',
		) );

		$wp_customize->add_control( 'topbar_countdown_headline', array(
			'label'       => __( 'Headline', 'topbar-countdown' ),
			'description' => __( 'HTML code allowed, use <code>code</code> tag to wrap coupon code.', 'topbar-countdown' ),
			'section'     => 'topbar_countdown',
			'settings'    => 'topbar_countdown_headline',
			'type'        => 'textarea',
		) );

		$wp_customize->add_setting( 'topbar_countdown_coupon', array(
			'default'           => $this->code,
			'sanitize_callback' => 'wp_kses_post',
		) );

		$wp_customize->add_control( 'topbar_countdown_coupon', array(
			'label'    => __( 'Countdown coupon', 'topbar-countdown' ),
			'section'  => 'topbar_countdown',
			'settings' => 'topbar_countdown_coupon',
			'type'     => 'text',
		) );


		$wp_customize->add_setting( 'topbar_countdown_title', array(
			'default'           => $this->clock_title,
			'sanitize_callback' => 'wp_kses_post',
		) );

		$wp_customize->add_control( 'topbar_countdown_title', array(
			'label'    => __( 'Countdown box text', 'topbar-countdown' ),
			'section'  => 'topbar_countdown',
			'settings' => 'topbar_countdown_title',
			'type'     => 'text',
		) );

		$wp_customize->add_setting( 'topbar_countdown_box_type', array(
			'default'           => 'v',
			'sanitize_callback' => 'wp_kses_post',
		) );

		$wp_customize->add_control( 'topbar_countdown_box_type', array(
			'label'    => __( 'Countdown box type', 'topbar-countdown' ),
			'section'  => 'topbar_countdown',
			'settings' => 'topbar_countdown_box_type',
			'type'     => 'select',
			'choices'  => array(
				'v' => __( 'Vertical', 'topbar-countdown' ),
				'h' => __( 'Horizontal', 'topbar-countdown' ),
			)
		) );


		// Style
		$wp_customize->add_setting( 'topbar_countdown_bg', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_hex_color',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			'topbar_countdown_bg',
			array(
				'label'    => __( 'Background', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_bg',
			)
		) );

		$wp_customize->add_setting( 'topbar_countdown_color', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_hex_color',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			'topbar_countdown_color',
			array(
				'label'    => __( 'Color', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_color',
			)
		) );


		$wp_customize->add_setting( 'topbar_countdown_clock_bg', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_hex_color',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			'topbar_countdown_clock_bg',
			array(
				'label'    => __( 'Countdown Background', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_clock_bg',
			)
		) );

		$wp_customize->add_setting( 'topbar_countdown_clock_color', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_hex_color',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			'topbar_countdown_clock_color',
			array(
				'label'    => __( 'Countdown color', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_clock_color',
			)
		) );

		$wp_customize->add_setting( 'topbar_countdown_coupon_color', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_hex_color',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			'topbar_countdown_coupon_color',
			array(
				'label'    => __( 'Coupon color', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_coupon_color',
			)
		) );

		$wp_customize->add_setting( 'topbar_countdown_coupon_bg', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_hex_color',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			'topbar_countdown_coupon_bg',
			array(
				'label'    => __( 'Coupon background', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_coupon_bg',
			)
		) );


		$wp_customize->add_setting( 'topbar_countdown_border_color', array(
			'default'            => '',
			'sanitize_callback'  => 'sanitize_hex_color',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			'topbar_countdown_border_color',
			array(
				'label'    => __( 'Border color', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_border_color',
			)
		) );


		$wp_customize->add_setting( 'topbar_countdown_border_w', array(
			'default'            => '',
			'sanitize_callback'  => 'absint',
			'twelve_hour_format' => false,
		) );


		$wp_customize->add_control( 'topbar_countdown_border_w',
			array(
				'label'    => __( 'Border width', 'topbar-countdown' ),
				'section'  => 'topbar_countdown',
				'settings' => 'topbar_countdown_border_w',
			) );
	}

	function custom_style() {
		$bg    = sanitize_hex_color( get_theme_mod( 'topbar_countdown_bg' ) );
		$color = sanitize_hex_color( get_theme_mod( 'topbar_countdown_color' ) );

		$clock_bg    = sanitize_hex_color( get_theme_mod( 'topbar_countdown_clock_bg' ) );
		$clock_color = sanitize_hex_color( get_theme_mod( 'topbar_countdown_clock_color' ) );

		$border_color = sanitize_hex_color( get_theme_mod( 'topbar_countdown_border_color' ) );
		$border_w     = absint( get_theme_mod( 'topbar_countdown_border_w' ) );

		$c_color = sanitize_hex_color( get_theme_mod( 'topbar_countdown_coupon_color' ) );
		$c_bg    = sanitize_hex_color( get_theme_mod( 'topbar_countdown_coupon_bg' ) );


		$css = '';

		if ( $bg ) {
			$css .= ".ct-countdown .ct-countdown-cont-w{background-color: {$bg};} .ct-countdown.ct-closed .ct-close{ background-color: {$bg}; } ";
		}
		if ( $color ) {
			$css .= ".ct-countdown .ct-countdown-cont-w {color: {$color};} .ct-countdown .ct-close svg { fill: {$color}; } ";
		}

		if ( $clock_bg ) {
			$css .= ".ct-countdown-cont .ct-timer{background-color: {$clock_bg};} ";
		}
		if ( $clock_color ) {
			$css .= ".ct-countdown-cont .ct-timer{color: {$clock_color};} ";
		}

		if ( $c_bg ) {
			$css .= ".ct-countdown-cont .ct-text code {background-color: {$c_bg};} ";
		}
		if ( $c_color ) {
			$css .= ".ct-countdown-cont .ct-text code{color: {$c_color};} ";
		}

		if ( $border_color ) {
			$css .= ".ct-countdown .ct-countdown-cont-w{border-bottom-color: {$border_color};} ";
		}

		if ( $border_w !== false ) {
			$css .= ".ct-countdown .ct-countdown-cont-w{border-bottom-width: {$border_w}px;} ";
		}

		if ( $css ) {
			?>
            <style type="text/css">
                <?php
				echo $css;
				?>
            </style>
			<?php
		}
	}

	function css() {
		if ( ! get_theme_mod( 'topbar_countdown_enable' ) ) {
			return;
		}
		wp_enqueue_style( 'topbar-countdown', plugins_url( 'style.css', __FILE__ ), array(), false );
	}

	function add_bar() {

		if ( ! get_theme_mod( 'topbar_countdown_enable' ) ) {
			return;
		}

		$post_ids = get_theme_mod( 'topbar_countdown_posts' );
		$post_ids = explode( ',', $post_ids );

		$post_ids = array_map( 'absint', $post_ids );
		$post_ids = array_filter( $post_ids );
		if ( count( $post_ids ) ) {
			$id = get_the_ID();
			if ( ! in_array( $id, $post_ids ) ) {
				return;
			}
		}

		$current_date = current_time( 'timestamp' ) * 1000;// to millisecond

		$fixed_date_time = get_theme_mod( 'topbar_countdown_endate' );

		if ( ! $fixed_date_time ) {
			$fixed_date_time = 0;
		} else {
			$fixed_date_time = strtotime( $fixed_date_time ) * 1000; // to millisecond
		}

		$time_sting = get_theme_mod( 'topbar_countdown_time' );
		$time_sting = explode( ':', $time_sting );
		if ( count( $time_sting ) >= 3 ) {
			$time_sting = array_map( 'floatval', $time_sting );
			$time_sting = ( $time_sting[0] * DAY_IN_SECONDS ) + ( $time_sting[1] * HOUR_IN_SECONDS ) + ( $time_sting[2] * MINUTE_IN_SECONDS );
			$time_sting = $current_date + $time_sting * 1000; // to millisecond
		} else {
			$time_sting = 0;
		}

		$settings = array(
			'type'         => get_theme_mod( 'topbar_countdown_type', 'fixed' ),
			'current_date' => $current_date,
			'fixed_time'   => $fixed_date_time,
			'time'         => $time_sting,
			'clear'        => false,
		);

		$settings['_key'] = 'ct_endtime_' . $settings['type'];

		$title = get_theme_mod( 'topbar_countdown_title', $this->clock_title );

		$type = 'ct-time-type-' . get_theme_mod( 'topbar_countdown_box_type', 'v' );

		$text = get_theme_mod( 'topbar_countdown_headline', $this->headline );
		$code = get_theme_mod( 'topbar_countdown_coupon', $this->code );
		if ( $text ) {
			$text = '<div class="ct-text-w">' . $text . '</div>';
		}

		if ( $code ) {
			$code = '<code>' . $code . '</code>';
		}
		$text .= $code;


		if ( is_customize_preview() ) {
			$settings['clear'] = 1;
			unset( $_COOKIE[ $settings['_key'] ] );
		}

		?>
        <div class="ct-countdown <?php echo esc_attr( $type ); ?>">
            <span class="ct-close">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 22.88 22.88"  xml:space="preserve"><path d="M0.324,1.909c-0.429-0.429-0.429-1.143,0-1.587c0.444-0.429,1.143-0.429,1.587,0l9.523,9.539l9.539-9.539c0.429-0.429,1.143-0.429,1.571,0c0.444,0.444,0.444,1.159,0,1.587l-9.523,9.524l9.523,9.539c0.444,0.429,0.444,1.143,0,1.587c-0.429,0.429-1.143,0.429-1.571,0l-9.539-9.539l-9.523,9.539c-0.444,0.429-1.143,0.429-1.587,0c-0.429-0.444-0.429-1.159,0-1.587l9.523-9.539L0.324,1.909z"/><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
            </span>
            <div class="ct-countdown-inner">

                <div class="ct-countdown-cont-w">
                    <div class="ct-countdown-cont">

                        <div class="ct-timer">
							<?php if ( $title ) { ?>
                                <div class="ct-clock-title"><?php echo $title; ?></div>
							<?php } ?>
                            <div class="ct-num" title="<?php esc_attr_e( 'Days', 'topbar-countdown' ); ?>">
                                <span class="ct-days">00</span>
                            </div>
                            <div class="ct-num" title="<?php esc_attr_e( 'Hours', 'topbar-countdown' ); ?>">
                                <span class="ct-hours">00</span>
                            </div>
                            <div class="ct-num" title="<?php esc_attr_e( 'Minutes', 'topbar-countdown' ); ?>">
                                <span class="ct-minutes">00</span>
                            </div>
                            <div class="ct-num" title="<?php esc_attr_e( 'Seconds', 'topbar-countdown' ); ?>">
                                <span class="ct-seconds">00</span>
                            </div>
                        </div>

						<?php if ( $text ) { ?>
                            <div class="ct-text">
								<?php echo wp_kses_post( $text ); ?>
                            </div>
						<?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">

            let CountdownTopbar_Settings = <?php echo json_encode( $settings ); ?>;
            console.log('CountdownTopbar_Settings', CountdownTopbar_Settings);

            function getCookie(cname) {
                let name = cname + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }

            function setCookie(cname, cvalue, exdays) {
                let d = new Date();
                d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                let expires = "expires=" + d.toUTCString();
                document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            }

            function deleteCookie(name) {
                document.cookie = name +'=; path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            }

            function ct_countdown() {

                let endDate;

                if( CountdownTopbar_Settings.clear ) {
                    deleteCookie( CountdownTopbar_Settings._key );
                    deleteCookie( 'ct_closed' );
                }

                let timeRemaining_time = getCookie(CountdownTopbar_Settings._key);
                if (timeRemaining_time && timeRemaining_time > 0) {
                    endDate = parseInt(timeRemaining_time);
                } else {
                    if (CountdownTopbar_Settings.type === 'start') {
                        endDate = CountdownTopbar_Settings.time;
                    } else {
                        endDate = CountdownTopbar_Settings.fixed_time;
                    }
                    setCookie(CountdownTopbar_Settings._key, endDate, 365);
                }

                let el = document.querySelector('.ct-countdown');

                if ( getCookie( 'ct_closed' ) === 1 || getCookie( 'ct_closed' ) === '1' ) {
                    el.remove();
                    return ;
                }


                document.querySelector('body').prepend(el);
                let inner = el.querySelector('.ct-countdown-inner');

                let _now = new Date().getTime();

                setTimeout(function () {
                    let h = el.querySelector('.ct-countdown-cont-w').offsetHeight;
                    inner.style.height = h + 'px';
                    el.classList.add('js-added');
                    el.classList.add('ct-showing');

                    setTimeout(function () {
                        inner.style.height = 'auto';
                    }, 300);

                }, 800);

                // Toggle
                el.querySelector('.ct-close').addEventListener("click", function (e) {
                    e.preventDefault();
                    if (el.classList.contains('ct-showing')) {
                        inner.style.height = '0px';
                        el.classList.remove('ct-showing');
                        el.classList.add('ct-closed');
                        setCookie('ct_closed', 1, 360 );
                    } else {
                        let h = inner.querySelector('.ct-countdown-cont-w').offsetHeight;
                        inner.style.height = h + 'px';
                        el.classList.remove('ct-closed');
                        el.classList.add('ct-showing');
                    }
                });


                let days, hours, minutes, seconds;

                endDate = new Date(endDate).getTime();

                if (isNaN(endDate)) {
                    return;
                }

                window.addEventListener("resize", resizeThrottler, false);
                let resizeTimeout;

                function resizeThrottler() {

                    if (!resizeTimeout) {
                        resizeTimeout = setTimeout(function () {
                            resizeTimeout = null;
                            let h = inner.querySelector('.ct-countdown-cont-w').offsetHeight;
                            inner.style.height = h + 'px';
                            setTimeout(function () {
                                inner.style.height = 'auto';
                            }, 300);
                        }, 66);
                    }

                }

                setInterval(calculate, 1000);

                function calculate() {
                    let startDate = new Date();
                    startDate = startDate.getTime();

                    let timeRemaining = parseInt((endDate - startDate) / 1000);

                    if (timeRemaining >= 0) {
                        days = parseInt(timeRemaining / 86400);
                        timeRemaining = (timeRemaining % 86400);

                        hours = parseInt(timeRemaining / 3600);
                        timeRemaining = (timeRemaining % 3600);

                        minutes = parseInt(timeRemaining / 60);
                        timeRemaining = (timeRemaining % 60);

                        seconds = parseInt(timeRemaining);

                        el.querySelector(".ct-days").innerHTML = ('0' + parseInt(days, 10)).slice(-2);
                        el.querySelector(".ct-hours").innerHTML = ("0" + hours).slice(-2);
                        el.querySelector(".ct-minutes").innerHTML = ("0" + minutes).slice(-2);
                        el.querySelector(".ct-seconds").innerHTML = ("0" + seconds).slice(-2);
                    } else {
                        return;
                    }
                }
            }

            window.addEventListener("load", function (event) {
                ct_countdown();
            });

        </script>
		<?php
	}
}

new Topbar_Countdown();