<?php

/**
 * Handles scheduling of events for posts based on date tiem
 */
class Sesamy_Scheduling {

	public function after_insert_post( $post_id, $post, $update, $post_before ) {

		if ( ! in_array( $post->post_type, sesamy_get_enabled_post_types(), true ) ) {
			return;
		}

		$post_settings = Sesamy_Post_Properties::get_post_settings( $post_id );

		// Schedule event for locking the post if locked_from is set and not already locked. Otherwise cleanup schedule
		if ( false === Sesamy_Post_Properties::is_locked( $post_id ) && ! empty( $post_settings['locked_from'] ) && $post_settings['locked_from'] > 0 ) {
			$this->schedule_callback( $post_settings['locked_from'], $post_id, 'locked_from' );
		} else {
			$this->unschedule_callback_if_exists( $post_id, 'locked_from' );
		}

		// Schedule event for unlocking post if locked_until is set and the post is locked or will be locked by locked_from. Otherwise cleanup schedule
		if ( ! empty( $post_settings['locked_until'] ) && $post_settings['locked_until'] > 0 && ( Sesamy_Post_Properties::is_locked( $post_id ) || ( ! empty( $post_settings['locked_from'] ) && $post_settings['locked_from'] > 0 ) ) ) {
			$this->schedule_callback( $post_settings['locked_until'], $post_id, 'locked_until' );
		} else {
			$this->unschedule_callback_if_exists( $post_id, 'locked_until' );
		}
	}

	private function unschedule_callback_if_exists( $post_id, $kind ) {
		// Prevent duplicate schedules for post
		$schedule = wp_get_scheduled_event( 'sesamy_lock_schedule', array( $post_id, $kind ) );

		if ( false !== $schedule ) {
			$unschedule = wp_unschedule_event( $schedule->timestamp, 'sesamy_lock_schedule', array( $post_id, $kind ), true );
			if ( is_wp_error( $unschedule ) ) {
				wp_die( esc_html( $unschedule->get_error_message() ) );
			}
		}
	}

	/**
	 * Schedule callback and wp_die if unable to schedyle
	 *
	 * @param [type] $timestamp
	 * @return void
	 */
	private function schedule_callback( $timestamp, $post_id, $kind ) {

		// Prevent duplicate schedules for post
		$schedule = wp_get_scheduled_event( 'sesamy_lock_schedule', array( $post_id, $kind ) );

		// Unschedule if timestamp is different
		if ( false !== $schedule && $schedule->timestamp !== $timestamp ) {

			$this->unschedule_callback_if_exists( $post_id, $kind );

			$schedule = false;
		}

		// Schedule event
		if ( false === $schedule ) {

			$result = wp_schedule_single_event( $timestamp, 'sesamy_lock_schedule', array( $post_id, $kind ), true );

			if ( is_wp_error( $result ) ) {
				wp_die( esc_html( $result->get_error_message() ) );
			}
		}
	}


	/**
	 * Callback from wp_schedule_task to handle locking or unlocking for post
	 *
	 * @return void
	 */
	public function post_lock_callback( $post_id, $kind ) {

		$post_settings = sesamy_get_post_settings( $post_id );

		if ( ! empty( $post_settings['locked_from'] ) && $post_settings['locked_from'] < time() ) {
			update_post_meta( $post_id, '_sesamy_locked', true );
			update_post_meta( $post_id, '_sesamy_locked_from', null );
		}

		if ( ! empty( $post_settings['locked_until'] ) && $post_settings['locked_until'] < time() ) {
			update_post_meta( $post_id, '_sesamy_locked', false );
			update_post_meta( $post_id, '_sesamy_locked_until', null );
		}
	}


}
