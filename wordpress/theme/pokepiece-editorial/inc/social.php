<?php
/**
 * TikTok content swipe. The owner pastes public TikTok video URLs in Settings > PokePiece. For each URL the theme asks TikTok's official public oEmbed endpoint
 * for the title + thumbnail, stores the thumbnail locally (TikTok CDN links are signed and expire) and caches the result for 12 hours.
 * Nothing is embedded or loaded from TikTok in the visitor's browser: cards link out. If no URL resolves, the section shows the profile card only.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_options_page( 'PokePiece', 'PokePiece', 'manage_options', 'pokepiece', function () {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		if ( isset( $_POST['pp_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pp_settings_nonce'] ) ), 'pp_settings' ) ) {
			update_option( 'pokepiece_tiktok_videos', sanitize_textarea_field( wp_unslash( $_POST['tiktok_videos'] ?? '' ) ) );
			foreach ( array( 'tiktok_url', 'discord_url' ) as $k ) { update_option( 'pokepiece_' . $k, esc_url_raw( wp_unslash( $_POST[ $k ] ?? '' ) ) ); }
			foreach ( array( 'pokemon', 'one-piece', 'dragon-ball' ) as $gk ) { update_option( 'pokepiece_world_img_' . $gk, esc_url_raw( wp_unslash( $_POST[ 'world_' . $gk ] ?? '' ) ) ); }
			update_option( 'pokepiece_contact_email', sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) ) );
			update_option( 'pokepiece_dispatch_text', sanitize_text_field( wp_unslash( $_POST['dispatch_text'] ?? '' ) ) );
			delete_transient( 'pp_tiktok_items' );
			echo '<div class="notice notice-success"><p>Saved.</p></div>';
		}
		$v = fn( $k, $d = '' ) => esc_attr( get_option( 'pokepiece_' . $k, $d ) );
		echo '<div class="wrap"><h1>PokePiece storefront</h1><form method="post">'; wp_nonce_field( 'pp_settings', 'pp_settings_nonce' );
		echo '<table class="form-table"><tr><th>TikTok video links</th><td><textarea name="tiktok_videos" rows="8" class="large-text code" placeholder="https://www.tiktok.com/@pokepiecetcgg/video/…">' . esc_textarea( get_option( 'pokepiece_tiktok_videos', '' ) ) . '</textarea><p class="description">One per line (max 12). Either a public video URL (cover and title are fetched from TikTok oEmbed), or `video URL | cover image URL | caption` to use your own snapshot from the Media Library. Shown as a swipe on the home page; cards link to TikTok.</p></td></tr>';
		foreach ( array( 'pokemon' => 'Pokémon', 'one-piece' => 'One Piece', 'dragon-ball' => 'Dragon Ball' ) as $gk => $gn ) { echo '<tr><th>' . esc_html( $gn ) . ' panel image URL</th><td><input class="large-text" name="world_' . esc_attr( $gk ) . '" value="' . $v( 'world_img_' . $gk ) . '" placeholder="Optional, a licensed image. Empty = crop of a product photo"></td></tr>'; }
		echo '<tr><th>TikTok profile</th><td><input class="regular-text" name="tiktok_url" value="' . $v( 'tiktok_url', 'https://www.tiktok.com/@pokepiecetcgg' ) . '"></td></tr>';
		echo '<tr><th>Discord invite</th><td><input class="regular-text" name="discord_url" value="' . $v( 'discord_url', 'https://discord.com/invite/54bNErEDb' ) . '"></td></tr>';
		echo '<tr><th>Contact email</th><td><input class="regular-text" name="contact_email" value="' . $v( 'contact_email', 'info@pokepiecetcg.co.uk' ) . '"></td></tr>';
		echo '<tr><th>Dispatch wording</th><td><input class="large-text" name="dispatch_text" value="' . $v( 'dispatch_text', 'Orders are usually dispatched within 1 to 3 working days unless stated otherwise.' ) . '"></td></tr></table>';
		submit_button(); echo '</form></div>';
	} );
} );

/** Bundled snapshots of the owner's public TikTok posts (fetched 6 Nov 2026 through a real browser; covers + one-line summaries of his own captions, customer @names removed). Replaced by Settings > PokePiece when the owner sets links. */
function pp_tiktok_bundled() {
	$f = PP_DIR . '/assets/social/clips.json';
	if ( ! is_readable( $f ) ) { return array(); }
	$out = array();
	foreach ( (array) json_decode( file_get_contents( $f ), true ) as $c ) { $out[] = array( 'url' => $c['url'], 'title' => $c['caption'], 'thumb' => PP_URI . '/assets/social/' . $c['img'] ); }
	return $out;
}

function pp_tiktok_items() {
	if ( '' === trim( (string) get_option( 'pokepiece_tiktok_videos', '' ) ) ) { return pp_tiktok_bundled(); }
	$cached = get_transient( 'pp_tiktok_items' );
	if ( is_array( $cached ) ) { return $cached; }
	$urls = array_slice( array_filter( array_map( 'trim', preg_split( '/\R/', (string) get_option( 'pokepiece_tiktok_videos', '' ) ) ) ), 0, 12 );
	$items = array();
	if ( $urls ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$up = wp_upload_dir(); $dir = trailingslashit( $up['basedir'] ) . 'pokepiece-tiktok'; wp_mkdir_p( $dir );
		foreach ( $urls as $line ) {
			// Manual mode: `video URL | image URL (media library) | caption`, no request to TikTok needed.
			if ( str_contains( $line, '|' ) ) { $p = array_map( 'trim', explode( '|', $line ) ); if ( preg_match( '#^https://(www\.)?tiktok\.com/#', $p[0] ) && ! empty( $p[1] ) ) { $items[] = array( 'url' => $p[0], 'title' => sanitize_text_field( $p[2] ?? '' ), 'thumb' => esc_url_raw( $p[1] ) ); } continue; }
			$u = $line;
			if ( ! preg_match( '#^https://(www\.)?tiktok\.com/@[\w.\-]+/video/\d+#', $u ) ) { continue; }
			$r = wp_remote_get( 'https://www.tiktok.com/oembed?url=' . rawurlencode( $u ), array( 'timeout' => 8 ) );
			if ( is_wp_error( $r ) || 200 !== wp_remote_retrieve_response_code( $r ) ) { continue; }
			$d = json_decode( wp_remote_retrieve_body( $r ), true );
			if ( empty( $d['thumbnail_url'] ) ) { continue; }
			$id = md5( $u ); $file = $dir . '/' . $id . '.jpg';
			if ( ! file_exists( $file ) ) { $tmp = download_url( $d['thumbnail_url'], 10 ); if ( is_wp_error( $tmp ) ) { continue; } copy( $tmp, $file ); @unlink( $tmp ); }
			$items[] = array( 'url' => $u, 'title' => wp_strip_all_tags( (string) ( $d['title'] ?? '' ) ), 'thumb' => trailingslashit( $up['baseurl'] ) . 'pokepiece-tiktok/' . $id . '.jpg' );
		}
	}
	set_transient( 'pp_tiktok_items', $items, $items ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
	return $items ?: pp_tiktok_bundled();
}
