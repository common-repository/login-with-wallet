<?php

/**
 * Add a widget to WordPress widgets
 * It will show the trigger button
 * options are: widget title and button text
 *
 * @since 1.0.0
 */
class LoginWithWalletWidget extends WP_Widget
{

	/**
	 * Add the widget detail to WP_Widget
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		parent::__construct(
			'login_with_wallet_widget',
			esc_html__('Login With Wallet', 'login-with-wallet'),
			 ['description' => esc_html__('Add the button. It allows users to connect', 'login-with-wallet')]
		);
	}

	/**
	 * Render the widget fron-end UI
	 *
	 * @since 1.0.0
	 * @param  array $args                   WP args
	 * @param  array $instance               widget instance
	 */
	public function widget($args, $instance) {
		$title = apply_filters('widget_title', $instance['title'] );
		$button_title = isset($instance['button_title']) && !empty($instance['button_title']) ? $instance['button_title'] : esc_html__('Connect Wallet', 'login-with-wallet');
		echo wp_kses_post($args['before_widget']);
		if (!empty($title)) {
			echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
		}
		echo '<button class="login-with-wallet-connect" type="button">'.esc_html($button_title).'</button>';
		echo wp_kses_post($args['after_widget']);
	}

	/**
	 * Render back-end widget options UI
	 *
	 * @since 1.0.0
	 * @param  array $instance               widget data
	 */
	public function form($instance) {
		$title = isset($instance['title']) ? $instance['title'] : esc_html__('Connect Wallet', 'login-with-wallet');
		$button_title = isset($instance['button_title']) ? $instance['button_title'] : esc_html__('Connect', 'login-with-wallet');
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'login-with-wallet'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('button_title')); ?>"><?php esc_html_e('Button Title:', 'login-with-wallet'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('button_title')); ?>" name="<?php echo esc_attr($this->get_field_name('button_title')); ?>" type="text" value="<?php echo esc_attr($button_title); ?>" />
		</p>
	<?php
	}

	/**
	 * Update widget option data
	 *
	 * @since 1.0.0
	 * @param  array $new_instance            new data
	 * @param  array $old_instance            old data
	 * @return array               						updated data
	 */
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (isset($new_instance['title']) && !empty($new_instance['title'])) ? sanitize_text_field(strip_tags($new_instance['title'])) : '';
		$instance['button_title'] = (isset($new_instance['button_title']) && !empty($new_instance['button_title'])) ? sanitize_text_field(strip_tags($new_instance['button_title'])) : '';
		return $instance;
	}
}
