<?php
/**
 * Custom widgets.
 *
 * @package GazetteNews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GazetteNews_Popular_Posts extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'gazettenews_popular',
			__( 'Gazette: Popular Posts', 'gazettenews' ),
			array( 'description' => __( 'Most commented or recent posts in a compact list.', 'gazettenews' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Popular Posts', 'gazettenews' );
		$count = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$q = gazettenews_query( array(
			'posts_per_page' => $count,
			'orderby'        => 'comment_count',
			'order'          => 'DESC',
		) );

		if ( $q->have_posts() ) {
			echo '<ul class="gn-post-list">';
			$i = 1;
			while ( $q->have_posts() ) {
				$q->the_post();
				echo '<li>';
				echo '<span class="rank">' . esc_html( str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) ) . '</span>';
				if ( has_post_thumbnail() ) {
					echo '<a class="thumb" href="' . esc_url( get_permalink() ) . '">';
					the_post_thumbnail( 'gazettenews-small' );
					echo '</a>';
				}
				echo '<div><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
				echo '<span class="list-date">' . esc_html( get_the_date() ) . '</span></div>';
				echo '</li>';
				$i++;
			}
			echo '</ul>';
			wp_reset_postdata();
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Popular Posts', 'gazettenews' );
		$count = isset( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		?>
		<p>
			<label><?php esc_html_e( 'Title', 'gazettenews' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Number of posts', 'gazettenews' ); ?></label>
			<input class="tiny-text" type="number" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" value="<?php echo esc_attr( $count ); ?>" min="1" max="12">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( $new['title'] ),
			'count' => absint( $new['count'] ),
		);
	}
}

class GazetteNews_Category_Posts extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'gazettenews_cat_posts',
			__( 'Gazette: Category Posts', 'gazettenews' ),
			array( 'description' => __( 'Latest posts from one category.', 'gazettenews' ) )
		);
	}

	public function widget( $args, $instance ) {
		$cat_id = ! empty( $instance['cat'] ) ? absint( $instance['cat'] ) : 0;
		$count  = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 4;
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : ( $cat_id ? get_cat_name( $cat_id ) : __( 'Latest', 'gazettenews' ) );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$query_args = array( 'posts_per_page' => $count );
		if ( $cat_id ) {
			$query_args['cat'] = $cat_id;
		}
		$q = gazettenews_query( $query_args );

		if ( $q->have_posts() ) {
			echo '<ul class="gn-post-list">';
			while ( $q->have_posts() ) {
				$q->the_post();
				echo '<li>';
				if ( has_post_thumbnail() ) {
					echo '<a class="thumb" href="' . esc_url( get_permalink() ) . '">';
					the_post_thumbnail( 'gazettenews-small' );
					echo '</a>';
				}
				echo '<div><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
				echo '<span class="list-date">' . esc_html( get_the_date() ) . '</span></div>';
				echo '</li>';
			}
			echo '</ul>';
			wp_reset_postdata();
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$cat   = isset( $instance['cat'] ) ? absint( $instance['cat'] ) : 0;
		$count = isset( $instance['count'] ) ? absint( $instance['count'] ) : 4;
		?>
		<p>
			<label><?php esc_html_e( 'Title', 'gazettenews' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Category', 'gazettenews' ); ?></label>
			<?php
			wp_dropdown_categories( array(
				'name'            => $this->get_field_name( 'cat' ),
				'selected'        => $cat,
				'show_option_all' => __( 'All categories', 'gazettenews' ),
				'class'           => 'widefat',
			) );
			?>
		</p>
		<p>
			<label><?php esc_html_e( 'Number of posts', 'gazettenews' ); ?></label>
			<input type="number" class="tiny-text" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" value="<?php echo esc_attr( $count ); ?>" min="1" max="12">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( $new['title'] ),
			'cat'   => absint( $new['cat'] ),
			'count' => absint( $new['count'] ),
		);
	}
}

class GazetteNews_Ad_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'gazettenews_ad',
			__( 'Gazette: Advertisement', 'gazettenews' ),
			array( 'description' => __( 'Simple image or HTML ad slot.', 'gazettenews' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$code  = ! empty( $instance['code'] ) ? $instance['code'] : '';
		$image = ! empty( $instance['image'] ) ? $instance['image'] : '';
		$url   = ! empty( $instance['url'] ) ? $instance['url'] : '';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<div class="gn-ad">';
		if ( $code ) {
			echo wp_kses_post( $code );
		} elseif ( $image ) {
			$html = '<img src="' . esc_url( $image ) . '" alt="">';
			if ( $url ) {
				$html = '<a href="' . esc_url( $url ) . '" rel="nofollow sponsored">' . $html . '</a>';
			}
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( '- Advertisement -', 'gazettenews' );
		$image = isset( $instance['image'] ) ? $instance['image'] : '';
		$url   = isset( $instance['url'] ) ? $instance['url'] : '';
		$code  = isset( $instance['code'] ) ? $instance['code'] : '';
		?>
		<p>
			<label><?php esc_html_e( 'Title', 'gazettenews' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Image URL', 'gazettenews' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'image' ) ); ?>" value="<?php echo esc_attr( $image ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Link URL', 'gazettenews' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'url' ) ); ?>" value="<?php echo esc_attr( $url ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Or HTML / shortcode', 'gazettenews' ); ?></label>
			<textarea class="widefat" rows="4" name="<?php echo esc_attr( $this->get_field_name( 'code' ) ); ?>"><?php echo esc_textarea( $code ); ?></textarea>
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title' => sanitize_text_field( $new['title'] ),
			'image' => esc_url_raw( $new['image'] ),
			'url'   => esc_url_raw( $new['url'] ),
			'code'  => wp_kses_post( $new['code'] ),
		);
	}
}

class GazetteNews_Newsletter extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'gazettenews_nl',
			__( 'Gazette: Newsletter', 'gazettenews' ),
			array( 'description' => __( 'Email signup box (mailto or custom form action).', 'gazettenews' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Newsletter', 'gazettenews' );
		$text  = ! empty( $instance['text'] ) ? $instance['text'] : __( 'Get the latest news delivered to your inbox.', 'gazettenews' );
		$action = ! empty( $instance['action'] ) ? $instance['action'] : '';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p class="nl-text">' . esc_html( $text ) . '</p>';
		echo '<form class="gn-nl" method="post" action="' . esc_url( $action ? $action : '#' ) . '">';
		echo '<input type="email" name="email" required placeholder="' . esc_attr__( 'Email address', 'gazettenews' ) . '">';
		echo '<button type="submit">' . esc_html__( 'Subscribe', 'gazettenews' ) . '</button>';
		echo '</form>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : __( 'Newsletter', 'gazettenews' );
		$text   = isset( $instance['text'] ) ? $instance['text'] : '';
		$action = isset( $instance['action'] ) ? $instance['action'] : '';
		?>
		<p>
			<label><?php esc_html_e( 'Title', 'gazettenews' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label><?php esc_html_e( 'Text', 'gazettenews' ); ?></label>
			<textarea class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
		</p>
		<p>
			<label><?php esc_html_e( 'Form action URL', 'gazettenews' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'action' ) ); ?>" value="<?php echo esc_attr( $action ); ?>">
		</p>
		<?php
	}

	public function update( $new, $old ) {
		return array(
			'title'  => sanitize_text_field( $new['title'] ),
			'text'   => sanitize_text_field( $new['text'] ),
			'action' => esc_url_raw( $new['action'] ),
		);
	}
}

function gazettenews_register_widgets() {
	register_widget( 'GazetteNews_Popular_Posts' );
	register_widget( 'GazetteNews_Category_Posts' );
	register_widget( 'GazetteNews_Ad_Widget' );
	register_widget( 'GazetteNews_Newsletter' );
}
add_action( 'widgets_init', 'gazettenews_register_widgets' );
