<?php
/**
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      06/11/2018
 *
 * @package themeisle-onboarding
 */

if ( apply_filters( 'ti_onboarding_filter_module_status', true ) !== true ) {
	return;
}

if ( ! class_exists( 'Themeisle_Onboarding' ) ) {
	require_once dirname( __FILE__ ) . '/class-themeisle-onboarding.php';
}
