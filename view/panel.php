<?php date_default_timezone_set( 'Europe/Berlin' ); ?>
<div class="at-ajax-loader">
	<div class="inner">
		<p></p>

		<div class="progress">
			<div class="progress-bar" style="width:0%;" data-item="0">0%</div>
		</div>
	</div>
</div>

<div class="wrap" id="at-import-page" data-url="<?php echo admin_url(); ?>" data-nonce="<?php echo wp_create_nonce("at_amazon_import_wpnonce"); ?>">
	<div class="at-inner">
		<h1>AffiliateTheme Import » Amazon</h1>
		
		<div id="checkConnection"></div>

		<?php
			if(version_compare(PHP_VERSION, '5.3.0', '<')):
		?>
			<div class="error" id="required-by-plugin">
				<p>Achtung: Um dieses Plugin zu verwenden benötigst du mindestens PHP 5.3.x. Derzeit verwendet <?php echo PHP_VERSION; ?>.</p>
			</div>
		<?php
			endif;
		?>

        <?php
            if(extension_loaded('curl') != function_exists('curl_version')):
        ?>
            <div class="error" id="required-by-plugin">
                <p>Um dieses Plugin zu verwenden benötigst du cURL. <a href="http://php.net/manual/de/book.curl.php" taget="_blank">Hier</a> findest du mehr Informationen darüber. Kontaktiere im Zweifel deinen Systemadministrator.</p>
            </div>
        <?php
            endif;
        ?>
        
        <?php
            if(ini_get('allow_url_fopen') == false):
        ?>
            <div class="error" id="required-by-plugin">
                <p>Achtung: Du hast allow_url_fopen deaktiviert. Du benötigst diese Funktionen um das Rating von Amazon zu beziehen.</p>
            </div>
        <?php
            endif;
        ?>
		
		<h2 class="nav-tab-wrapper" id="at-api-tabs">
			<a class="nav-tab nav-tab-active" id="settings-tab" href="#top#settings"><?php _e('Einstellungen', 'affiliatetheme-api'); ?></a>
			<a class="nav-tab" id="search-tab" href="#top#search"><?php _e('Suche', 'affiliatetheme-api'); ?></a>
			<a class="nav-tab" id="apilog-tab" href="#top#apilog"><?php _e('API Log', 'affiliatetheme-api'); ?></a>
			<a class="nav-tab" id="buttons-tab" href="#top#buttons"><?php _e('Buttons', 'affiliatetheme-api'); ?></a>
		</h2>
		
		<div class="tabwrapper">
			<!-- START: Settings Tab-->
			<div id="settings" class="at-api-tab active">
				<div id="at-import-settings" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('Einstellungen', 'affiliatetheme-api'); ?></span></h3>
					<div class="inside">
						<form action="options.php" method="post" id="<?php echo $plugin_options; ?>_form" name="<?php echo $plugin_options; ?>_form">
							<?php settings_fields($plugin_options); ?>
							<?php do_settings_sections( $plugin_options ); ?>
							<div class="form-container">
								<div class="form-group">
									<label for="amazon_public_key"><?php _e('Access Key ID', 'affiliatetheme-api'); ?></label>
									<input type="text" name="amazon_public_key" value="<?php echo get_option('amazon_public_key'); ?>" />
								</div>
								<div class="form-group">	
									<label for="amazon_secret_key"><?php _e('Secret Access Key', 'affiliatetheme-api'); ?></label>
									<input type="text" name="amazon_secret_key" value="<?php echo get_option('amazon_secret_key'); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_country"><?php _e('Land', 'affiliatetheme-api'); ?></label>
									<?php $selected_amazon_country = get_option('amazon_country'); ?>
									<select name="amazon_country" id="amazon_country">
										<option value="de" <?php if($selected_amazon_country == "de") echo 'selected'; ?>>Deutschland</option>
										<option value="com" <?php if($selected_amazon_country == "com") echo 'selected'; ?>>US</option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_partner_id"><?php _e('Partner ID', 'affiliatetheme-api'); ?></label>
									<input type="text" name="amazon_partner_id" value="<?php echo get_option('amazon_partner_id'); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_notification"><?php _e('Benachrichtigung', 'affiliatetheme-api'); ?></label>
									<?php $selected_amazon_notification = get_option('amazon_notification'); ?>
									<select name="amazon_notification" id="amazon_notification">
										<option value=""><?php _e('Nichts', 'affiliatetheme-api'); ?></option>
										<option value="email" <?php if($selected_amazon_notification == "email") echo 'selected'; ?>><?php _e('E-Mail Benachrichtigung', 'affiliatetheme-api'); ?></option>
										<option value="draft" <?php if($selected_amazon_notification == "draft") echo 'selected'; ?>><?php _e('Produkt als Entwurf setzen', 'affiliatetheme-api'); ?></option>
										<option value="email_draft" <?php if($selected_amazon_notification == "email_draft") echo 'selected'; ?>><?php _e('E-Mail Benachrichtigung & Produkt als Entwurf setzen', 'affiliatetheme-api'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Was soll passieren wenn ein Produkt nicht mehr verfügbar ist?', 'affiliatetheme-api'); ?></p>
								</div>
                                <div class="form-group">
                                    <label for="amazon_post_status"><?php _e('Produktstatus', 'affiliatetheme-api'); ?></label>
                                    <?php $selected_amazon_post_status = get_option('amazon_post_status'); ?>
                                    <select name="amazon_post_status" id="amazon_post_status">
                                        <option value="publish"><?php _e('Veröffentlicht', 'affiliatetheme-api'); ?></option>
                                        <option value="draft" <?php if($selected_amazon_post_status == "draft") echo 'selected'; ?>><?php _e('Entwurf', 'affiliatetheme-api'); ?></option>
                                    </select>
                                    <p class="form-hint"><?php _e('Du kannst Produkte sofort veröffentlichen oder als Entwurf anlegen.', 'affiliatetheme-api'); ?></p>
                                </div>
                                <div class="form-group">
                                    <label for="amazon_import_description"><?php _e('Beschreibung', 'affiliatetheme-api'); ?></label>
                                    <input type="checkbox" name="amazon_import_description" id="amazon_import_description" value="1" <?php if('1' == get_option('amazon_import_description')) echo 'checked'; ?>> <?php _e('Produktbeschreibung importieren', 'affiliatetheme-api'); ?>
                                </div>
								<div class="form-group">
									<?php submit_button(); ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- END: Settings Tab-->	
			
			<!-- START: Search Tab-->
			<div id="search" class="at-api-tab">
				<div id="at-import-window" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('Amazon durchsuchen', 'affiliatetheme-api'); ?></span></h3>
					<div class="inside">
						<div class="form-container">
							<div class="form-group">
								<label for="search"><?php _e('Suche', 'affiliatetheme-api'); ?></label>
								<input type="text" name="search" id="search">
							</div>
							<div class="form-group">
								<label><?php _e('Kategorie', 'affiliatetheme-api'); ?></label>
								<select name="category" id="category">
									<option value="All" selected>Alle Kategorien</option>
									<option value="Apparel">Apparel</option>
									<option value="Automotive">Automotive</option>
									<option value="Baby">Baby</option>
									<option value="Blended">Blended</option>
									<option value="Beauty">Beauty</option>
									<option value="Books">Bücher</option>
									<option value="Classical">Classical</option>
									<option value="DVD">DVD</option>
									<option value="Electronics">Elektronik</option>
									<option value="ForeignBooks">Foreign Books</option>
									<option value="Grocery">Grocery</option>
									<option value="HealthPersonalCare">Health Personal Care</option>
									<option value="HomeGarden">HomeGarden</option>
									<option value="Jewelry">Juwelen</option>
									<option value="KindleStore">Kindle Store</option>
									<option value="Kitchen">Küche</option>
									<option value="Lighting">Beleuchtung</option>
									<option value="Luggage">Luggage</option>
									<option value="Magazines">Magazine</option>
									<option value="Marketplace">Marketplace</option>
									<option value="MP3Downloads">MP3 Downloads</option>
									<option value="MobileApps">Mobileapps</option>
									<option value="Music">Musik</option>
									<option value="MusicalInstruments">Musikinstrumente</option>
									<option value="MusicTracks">Lieder</option>
									<option value="OfficeProducts">Büro Produkte</option>
									<option value="OutdoorLiving">Outdoor living</option>
									<option value="Outlet">Outlet</option>
									<option value="PCHardware">PC Hardware</option>
									<option value="Photo">Foto</option>
									<option value="Software">Software</option>
									<option value="SoftwareVideoGames">Software Videospiele</option>
									<option value="SportingGoods">Sporting goods</option>
									<option value="Tools">Werkzeuge</option>
									<option value="Toys">Spielzeuge</option>
									<option value="VHS">VHS</option>
									<option value="Video">Videos</option>
									<option value="VideoGames">Videospiele</option>
									<option value="Watches">Uhren</option>
								</select>
							</div>
                            <hr>
                            <div class="form-container" style="">
                                <form class="form-inline" method="post" action="">
                                    <div class="form-group">
                                        <label for="grabburl"><?php _e('ASIN Grabber', 'affiliatetheme-api'); ?></label>
                                        <input type="text" class="form-control" id="grabburl" name="grabburl" placeholder="URL eintragen...">
                                        <button id="grab-link" class="button button-primary"><?php _e('Grab ASINs', 'affiliatetheme-api'); ?></button>
                                    </div>

                                </form>
                                <div class="clearfix"></div>
                            </div>
                            <div class="form-container">
                                <div class="form-group">
                                    <label for="grabbedasins" class="control-label"><?php _e('Suche nach ASINs', 'affiliatetheme-api'); ?></label>
                                    <textarea name="grabbedasins" id="grabbedasins" cols="30" rows="10" placeholder="ASINs eintragen..."></textarea>
                                </div>
                                <div class="clearfix"></div>
                            </div>
							<div class="form-group submit-group">
								<input type="hidden" name="page" id="page" value="1">
								<input type="hidden" name="max-pages" id="max-pages" value="">
								<button id="search-link" class="button button-primary"><?php _e('Suche', 'affiliatetheme-api'); ?></button>
							</div>
						</div>
					
				
						<div id="info-title"></div>
						
						<div class="page-links" style="margin-bottom:15px;">
							<button class="prev-page button">« <?php _e('Vorherige Seite', 'affiliatetheme-api'); ?></button>
							<button class="next-page button"><?php _e('Nächste Seite', 'affiliatetheme-api'); ?> »</button>
						</div>
										
						<table class="wp-list-table widefat fixed products">
							<thead>
								<tr>
									<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
										<label class="screen-reader-text" for="cb-select-all-1">Alle auswählen</label><input id="cb-select-all-1" type="checkbox">
									</th>
									<th scope="col" id="asin" class="manage-column column-asin">
										<span><?php _e('ASIN', 'affiliatetheme-api'); ?></span>
									</th>
									<th scope="col" id="image" class="manage-column column-image">
										<span><?php _e('Vorschau', 'affiliatetheme-api'); ?></span>
									</th>
									<th scope="col" id="title" class="manage-column column-title">
										<span><?php _e('Titel', 'affiliatetheme-api'); ?></span>
									</th>
									<th scope="col" id="rating" class="manage-column column-rating">
										<span><?php _e('Bewertung', 'affiliatetheme-api'); ?></span>
									</th>
									<th scope="col" id="price" class="manage-column column-price">
										<span><?php _e('Preis', 'affiliatetheme-api'); ?></span>
									</th>
									<th scope="col" id="margin" class="manage-column column-margin">
										<span><?php _e('Provision', 'affiliatetheme-api'); ?></span>
									</th>
									<th scope="col" id="category" class="manage-column column-category">
										<span><?php _e('Kategorie', 'affiliatetheme-api'); ?></span>
									</th>
									<th scope="col" id="actions" class="manage-column column-action">
										<span></span>
									</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="8"><a href="#" class="mass-import button button-primary"><?php _e('Ausgewählte Produkte importieren', 'affiliatetheme-api'); ?></a></td>
								</tr>
							</tfoot>
							<tbody id="results"></tbody>
						</table>

						<div class="page-links" style="margin-top:15px;">
							<button class="prev-page button">« <?php _e('Vorherige Seite', 'affiliatetheme-api'); ?></button>
							<button class="next-page button"><?php _e('Nächste Seite', 'affiliatetheme-api'); ?> »</button>
						</div>
						
						<?php add_thickbox(); ?>
						<div id="my-content-id" style="display:none;">
							 <p>
								  Yeah, endcore rocks!
							 </p>
						</div>
					</div>
				</div>
			</div>
			<!-- END: Search Tab -->
			
			<!-- START: API Log Tab-->
			<div id="apilog" class="at-api-tab active">
				<div id="at-import-settings" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('API Log', 'affiliatetheme-api'); ?></span></h3>
					<div class="inside">
                        <p><?php _e('Hier werden dir die letzten 200 Einträge der API log angezeigt.', 'affiliatetheme-api'); ?></p>
                        <p><a href="" class="clear-api-log button" data-type="amazon" data-hash="<?php echo AWS_CRON_HASH; ?>"><?php _e('Log löschen', 'affiliatetheme-api'); ?></a></p>
						<table class="apilog">
							<thead>
								<tr>
									<th><?php _e('Datum', 'affiliatetheme-api') ?></th>
									<th><?php _e('Typ', 'affiliatetheme-api') ?></th>
									<th><?php _e('Nachricht', 'affiliatetheme-api') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$log = get_option('at_amazon_api_log');
								if($log) {
									$log = array_reverse($log);

									foreach($log as $item) {
										?>
										<tr>
											<td><?php echo date('d.m.Y H:i:s', $item['time']); ?></td>
											<td>
												<?php 
												if('system' != ($item['post_id'])) {
													?><a href="<?php echo admin_url('post.php?post='.$item["post_id"].'&action=edit'); ?>" target="_blank"><?php echo get_the_title($item['post_id']); ?></a><?php
												} else {
													echo $item['post_id'];
												}
												?>
											</td>
											<td><?php echo $item['msg']; ?></td>
										</tr>
										<?php 
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!-- END: API Log Tab-->

			<!-- START: Buttons Tab-->
			<div id="buttons" class="at-api-tab">
				<div id="at-import-settings" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('Buttons', 'affiliatetheme-api'); ?></span></h3>
					<div class="inside">
						<form action="options.php" method="post" id="<?php echo $plugin_button_options; ?>_form" name="<?php echo $plugin_button_options; ?>_form">
							<?php settings_fields($plugin_button_options); ?>
							<?php do_settings_sections( $plugin_button_options ); ?>
							<p class="hint">Wenn du für Amazon Produkte spezielle Button-Texte ausgeben möchtest, kannst du diese hier angeben.</p>
							<div class="form-container">
								<div class="form-group">
									<label for="amazon_buy_short_button"><?php _e('Kaufen Button (kurz)', 'affiliatetheme-api'); ?></label>
									<input type="text" name="amazon_buy_short_button" value="<?php echo (get_option('amazon_buy_short_button') ? get_option('amazon_buy_short_button') : 'Kaufen'); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_buy_button"><?php _e('Kaufen Button', 'affiliatetheme-api'); ?></label>
									<input type="text" name="amazon_buy_button" value="<?php echo (get_option('amazon_buy_button') ? get_option('amazon_buy_button') : 'Jetzt bei Amazon kaufen'); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_not_avail_button"><?php _e('Nicht Verfügbar', 'affiliatetheme-api'); ?></label>
									<input type="text" name="amazon_not_avail_button" value="<?php echo (get_option('amazon_not_avail_button') ? get_option('amazon_not_avail_button') : 'Nicht Verfügbar'); ?>" />
								</div>
								<div class="form-group">
									<?php submit_button(); ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- END: Buttons Tab-->
		</div>
	</div>

    <div class="afs_ads">&nbsp;</div>
</div>
