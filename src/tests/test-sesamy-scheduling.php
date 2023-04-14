<?php

class Test_Sesamy_Scheduling_Url extends WP_UnitTestCase {

	public static function setUpBeforeClass(): void {

		// Enable sesamy for posts
		add_option( 'sesamy_content_types', array( 'post' ) );
	}

	public function test_schedule_is_locking_post() {

		// Arrange
		$locked_from = time() - HOUR_IN_SECONDS;

		$post_id = wp_insert_post(
			array(
				'post_title'   => wp_strip_all_tags( 'Test post' ),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'meta_input'   => array(
					'_sesamy_locked'      => false,
					'_sesamy_locked_from' => $locked_from,
				),
			)
		);

		$scheduling           = new Sesamy_Scheduling();
		$post_settings_before = Sesamy_Post_Properties::get_post_settings( $post_id );

		// Act
		$scheduling->post_lock_callback( $post_id, 'locked_from' );
		$post = get_post( $post_id );

		$post_settings_after = Sesamy_Post_Properties::get_post_settings( $post_id );

		// Assert
		$this->assertFalse( $post_settings_before['locked'] );
		$this->assertTrue( $post_settings_after['locked'] );
		$this->assertTrue( Sesamy_Post_Properties::is_locked( $post_id ) );
	}

	public function test_schedule_is_unlocking_post() {

		// Arrange
		$unlocked_from = time() - HOUR_IN_SECONDS;

		$post_id = wp_insert_post(
			array(
				'post_title'   => wp_strip_all_tags( 'Test post' ),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'meta_input'   => array(
					'_sesamy_locked'      => true,
					'_sesamy_locked_until' => $unlocked_from,
				),
			)
		);

		$scheduling           = new Sesamy_Scheduling();
		$post_settings_before = Sesamy_Post_Properties::get_post_settings( $post_id );

		// Act
		$scheduling->post_lock_callback( $post_id, 'locked_until' );

		$post_settings_after = Sesamy_Post_Properties::get_post_settings( $post_id );

		// Assert
		$this->assertTrue( $post_settings_before['locked'] );
		$this->assertFalse( $post_settings_after['locked'] );
		$this->assertFalse( Sesamy_Post_Properties::is_locked( $post_id ) );
	}


	public function test_saving_post_with_schedule_is_added_to_cron() {

		// Arrange
		$locked_from = time() - HOUR_IN_SECONDS;

		$post_id = wp_insert_post(
			array(
				'post_title'   => wp_strip_all_tags( 'Test post' ),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'meta_input'   => array(
					'_sesamy_locked'      => false,
					'_sesamy_locked_from' => $locked_from,
				),
			)
		);

		// Act
		$schedule = wp_get_scheduled_event( 'sesamy_lock_schedule', array( $post_id, 'locked_from' ) );

		// Assert
		$this->assertTrue( false !== $schedule );
		$this->assertSame( $schedule->timestamp, $locked_from );
	}

}
