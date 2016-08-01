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
			<a class="nav-tab nav-tab-active" id="settings-tab" href="#top#settings"><?php _e('Einstellungen', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" id="search-tab" href="#top#search"><?php _e('Suche', 'affiliatetheme-amazon'); ?></a>
			<!--<a class="nav-tab" id="feed-tab" href="#top#feed"><?php _e('Feed', 'affiliatetheme-amazon'); ?></a>-->
			<a class="nav-tab" id="apilog-tab" href="#top#apilog"><?php _e('API Log', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" id="buttons-tab" href="#top#buttons"><?php _e('Buttons', 'affiliatetheme-amazon'); ?></a>
		</h2>
		
		<div class="tabwrapper">
			<!-- START: Settings Tab-->
			<div id="settings" class="at-api-tab active">
				<div id="at-import-settings" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('Einstellungen', 'affiliatetheme-amazon'); ?></span></h3>
					<div class="inside">
						<form action="options.php" method="post" id="<?php echo $plugin_options; ?>_form" name="<?php echo $plugin_options; ?>_form">
							<?php settings_fields($plugin_options); ?>
							<?php do_settings_sections( $plugin_options ); ?>
							<div class="form-container">
								<div class="form-group">
									<label for="amazon_public_key"><?php _e('Access Key ID', 'affiliatetheme-amazon'); ?> <sup>*</sup></label>
									<input type="password" name="amazon_public_key" id="amazon_public_key" value="<?php echo get_option('amazon_public_key'); ?>" />
								</div>
								<div class="form-group">	
									<label for="amazon_secret_key"><?php _e('Secret Access Key', 'affiliatetheme-amazon'); ?> <sup>*</sup></label>
									<input type="password" name="amazon_secret_key" id="amazon_secret_key" value="<?php echo get_option('amazon_secret_key'); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_country"><?php _e('Land', 'affiliatetheme-amazon'); ?> <sup>*</sup></label>
									<?php $selected_amazon_country = get_option('amazon_country'); ?>
									<select name="amazon_country" id="amazon_country">
										<option value="de" <?php if($selected_amazon_country == "de") echo 'selected'; ?>>Deutschland</option>
										<option value="com" <?php if($selected_amazon_country == "com") echo 'selected'; ?>>US</option>
										<option value="ca" <?php if($selected_amazon_country == "ca") echo 'selected'; ?>>Canada</option>
										<option value="co.uk" <?php if($selected_amazon_country == "co.uk") echo 'selected'; ?>>United Kingdom</option>
										<option value="fr" <?php if($selected_amazon_country == "fr") echo 'selected'; ?>>France</option>
										<option value="it" <?php if($selected_amazon_country == "it") echo 'selected'; ?>>Italia</option>
										<option value="es" <?php if($selected_amazon_country == "es") echo 'selected'; ?>>Espania</option>
										<option value="in" <?php if($selected_amazon_country == "in") echo 'selected'; ?>>India</option>
										<option value="co.jp" <?php if($selected_amazon_country == "co.jp") echo 'selected'; ?>>Japan</option>
										<option value="com.mx" <?php if($selected_amazon_country == "com.mx") echo 'selected'; ?>>Mexiko</option>
										<option value="cn" <?php if($selected_amazon_country == "cn") echo 'selected'; ?>>China</option>
										<option value="com.br" <?php if($selected_amazon_country == "com.br") echo 'selected'; ?>>Brazil</option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_partner_id"><?php _e('Partner Tag', 'affiliatetheme-amazon'); ?> <sup>*</sup></label>
									<input type="text" name="amazon_partner_id" value="<?php echo get_option('amazon_partner_id'); ?>" />
									<p class="form-hint"><?php _e('Damit die Produkt-Links dem richtigen Partner zugeordnet werden, trage hier deinen Partner Tag ein (z.B. superaffiliate-21).<br><strong>Wichtiger Hinweis:</strong> Wenn du diese Partner ID im späteren Verlauf änderst, werden alle Links in der Datenbank nach und nach mit dem neuen Partner Tag ausgestattet.', 'affiliatetheme-amazon'); ?></p>
								</div>
								<div class="form-group">
									<label for="amazon_notification"><?php _e('Benachrichtigung', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_notification = get_option('amazon_notification'); ?>
									<select name="amazon_notification" id="amazon_notification">
										<option value=""><?php _e('Nichts', 'affiliatetheme-amazon'); ?></option>
										<option value="email" <?php if($selected_amazon_notification == "email") echo 'selected'; ?>><?php _e('E-Mail Benachrichtigung', 'affiliatetheme-amazon'); ?></option>
										<option value="draft" <?php if($selected_amazon_notification == "draft") echo 'selected'; ?>><?php _e('Produkt als Entwurf setzen', 'affiliatetheme-amazon'); ?></option>
										<option value="email_draft" <?php if($selected_amazon_notification == "email_draft") echo 'selected'; ?>><?php _e('E-Mail Benachrichtigung & Produkt als Entwurf setzen', 'affiliatetheme-amazon'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Was soll passieren wenn ein Produkt nicht mehr verfügbar ist?', 'affiliatetheme-amazon'); ?></p>
								</div>
                                <div class="form-group">
                                    <label for="amazon_post_status"><?php _e('Produktstatus', 'affiliatetheme-amazon'); ?></label>
                                    <?php $selected_amazon_post_status = get_option('amazon_post_status'); ?>
                                    <select name="amazon_post_status" id="amazon_post_status">
                                        <option value="publish"><?php _e('Veröffentlicht', 'affiliatetheme-amazon'); ?></option>
                                        <option value="draft" <?php if($selected_amazon_post_status == "draft") echo 'selected'; ?>><?php _e('Entwurf', 'affiliatetheme-amazon'); ?></option>
                                    </select>
                                    <p class="form-hint"><?php _e('Du kannst Produkte sofort veröffentlichen oder als Entwurf anlegen.', 'affiliatetheme-amazon'); ?></p>
                                </div>
								<div class="form-group">
									<label for="amazon_import_description"><?php _e('Beschreibung', 'affiliatetheme-amazon'); ?></label>
									<input type="checkbox" name="amazon_import_description" id="amazon_import_description" value="1" <?php if('1' == get_option('amazon_import_description')) echo 'checked'; ?>> <?php _e('Produktbeschreibung importieren', 'affiliatetheme-amazon'); ?>
								</div>
                                <div class="form-group">
                                    <label for="amazon_images_external"><?php _e('Externe Produktbilder', 'affiliatetheme-amazon'); ?></label>
                                    <input type="checkbox" name="amazon_images_external" id="amazon_images_external" value="1" <?php if('1' == get_option('amazon_images_external')) echo 'checked'; ?>> <?php _e('Produktbilder von extern einbinden', 'affiliatetheme-amazon'); ?>
                                	<p class="form-hint"><?php _e('Mit dieser Option werden die Produktbilder nicht auf den eigenen Server heruntergeladen sondern direkt über Amazon eingebunden.', 'affiliatetheme-amazon'); ?></p>
								</div>
								<div class="form-group toggle_amazon_images_external" <?php if(get_option('amazon_images_external') != '1') { echo 'style="display:none;"'; } ?>>
									<label for="amazon_images_external_size"><?php _e('Bildgröße der Bilder', 'affiliatetheme-api'); ?></label>
									<?php $selected_amazon_images_external_size = get_option('amazon_images_external_size'); ?>
									<select name="amazon_images_external_size" id="amazon_images_external_size">
										<option value="SmallImage" <?php if($selected_amazon_images_external_size == 'SmallImage' || $selected_amazon_update_external_images == '') echo 'selected'; ?>><?php _e('Klein', 'affiliatetheme-api'); ?></option>
										<option value="MediumImage" <?php if($selected_amazon_images_external_size == 'MediumImage') echo 'selected'; ?>><?php _e('Mittel', 'affiliatetheme-api'); ?></option>
										<option value="LargeImage" <?php if($selected_amazon_images_external_size == 'LargeImage') echo 'selected'; ?>><?php _e('Groß', 'affiliatetheme-api'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Wir empfehlen die Bildgröße "Klein" oder "Mittel" zu wählen, die großen Bilder könnten zu einer längeren Ladezeit deiner Seite führen!', 'affiliatetheme-amazon'); ?></p>
								</div>
								<div class="form-group">
									<label for="amazon_show_reviews"><?php _e('Kundenrezensionen', 'affiliatetheme-amazon'); ?></label>
									<input type="checkbox" name="amazon_show_reviews" id="amazon_show_reviews" value="1" <?php if('1' == get_option('amazon_show_reviews')) echo 'checked'; ?>> <?php _e('Kundenrezensionen auf der Produktdetailseite verlinken', 'affiliatetheme-amazon'); ?>
								</div>
								<h3><?php _e('Einstellungen für den Update-Prozess', 'affiliatetheme-api'); ?></h3>
								<div class="form-group">
									<label for="amazon_update_ean"><?php _e('EAN', 'affiliatetheme-api'); ?></label>
									<?php $selected_amazon_update_ean = get_option('amazon_update_ean'); ?>
									<select name="amazon_update_ean" id="amazon_update_ean">
										<option value="yes" <?php if($selected_amazon_update_ean == 'yes' || $selected_amazon_update_ean == '') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-api'); ?></option>
										<option value="no" <?php if($selected_amazon_update_ean == 'no') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-api'); ?></option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_update_price"><?php _e('Preise', 'affiliatetheme-api'); ?></label>
									<?php $selected_amazon_update_price = get_option('amazon_update_price'); ?>
									<select name="amazon_update_price" id="amazon_update_price">
										<option value="yes" <?php if($selected_amazon_update_price == 'yes' || $selected_amazon_update_price == '') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-api'); ?></option>
										<option value="no" <?php if($selected_amazon_update_price == 'no') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-api'); ?></option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_update_url"><?php _e('URL', 'affiliatetheme-api'); ?></label>
									<?php $selected_amazon_update_url = get_option('amazon_update_url'); ?>
									<select name="amazon_update_url" id="amazon_update_url">
										<option value="yes" <?php if($selected_amazon_update_url == 'yes' || $selected_amazon_update_url == '') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-api'); ?></option>
										<option value="no" <?php if($selected_amazon_update_url == 'no') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-api'); ?></option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_update_external_images"><?php _e('Externe Bilder', 'affiliatetheme-api'); ?></label>
									<?php $selected_amazon_update_external_images = get_option('amazon_update_external_images'); ?>
									<select name="amazon_update_external_images" id="amazon_update_external_images">
										<option value="yes" <?php if($selected_amazon_update_external_images == 'yes' || $selected_amazon_update_external_images == '') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-api'); ?></option>
										<option value="no" <?php if($selected_amazon_update_external_images == 'no') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-api'); ?></option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_show_reviews"><?php _e('Bewertungen', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_update_rating = get_option('amazon_update_rating'); ?>
									<select name="amazon_update_rating" id="amazon_update_rating">
										<option value="yes" <?php if($selected_amazon_update_rating == 'yes' || $selected_amazon_update_rating == '1') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-api'); ?></option>
										<option value="no" <?php if($selected_amazon_update_rating == 'no' || $selected_amazon_update_rating == '') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-api'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Mit dieser Einstellung werden Bewertungen (und derren Anzahl) während des regelmäßigen Update-Prozesses aktualisiert.<br> <span style="color:#c01313"><strong>Achtung:</strong> Sofern du das Rating manuell angepasst hast, wird diese <u>überschrieben!</u></span>', 'affiliatetheme-amazon'); ?></p>
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
					<h3 class="hndle"><span><?php _e('Amazon durchsuchen', 'affiliatetheme-amazon'); ?></span></h3>
					<div class="inside">
						<div class="form-container">
							<div class="form-group">
								<label for="search"><?php _e('Suche', 'affiliatetheme-amazon'); ?></label>
								<input type="text" name="search" id="search">
							</div>
							<div class="form-group">
								<label><?php _e('Kategorie', 'affiliatetheme-amazon'); ?></label>
								<?php if(at_aws_search_index_list()) echo at_aws_search_index_list(); ?>
							</div>
                            <hr>
                            <div class="form-container" style="">
                                <form class="form-inline" method="post" action="">
                                    <div class="form-group">
                                        <label for="grabburl"><?php _e('ASIN Grabber', 'affiliatetheme-amazon'); ?></label>
                                        <input type="text" class="form-control" id="grabburl" name="grabburl" placeholder="URL eintragen...">
                                        <button id="grab-link" class="button button-primary"><?php _e('Grab ASINs', 'affiliatetheme-amazon'); ?></button>
                                    </div>

                                </form>
                                <div class="clearfix"></div>
                            </div>
                            <div class="form-container">
                                <div class="form-group">
                                    <label for="grabbedasins" class="control-label"><?php _e('Suche nach ASINs', 'affiliatetheme-amazon'); ?></label>
                                    <textarea name="grabbedasins" id="grabbedasins" cols="30" rows="10" placeholder="ASINs eintragen..."></textarea>
                                    <textarea id="leavedasins" cols="30" rows="10" placeholder="Fehlende ASINs..." class="hidden"></textarea>
                                    <button class="button button-small" id="asinsremlist" title="hide/show remaining">&lt;&gt;</button>
                                </div>
                                <div class="clearfix"></div>
                            </div>
							<div class="form-group submit-group">
								<input type="hidden" name="page" id="page" value="1">
								<input type="hidden" name="max-pages" id="max-pages" value="">
								<button id="search-link" class="button button-primary"><?php _e('Suche', 'affiliatetheme-amazon'); ?></button>
							</div>
						</div>
					
				
						<div id="info-title"></div>
						
						<div class="page-links" style="margin-bottom:15px;">
							<button class="prev-page button">« <?php _e('Vorherige Seite', 'affiliatetheme-amazon'); ?></button>
							<button class="next-page button"><?php _e('Nächste Seite', 'affiliatetheme-amazon'); ?> »</button>
						</div>
										
						<table class="wp-list-table widefat fixed products">
							<thead>
								<tr>
									<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
										<label class="screen-reader-text" for="cb-select-all-1">Alle auswählen</label><input id="cb-select-all-1" type="checkbox">
									</th>
									<th scope="col" id="asin" class="manage-column column-asin">
										<span><?php _e('ASIN', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="image" class="manage-column column-image">
										<span><?php _e('Vorschau', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="title" class="manage-column column-title">
										<span><?php _e('Titel', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="rating" class="manage-column column-rating">
										<span><?php _e('Bewertung', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="price" class="manage-column column-price">
										<span><?php _e('Preis', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="margin" class="manage-column column-margin">
										<span><?php _e('Provision', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="category" class="manage-column column-category">
										<span><?php _e('Kategorie', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="actions" class="manage-column column-action">
										<span></span>
									</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td colspan="9">
										<?php
										if(get_products_multiselect_tax_form())
											echo '<div class="taxonomy-select">' . get_products_multiselect_tax_form() . '</div>';
										?>
										<div class="clearfix"></div>
										<a href="#" class="mass-import button button-primary"><?php _e('Ausgewählte Produkte importieren', 'affiliatetheme-amazon'); ?></a>
									</td>
								</tr>
							</tfoot>
							<tbody id="results"></tbody>
						</table>

						<div class="page-links" style="margin-top:15px;">
							<button class="prev-page button">« <?php _e('Vorherige Seite', 'affiliatetheme-amazon'); ?></button>
							<button class="next-page button"><?php _e('Nächste Seite', 'affiliatetheme-amazon'); ?> »</button>
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

			<!-- START: API Feed -->
			<div id="feed" class="at-api-tab">
				<div id="at-import-settings" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('Feed', 'affiliatetheme-amazon'); ?></span></h3>
					<div class="inside">
						<p><?php _e('Du kannst bestimmte Suchbegriffe hinterlegen, welche regelmäßig automatisch aberufen werden. Nicht importierte Produkte werden dann automatisch angelegt.', 'affiliatetheme-amazon'); ?></p>
						<table class="feed">
							<tbody>
								<?php
								$feed_itmes = at_amazon_feed_read();
								if($feed_itmes) {
									foreach($feed_itmes as $item) {
										$curr_status = $item->status;
										$change_status = ($item->status == '1' ? '0' : '1');
										?>
										<tr class="item closed" data-id="<?php echo $item->id; ?>">
											<td>
												<table>
													<tr>
														<td><div class="handle"></div></td>
														<td><?php echo $item->keyword; ?></td>
														<td><?php echo $item->last_message; ?></td>
														<td><?php echo at_amazon_feed_status_label($item->status); ?></td>
														<td><a href="#" class="change-status" data-id="<?php echo $item->id; ?>" data-status="<?php echo $change_status; ?>"><?php echo ($curr_status == '1' ? __('pausieren', 'affiliatetheme-amazon') : __('aktivieren', 'affiliatetheme-amazon')); ?></a> | <a href="#" class="delete-keyword" data-id="<?php echo $item->id; ?>"><?php _e('löschen', 'affiliatetheme-amazon'); ?></a></td>
													</tr>

													<tr class="inside">
														<td colspan="5">
															<form id="feed-item-<?php echo $item->id; ?>" class="edit-feed-item">
																<div class="row">
																	<div class="form-group">
																		<label for="post_status"><?php _e('Beitragsstatus', 'affiliatetheme-amazon'); ?></label>
																		<select name="post_status">
																			<option value="publish" <?php if($item->post_status == 'publish') echo 'selected'; ?>><?php _e('Veröffentlichen', 'affiliatetheme-amazon'); ?></option>
																			<option value="draft" <?php if($item->post_status == 'draft') echo 'selected'; ?>><?php _e('Entwurf', 'affiliatetheme-amazon'); ?></option>
																		</select>
																	</div>

																	<div class="form-group">
																		<label for="images"><?php _e('Bilder importieren', 'affiliatetheme-amazon'); ?></label>
																		<select name="images">
																			<option value="1" <?php if($item->images == '1') echo 'selected'; ?>><?php _e('Ja', 'affiliatetheme-amazon'); ?></option>
																			<option value="0" <?php if($item->images == '0') echo 'selected'; ?>><?php _e('Nein', 'affiliatetheme-amazon'); ?></option>
																		</select>
																	</div>

																	<div class="form-group">
																		<label for="description"><?php _e('Beschreibung importieren', 'affiliatetheme-amazon'); ?></label>
																		<select name="description">
																			<option value="1" <?php if($item->description == '1') echo 'selected'; ?>><?php _e('Ja', 'affiliatetheme-amazon'); ?></option>
																			<option value="0" <?php if($item->description == '0') echo 'selected'; ?>><?php _e('Nein', 'affiliatetheme-amazon'); ?></option>
																		</select>
																	</div>

																	<div class="form-group">
																		<label for="category"><?php _e('Kategorie', 'affiliatetheme-amazon'); ?></label>
																		<?php if(at_aws_search_index_list()) echo at_aws_search_index_list(true, false, $item->category); ?>
																	</div>
																</div>

																<?php
																if(get_products_multiselect_tax_form()) {
																	if($item->tax) {
																		$taxonomies = unserialize($item->tax);
																	} else {
																		$taxonomies = array();
																	}

																	echo '<div class="taxonomy-select">' . get_products_multiselect_tax_form(false, $taxonomies) . '</div>';
																}
																?>

																<div class="row">
																	<button type="submit" class="button button-primary"><?php _e('Speichern', 'affiliatetheme-amazon'); ?></button>
																</div>

																<div id="form-messages"></div>
															</form>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<?php
									}
								} else {
									?>
									<tr>
										<td colspan="4">
											<?php _e('Es wurde bisher kein Suchbegriff hinterlegt', 'affiliatetheme-amazon'); ?>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>

						<hr>

						<form id="add-new-keyword">
							<input name="keyword" class="form-control" placeholder="Suchbegriff" />
							<?php if(at_aws_search_index_list()) echo at_aws_search_index_list(true, false); ?>
							<button class="button"><?php _e('hinzufügen', 'affiliatetheme-amazon'); ?></button>
						</form>

						<div id="feed-messages"></div>
					</div>
				</div>
			</div>
			<!-- END: API Log Tab-->

			<!-- START: API Log Tab-->
			<div id="apilog" class="at-api-tab">
				<div id="at-import-settings" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('API Log', 'affiliatetheme-amazon'); ?></span></h3>
					<div class="inside">
                        <p><?php _e('Hier werden dir die letzten 200 Einträge der API log angezeigt.', 'affiliatetheme-amazon'); ?></p>
                        <p><a href="" class="clear-api-log button" data-type="amazon" data-hash="<?php echo AWS_CRON_HASH; ?>"><?php _e('Log löschen', 'affiliatetheme-amazon'); ?></a></p>
						<table class="apilog">
							<thead>
								<tr>
									<th><?php _e('Datum', 'affiliatetheme-amazon') ?></th>
									<th><?php _e('Typ', 'affiliatetheme-amazon') ?></th>
									<th><?php _e('Nachricht', 'affiliatetheme-amazon') ?></th>
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
					<h3 class="hndle"><span><?php _e('Buttons', 'affiliatetheme-amazon'); ?></span></h3>
					<div class="inside">
						<form action="options.php" method="post" id="<?php echo $plugin_button_options; ?>_form" name="<?php echo $plugin_button_options; ?>_form">
							<?php settings_fields($plugin_button_options); ?>
							<?php do_settings_sections( $plugin_button_options ); ?>
							<p class="hint">
								Wenn du für Amazon Produkte spezielle Button-Texte ausgeben möchtest, kannst du diese hier angeben.<br>
								Falls du das Amazon-Icon verwenden willst, nutze hierfür <mark><?php echo htmlentities('<i class="fa fa-amazon"></i>'); ?></mark>
							</p>
							<div class="form-container">
								<div class="form-group">
									<label for="amazon_buy_short_button"><?php _e('Kaufen Button (kurz)', 'affiliatetheme-amazon'); ?></label>
									<input type="text" name="amazon_buy_short_button" value="<?php echo (get_option('amazon_buy_short_button') ? htmlentities(get_option('amazon_buy_short_button')) : 'Kaufen'); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_buy_button"><?php _e('Kaufen Button', 'affiliatetheme-amazon'); ?></label>
									<input type="text" name="amazon_buy_button" value="<?php echo (get_option('amazon_buy_button') ? htmlentities(get_option('amazon_buy_button')) : 'Jetzt bei Amazon kaufen'); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_not_avail_button"><?php _e('Nicht Verfügbar', 'affiliatetheme-amazon'); ?></label>
									<input type="text" name="amazon_not_avail_button" value="<?php echo (get_option('amazon_not_avail_button') ? htmlentities(get_option('amazon_not_avail_button')) : 'Nicht Verfügbar'); ?>" />
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

<style>
	table.products tfoot .taxonomy-select{display:none;}
	table .taxonomy-select .form-group { background: #fafafa; float: left; padding: 10px; marign: 10px 20px 10px 0 !important; border: 1px solid #eee;  }
	@media(min-width: 1200px) { table .taxonomy-select .form-group { width: 20%; } }
	@media (min-width: 961px) and (max-width: 1199px) { table .taxonomy-select .form-group { width: 27%; } }
	@media (min-width: 783px) and (max-width: 960px) { table .taxonomy-select .form-group { width: 43%; } }
	@media (max-width: 782px) { table .taxonomy-select .form-group { width: 100% !important; min-width: 300px; } }
	@media (max-width: 400px) { table .taxonomy-select .form-group { min-width: 220px; } }
	table .taxonomy-select label { display: block; font-weight: 600; margin-bottom: 5px; } }
	table .taxonomy-select .select2-container { width: 100%; }
	table .taxonomy-select input { display: block !important; width: 100% !important; max-width: auto; min-width: 0 !important; }
	table .taxonomy-select .form-control { padding: 5px !important; margin: 5px 0 0 0 !important; border: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;  border-bottom: 1px dashed #bbb !important; }
	table .taxonomy-select select + label { display: none !important; }

	table.feed .item .handle{cursor: pointer;width: 27px;height: 30px;}
	table.feed .item .handle:before {right: 12px;font: 400 20px/1 dashicons;speak: none;display: inline-block;padding: 8px 10px;top: 0;position: relative;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;text-decoration: none !important;content: '\f142';}
	table.feed .item.closed .handle:before {content: '\f140';}
	table.feed .item.closed .inside{display:none;}
</style>