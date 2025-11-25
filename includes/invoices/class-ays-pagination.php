<?php
/**
 * AYS Pagination Helper Class
 *
 * Provides reusable pagination functionality for all tabular data in the invoicing system.
 * Works with database queries to handle offset, limit, and page navigation.
 *
 * Usage:
 *   $paginator = new AYS_Pagination( $total_items, 20 );
 *   $paginator->render_pagination_links();
 *   $results = $wpdb->get_results( $paginator->get_query_sql( $base_sql ) );
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Pagination {

	/**
	 * Total number of items
	 *
	 * @var int
	 */
	private $total_items;

	/**
	 * Items per page
	 *
	 * @var int
	 */
	private $per_page;

	/**
	 * Current page number (1-indexed)
	 *
	 * @var int
	 */
	private $current_page;

	/**
	 * Total number of pages
	 *
	 * @var int
	 */
	private $total_pages;

	/**
	 * Query parameter name for page (default: 'paged')
	 *
	 * @var string
	 */
	private $page_param;

	/**
	 * Constructor
	 *
	 * @param int    $total_items Total number of items to paginate.
	 * @param int    $per_page    Items to display per page (default: 20).
	 * @param string $page_param  Query parameter name for page (default: 'paged').
	 */
	public function __construct( $total_items = 0, $per_page = 20, $page_param = 'paged' ) {
		$this->total_items = max( 0, intval( $total_items ) );
		$this->per_page    = max( 1, intval( $per_page ) );
		$this->page_param  = sanitize_key( $page_param );

		// Get current page from query string or default to 1
		$this->current_page = isset( $_GET[ $this->page_param ] ) ? max( 1, intval( $_GET[ $this->page_param ] ) ) : 1;

		// Calculate total pages
		$this->total_pages = max( 1, ceil( $this->total_items / $this->per_page ) );

		// Clamp current page to valid range
		if ( $this->current_page > $this->total_pages ) {
			$this->current_page = $this->total_pages;
		}
	}

	/**
	 * Get the current page number
	 *
	 * @return int
	 */
	public function get_current_page() {
		return $this->current_page;
	}

	/**
	 * Get total pages
	 *
	 * @return int
	 */
	public function get_total_pages() {
		return $this->total_pages;
	}

	/**
	 * Get items per page
	 *
	 * @return int
	 */
	public function get_per_page() {
		return $this->per_page;
	}

	/**
	 * Get total items
	 *
	 * @return int
	 */
	public function get_total_items() {
		return $this->total_items;
	}

	/**
	 * Get the OFFSET value for SQL LIMIT clause
	 *
	 * @return int
	 */
	public function get_offset() {
		return ( $this->current_page - 1 ) * $this->per_page;
	}

	/**
	 * Get the LIMIT clause for SQL query
	 *
	 * @return string SQL LIMIT clause (e.g., "LIMIT 20 OFFSET 0")
	 */
	public function get_limit_clause() {
		return sprintf( 'LIMIT %d OFFSET %d', $this->per_page, $this->get_offset() );
	}

	/**
	 * Apply pagination to a base SQL query
	 *
	 * @param string $base_sql Base SQL query (should NOT include LIMIT clause).
	 * @return string Modified SQL query with LIMIT and OFFSET.
	 */
	public function get_query_sql( $base_sql ) {
		// Remove any existing LIMIT clause
		$base_sql = preg_replace( '/\sLIMIT\s.*$/i', '', $base_sql );

		return $base_sql . ' ' . $this->get_limit_clause();
	}

	/**
	 * Render pagination links (Previous | 1 2 3 | Next)
	 *
	 * @param array $args Optional arguments to customize output.
	 * @return void
	 */
	public function render_pagination_links( $args = [] ) {
		// Don't render if only one page
		if ( $this->total_pages <= 1 ) {
			return;
		}

		// Default arguments
		$defaults = [
			'show_info'     => true,
			'prev_text'     => esc_html__( '← Previous', 'atyourservice' ),
			'next_text'     => esc_html__( 'Next →', 'atyourservice' ),
			'mid_size'      => 2,        // Number of page links to show either side of current page
			'end_size'      => 1,        // Number of page links to show at start and end
			'before'        => '<nav class="ays-pagination" style="margin: 20px 0; text-align: center;">',
			'after'         => '</nav>',
			'base_url'      => add_query_arg( $this->page_param, '%#%' ),
		];
		$args = wp_parse_args( $args, $defaults );

		echo wp_kses_post( $args['before'] );

		// Show info if enabled
		if ( $args['show_info'] ) {
			$start = ( $this->current_page - 1 ) * $this->per_page + 1;
			$end   = min( $this->current_page * $this->per_page, $this->total_items );
			echo '<p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">';
			echo sprintf(
				esc_html__( 'Showing %d–%d of %d items', 'atyourservice' ),
				intval( $start ),
				intval( $end ),
				intval( $this->total_items )
			);
			echo '</p>';
		}

		// Build pagination links
		$links = paginate_links( [
			'base'      => $args['base_url'],
			'format'    => '',
			'total'     => $this->total_pages,
			'current'   => $this->current_page,
			'prev_text' => $args['prev_text'],
			'next_text' => $args['next_text'],
			'mid_size'  => $args['mid_size'],
			'end_size'  => $args['end_size'],
			'type'      => 'array',
		] );

		if ( $links ) {
			echo '<div style="display: flex; justify-content: center; gap: 5px; flex-wrap: wrap;">';
			foreach ( $links as $link ) {
				// Add WordPress pagination classes for styling
				echo wp_kses_post( $link );
			}
			echo '</div>';
		}

		echo wp_kses_post( $args['after'] );
	}

	/**
	 * Render a simple pagination controls with page info
	 * Alternative to render_pagination_links for minimal UI
	 *
	 * @param array $args Optional arguments.
	 * @return void
	 */
	public function render_simple_pagination( $args = [] ) {
		if ( $this->total_pages <= 1 ) {
			return;
		}

		$defaults = [
			'prev_text' => esc_html__( '← Previous', 'atyourservice' ),
			'next_text' => esc_html__( 'Next →', 'atyourservice' ),
		];
		$args = wp_parse_args( $args, $defaults );

		$start = ( $this->current_page - 1 ) * $this->per_page + 1;
		$end   = min( $this->current_page * $this->per_page, $this->total_items );

		?>
		<nav class="ays-pagination-simple" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
			<div style="font-size: 14px; color: #666;">
				<?php
				echo sprintf(
					esc_html__( 'Page %d of %d', 'atyourservice' ),
					intval( $this->current_page ),
					intval( $this->total_pages )
				);
				echo ' | ';
				echo sprintf(
					esc_html__( 'Showing %d–%d of %d', 'atyourservice' ),
					intval( $start ),
					intval( $end ),
					intval( $this->total_items )
				);
				?>
			</div>
			<div style="display: flex; gap: 10px;">
				<?php if ( $this->current_page > 1 ) : ?>
					<a href="<?php echo esc_url( add_query_arg( $this->page_param, $this->current_page - 1 ) ); ?>" class="button button-secondary">
						<?php echo esc_html( $args['prev_text'] ); ?>
					</a>
				<?php else : ?>
					<button disabled class="button button-secondary" style="opacity: 0.5; cursor: not-allowed;">
						<?php echo esc_html( $args['prev_text'] ); ?>
					</button>
				<?php endif; ?>

				<?php if ( $this->current_page < $this->total_pages ) : ?>
					<a href="<?php echo esc_url( add_query_arg( $this->page_param, $this->current_page + 1 ) ); ?>" class="button button-secondary">
						<?php echo esc_html( $args['next_text'] ); ?>
					</a>
				<?php else : ?>
					<button disabled class="button button-secondary" style="opacity: 0.5; cursor: not-allowed;">
						<?php echo esc_html( $args['next_text'] ); ?>
					</button>
				<?php endif; ?>
			</div>
		</nav>
		<?php
	}

	/**
	 * Get starting item number for current page
	 *
	 * @return int
	 */
	public function get_start_item() {
		return $this->get_offset() + 1;
	}

	/**
	 * Get ending item number for current page
	 *
	 * @return int
	 */
	public function get_end_item() {
		return min( $this->current_page * $this->per_page, $this->total_items );
	}

	/**
	 * Check if there's a previous page
	 *
	 * @return bool
	 */
	public function has_previous_page() {
		return $this->current_page > 1;
	}

	/**
	 * Check if there's a next page
	 *
	 * @return bool
	 */
	public function has_next_page() {
		return $this->current_page < $this->total_pages;
	}

	/**
	 * Get previous page URL
	 *
	 * @return string
	 */
	public function get_previous_page_url() {
		if ( ! $this->has_previous_page() ) {
			return '';
		}
		return esc_url( add_query_arg( $this->page_param, $this->current_page - 1 ) );
	}

	/**
	 * Get next page URL
	 *
	 * @return string
	 */
	public function get_next_page_url() {
		if ( ! $this->has_next_page() ) {
			return '';
		}
		return esc_url( add_query_arg( $this->page_param, $this->current_page + 1 ) );
	}

	/**
	 * Enqueue smooth scroll jQuery for pagination
	 * Call this once per page to add smooth scrolling to all pagination links
	 *
	 * @return void
	 */
	public static function enqueue_smooth_scroll() {
		static $script_enqueued = false;

		// Only enqueue once per page load
		if ( $script_enqueued ) {
			return;
		}
		$script_enqueued = true;

		// Add inline script for smooth pagination scroll
		add_action( 'admin_footer', [ self::class, 'render_smooth_scroll_script' ] );
	}

	/**
	 * Render the smooth scroll jQuery script
	 * Called via admin_footer action
	 *
	 * @return void
	 */
	public static function render_smooth_scroll_script() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Smooth scroll to table when pagination links are clicked
			$('a.page-numbers, .ays-pagination a, .ays-pagination-simple a').on('click', function(e) {
				// Check if link has href (not current page)
				if (!$(this).attr('href') || $(this).hasClass('page-numbers') && $(this).attr('aria-current') === 'page') {
					return;
				}

				// Find the nearest table to scroll to
				var $table = $(this).closest('.ays-pagination, .ays-pagination-simple').prevAll('table').first();
				
				if ($table.length) {
					// Smooth scroll to the table with offset for admin bar
					$('html, body').animate({
						scrollTop: $table.offset().top - 100
					}, 600);
				}
			});
		});
		</script>
		<?php
	}
}

