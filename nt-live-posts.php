<?php
/**
 * Plugin Name: NT Live Posts
 */

if ( ! class_exists( 'NT_Live_Posts' ) ) {
	class NT_Live_Posts {
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enq_jquery' ) );
			add_action( 'wp_ajax_load_posts', array( $this, 'sid_ajax_query_posts' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'sid_create_dashboard_widget' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'nt_style_enqueue' ) );
		}

		public function enq_jquery() {
			wp_enqueue_script( 'jquery' );
		}

		public function sid_ajax_query_posts() {
			$query = new WP_Query( array(
				'post_type' => 'post',
			));

			printf( '<table><thead><tr><th>Post</th><th>Author</th><th>Date</th><th>Status</th></tr></thead><tbody>' );
			if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
					$post_status = get_post_status();
					if ( 'publish' === $post_status ) {
						$nt_status_class = 'nt-status-publish';
					} elseif ( 'draft' === $post_status ) {
						$nt_status_class = 'nt-status-draft';
					} elseif ( 'pending' === $post_status ) {
						$nt_status_class = 'nt-status-pending-review';
						$post_status .= ' ' . 'Review';
					}
					printf( '<tr class="' . esc_attr( $nt_status_class ) . '"><td><a href="%1$s">%2$s</a></td><td>%3$s</td><td>%4$s</td><td class="nt-status-pill ' . esc_attr( $nt_status_class ) . '"><span>%5$s</span></td></tr>', esc_url( get_permalink() ), get_the_title(), esc_html( get_the_author_meta( 'nicename' ) ), esc_html( get_the_date( 'j-n-Y, h:i a' ) ), esc_html( $post_status ) );
			endwhile; endif;
			printf( '</tbody></table>' );
			wp_die();
		}

		public function sid_create_dashboard_widget() {
			wp_add_dashboard_widget(
				'nt-live-posts',
				'NT Live Posts',
				array( $this, 'ajax_fetch_posts' )
			);
		}

		public function ajax_fetch_posts() {
			echo '<div class="fetched-posts"></div>';
			?>
			<script>
				( function( $ ) {
					setInterval( button_load_posts, 1000 );
					nt_live_posts_full_width();
					function button_load_posts() {
						var data = {
							'action': 'load_posts'
						}

						$.post( ajaxurl, data, function( response ) {
							var fetched = $( '.fetched-posts' );
							fetched.html( response );
						});
					}

					function nt_live_posts_full_width() {
						var container = $( '#nt-live-posts' );
						parent = container.parent().parent();
						parent.css( 'width', '100%' );
					}
				})( jQuery );
			</script>
			<?php
		}

		public function nt_style_enqueue() {
			wp_enqueue_style( 'nt-style', plugins_url( '/css/nt-style.css', __FILE__ ) );
		}
	}
}

if ( class_exists( 'NT_Live_Posts' ) ) {
	new NT_Live_Posts();
}
