<?php
/**
 * OPML template.
 *
 * @package Yarns_Opml
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<opml version="1.0">
	<body>
		<?php foreach ( $channels as $channel ) : ?>
			<outline text="<?php echo esc_attr( $channel['name'] ); ?>" title="<?php echo esc_attr( $channel['name'] ); ?>">
				<?php
				if ( ! empty( $channel['items'] ) && is_array( $channel['items'] ) ) :
					foreach ( $channel['items'] as $item ) :
						?>
						<outline text="<?php echo esc_url( $item['url'] ); ?>" title="<?php echo esc_url( $item['url'] ); ?>" xmlUrl="<?php echo esc_url( $item['url'] ); ?>"></outline>
						<?php
					endforeach;
				endif;
				?>
			</outline>
		<?php endforeach; ?>
	</body>
</opml>
