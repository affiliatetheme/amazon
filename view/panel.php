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
		<h1><? _e('AffiliateTheme Import » Amazon', 'affiliatetheme-amazon'); ?></h1>

		<div id="checkConnection"></div>

		<h2 class="nav-tab-wrapper" id="at-api-tabs">
			<a class="nav-tab nav-tab-active" id="settings-tab" href="#top#settings"><?php _e('Einstellungen', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" id="search-tab" href="#top#search"><?php _e('Suche', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" id="feed-tab" href="#top#feed"><?php _e('Feed', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" id="apilog-tab" href="#top#apilog"><?php _e('API Log', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" id="buttons-tab" href="#top#buttons"><?php _e('Buttons', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" id="errorhandling-tab" href="#top#errorhandling"><?php _e('Fehlerbehandlung', 'affiliatetheme-amazon'); ?></a>
			<a class="nav-tab" href="<?php echo at_amazon_get_forum_url(); ?>" target="_blank"><span class="dashicons dashicons-sos"></span> <?php _e('Hilfe', 'affiliatetheme-amazon'); ?></a>
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
									<a class="api-help" href="http://affiliatetheme.io/amazon-aws-access-key-und-secret-key-erstellen/" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>
								</div>
								<div class="form-group">
									<label for="amazon_secret_key"><?php _e('Secret Access Key', 'affiliatetheme-amazon'); ?> <sup>*</sup></label>
									<input type="password" name="amazon_secret_key" id="amazon_secret_key" value="<?php echo get_option('amazon_secret_key'); ?>" />
									<a class="api-help" href="http://affiliatetheme.io/amazon-aws-access-key-und-secret-key-erstellen/" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>
								</div>
								<div class="form-group">
									<label for="amazon_country"><?php _e('Land', 'affiliatetheme-amazon'); ?> <sup>*</sup></label>
									<?php $selected_amazon_country = get_option('amazon_country'); ?>
									<select name="amazon_country" id="amazon_country">
										<option value="de" <?php if($selected_amazon_country == "de") echo 'selected'; ?>><?php _e('Deutschland', 'affiliatetheme-amazon'); ?></option>
										<option value="com" <?php if($selected_amazon_country == "com") echo 'selected'; ?>><?php _e('USA', 'affiliatetheme-amazon'); ?></option>
										<option value="ca" <?php if($selected_amazon_country == "ca") echo 'selected'; ?>><?php _e('Kanada', 'affiliatetheme-amazon'); ?></option>
										<option value="co.uk" <?php if($selected_amazon_country == "co.uk") echo 'selected'; ?>><?php _e('Vereinigtes Königreich (UK)', 'affiliatetheme-amazon'); ?></option>
										<option value="fr" <?php if($selected_amazon_country == "fr") echo 'selected'; ?>><?php _e('Frankreich', 'affiliatetheme-amazon'); ?></option>
										<option value="it" <?php if($selected_amazon_country == "it") echo 'selected'; ?>><?php _e('Italien', 'affiliatetheme-amazon'); ?></option>
										<option value="es" <?php if($selected_amazon_country == "es") echo 'selected'; ?>><?php _e('Spanien', 'affiliatetheme-amazon'); ?></option>
										<option value="in" <?php if($selected_amazon_country == "in") echo 'selected'; ?>><?php _e('Indien', 'affiliatetheme-amazon'); ?></option>
                                        <option value="com.tr" <?php if($selected_amazon_country == "com.tr") echo 'selected'; ?>><?php _e('Türkei', 'affiliatetheme-amazon'); ?></option>
										<option value="co.jp" <?php if($selected_amazon_country == "co.jp") echo 'selected'; ?>><?php _e('Japan', 'affiliatetheme-amazon'); ?></option>
										<option value="com.mx" <?php if($selected_amazon_country == "com.mx") echo 'selected'; ?>><?php _e('Mexiko', 'affiliatetheme-amazon'); ?></option>
										<option value="cn" <?php if($selected_amazon_country == "cn") echo 'selected'; ?>><?php _e('China', 'affiliatetheme-amazon'); ?></option>
										<option value="com.br" <?php if($selected_amazon_country == "com.br") echo 'selected'; ?>><?php _e('Brasilien', 'affiliatetheme-amazon'); ?></option>
                                        <option value="com.au" <?php if($selected_amazon_country == "com.au") echo 'selected'; ?>><?php _e('Australien', 'affiliatetheme-amazon'); ?></option>
                                        <option value="ae" <?php if($selected_amazon_country == "ae") echo 'selected'; ?>><?php _e('United Arab Emirates', 'affiliatetheme-amazon'); ?></option>
                                    </select>
								</div>
								<div class="form-group">
									<label for="amazon_partner_id"><?php _e('Partner Tag', 'affiliatetheme-amazon'); ?> <sup>*</sup></label>
									<input type="text" name="amazon_partner_id" value="<?php echo get_option('amazon_partner_id'); ?>" />
									<a class="api-help" href="http://affiliatetheme.io/amazon-aws-access-key-und-secret-key-erstellen/" target="_blank"><span class="dashicons dashicons-editor-help"></span></a>
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
                                        <option value="remove" <?php if($selected_amazon_notification == "remove") echo 'selected'; ?>><?php _e('Amazon Preis von Preisvergleich entfernen', 'affiliatetheme-amazon'); ?></option>
                                        <option value="email_remove" <?php if($selected_amazon_notification == "email_remove") echo 'selected'; ?>><?php _e('E-Mail Benachrichtigung & Amazon Preis von Preisvergleich entfernen', 'affiliatetheme-amazon'); ?></option>
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
									<label for="amazon_images_external_size"><?php _e('Bildgröße der Bilder', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_images_external_size = get_option('amazon_images_external_size'); ?>
									<select name="amazon_images_external_size" id="amazon_images_external_size">
										<option value="SmallImage" <?php if($selected_amazon_images_external_size == 'SmallImage' || $selected_amazon_images_external_size == '') echo 'selected'; ?>><?php _e('Klein', 'affiliatetheme-amazon'); ?></option>
										<option value="MediumImage" <?php if($selected_amazon_images_external_size == 'MediumImage') echo 'selected'; ?>><?php _e('Mittel', 'affiliatetheme-amazon'); ?></option>
										<option value="LargeImage" <?php if($selected_amazon_images_external_size == 'LargeImage') echo 'selected'; ?>><?php _e('Groß', 'affiliatetheme-amazon'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Wir empfehlen die Bildgröße "Klein" oder "Mittel" zu wählen, die großen Bilder könnten zu einer längeren Ladezeit deiner Seite führen!', 'affiliatetheme-amazon'); ?></p>
								</div>
                                <div class="form-group">
                                    <label for="amazon_price"><?php _e('Preis', 'affiliatetheme-amazon'); ?></label>
                                    <?php $selected_amazon_price = get_option('amazon_price'); ?>
                                    <select name="amazon_price" id="amazon_price">
                                        <option value="default"  <?php if($selected_amazon_price == 'default' || $selected_amazon_price == '') echo 'selected'; ?>><?php _e('Standard Preis', 'affiliatetheme-amazon'); ?></option>
                                        <option value="lowest" <?php if($selected_amazon_price == 'lowest') echo 'selected'; ?>><?php _e('Niedrigster Preis', 'affiliatetheme-amazon'); ?></option>
                                    </select>
                                </div>
								<div class="form-group">
									<label for="amazon_show_reviews"><?php _e('Kundenrezensionen', 'affiliatetheme-amazon'); ?></label>
									<input type="checkbox" name="amazon_show_reviews" id="amazon_show_reviews" value="1" <?php if('1' == get_option('amazon_show_reviews')) echo 'checked'; ?>> <?php _e('Kundenrezensionen auf der Produktdetailseite verlinken', 'affiliatetheme-amazon'); ?>
								</div>
								<h3><?php _e('Einstellungen für den Update-Prozess', 'affiliatetheme-amazon'); ?></h3>
								<p><?php printf(__('Nächster Start des Cronjobs: %s', 'affiliatetheme-amazon'), at_amazon_cronjob_next_run()); ?></p>
								<div class="form-group">
									<label for="amazon_update_run_cronjob"><?php _e('Aktualisierung', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_update_run_cronjob = get_option('amazon_update_run_cronjob'); ?>
									<select name="amazon_update_run_cronjob" id="amazon_update_run_cronjob">
										<option value="yes" <?php if($selected_amazon_update_run_cronjob == 'yes' || $selected_amazon_update_run_cronjob == '') echo 'selected'; ?>><?php _e('Aktiviert', 'affiliatetheme-amazon'); ?></option>
										<option value="no" <?php if($selected_amazon_update_run_cronjob == 'no') echo 'selected'; ?>><?php _e('Deaktiviert', 'affiliatetheme-amazon'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Mit dieser Einstellung kannst du die automatische Aktualisierung deaktivieren.<br>Wenn du den Cronjob extern starten möchtest, solltest diese Option deaktivieren.', 'affiliatetheme-amazon'); ?></p>
								</div>
								<div class="form-group">
									<label for="amazon_update_ean"><?php _e('EAN', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_update_ean = get_option('amazon_update_ean'); ?>
									<select name="amazon_update_ean" id="amazon_update_ean">
										<option value="yes" <?php if($selected_amazon_update_ean == 'yes' || $selected_amazon_update_ean == '') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-amazon'); ?></option>
										<option value="no" <?php if($selected_amazon_update_ean == 'no') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-amazon'); ?></option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_update_price"><?php _e('Preise', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_update_price = get_option('amazon_update_price'); ?>
									<select name="amazon_update_price" id="amazon_update_price">
										<option value="yes" <?php if($selected_amazon_update_price == 'yes' || $selected_amazon_update_price == '') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-amazon'); ?></option>
										<option value="no" <?php if($selected_amazon_update_price == 'no') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-amazon'); ?></option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_update_url"><?php _e('URL', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_update_url = get_option('amazon_update_url'); ?>
									<select name="amazon_update_url" id="amazon_update_url">
										<option value="yes" <?php if($selected_amazon_update_url == 'yes' || $selected_amazon_update_url == '') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-amazon'); ?></option>
										<option value="no" <?php if($selected_amazon_update_url == 'no') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-amazon'); ?></option>
									</select>
								</div>
								<div class="form-group">
									<label for="amazon_update_external_images"><?php _e('Bilder', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_update_external_images = get_option('amazon_update_external_images'); ?>
									<select name="amazon_update_external_images" id="amazon_update_external_images">
										<option value="yes" <?php if($selected_amazon_update_external_images == 'yes') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-amazon'); ?></option>
										<option value="no" <?php if($selected_amazon_update_external_images == 'no' || $selected_amazon_update_external_images == '') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-amazon'); ?></option>
									</select>

									<p class="form-hint"><?php _e('Mit dieser Einstellungen werden Bilder in deinem Produkt aktualisiert.<br><span style="color:#c01313"><strong>Achtung:</strong> Es werden auch vorhandene Produkte, welche keine Bilder nutzen, mit Bildern aktualisiert.</span>', 'affiliatetheme-amazon'); ?></p>
								</div>
								<?php
								/* <div class="form-group">
									<label for="amazon_update_rating"><?php _e('Bewertungen', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_update_rating = get_option('amazon_update_rating'); ?>
									<select name="amazon_update_rating" id="amazon_update_rating">
										<option value="yes" <?php if($selected_amazon_update_rating == 'yes' || $selected_amazon_update_rating == '1') echo 'selected'; ?>><?php _e('Aktualisieren', 'affiliatetheme-amazon'); ?></option>
										<option value="no" <?php if($selected_amazon_update_rating == 'no' || $selected_amazon_update_rating == '') echo 'selected'; ?>><?php _e('Nicht aktualisieren', 'affiliatetheme-amazon'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Mit dieser Einstellung werden Bewertungen (und derren Anzahl) während des regelmäßigen Update-Prozesses aktualisiert.<br> <span style="color:#c01313"><strong>Achtung:</strong> Sofern du das Rating manuell angepasst hast, wird diese <u>überschrieben!</u></span>', 'affiliatetheme-amazon'); ?></p>
								</div>
 								*/
								?>
								<div class="form-group">
									<label for="amazon_product_skip_interval"><?php _e('Aktualisierungsintervall für Produkte', 'affiliatetheme-amazon'); ?></label>
									<?php $selected_amazon_product_skip_interval = get_option('amazon_product_skip_interval'); ?>
									<select name="amazon_product_skip_interval" id="amazon_product_skip_interval">
										<option value="3600" <?php if($selected_amazon_product_skip_interval == '3600' || $selected_amazon_product_skip_interval == '') echo 'selected'; ?>><?php _e('1 Stunde', 'affiliatetheme-amazon'); ?></option>
										<option value="7200" <?php if($selected_amazon_product_skip_interval == '7200') echo 'selected'; ?>><?php _e('2 Stunden', 'affiliatetheme-amazon'); ?></option>
										<option value="10800" <?php if($selected_amazon_product_skip_interval == '10800') echo 'selected'; ?>><?php _e('3 Stunden', 'affiliatetheme-amazon'); ?></option>
										<option value="14400" <?php if($selected_amazon_product_skip_interval == '14400') echo 'selected'; ?>><?php _e('4 Stunden', 'affiliatetheme-amazon'); ?></option>
										<option value="18000" <?php if($selected_amazon_product_skip_interval == '18000') echo 'selected'; ?>><?php _e('5 Stunden', 'affiliatetheme-amazon'); ?></option>
										<option value="21600" <?php if($selected_amazon_product_skip_interval == '21600') echo 'selected'; ?>><?php _e('6 Stunden', 'affiliatetheme-amazon'); ?></option>
										<option value="43200" <?php if($selected_amazon_product_skip_interval == '43200') echo 'selected'; ?>><?php _e('1 Tag', 'affiliatetheme-amazon'); ?></option>
									</select>
									<p class="form-hint"><?php _e('Der Updateprozess wird jede Stunde angestoßen. Produkte werden für eine gewisse Zeit im Updateprozess ignoriert, damit andere Produkte auch aktualisiert werden können.<br>Bestimme hier, wie lange ein Produkt ignoriert wird. Dies ist z.B. sinnvoll, wenn du das Gefühl hast, dass nicht alle Produkte aktualisiert werden.<br>In diesem Fall solltest du den Wert höher setzen.', 'affiliatetheme-amazon'); ?></p>
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
								<label for="search"><?php _e('Suche nach Keyword(s)', 'affiliatetheme-amazon'); ?></label>
								<input type="text" name="search" id="search">
							</div>
							<div class="form-group form-dynamic-field" style="display:none;" data-hide-on="All">
								<label for="search"><?php _e('Suche nach Titel', 'affiliatetheme-amazon'); ?></label>
								<input type="text" name="title" id="title">
							</div>
							<div class="form-group">
								<label><?php _e('Kategorie', 'affiliatetheme-amazon'); ?></label>
								<?php if(at_aws_search_index_list()) echo at_aws_search_index_list(); ?>
							</div>
							<div class="form-container" style="margin-top:30px;">
								<div class="form-group">
									<h3 class="hndle" style="margin:0 -10px">
										<?php _e('Erweiterte Suche', 'affiliatetheme-amazon'); ?>
										<a href="#" class="form-toggle" data-hide-text="<?php _e('- ausblenden', 'affiliatetheme-amazon'); ?>" data-show-text="<?php _e('+ anzeigen', 'affiliatetheme-amazon'); ?>"><?php _e('+ anzeigen', 'affiliatetheme-amazon'); ?></a>
									</h3>
								</div>

								<div class="form-toggle-item" style="display:none;">
									<p><?php _e('Die meisten Filtermöglichkeiten sind nur für bestimmte Kategorien möglich, nicht für "Alle Kategorien"', 'affiliatetheme-amazon'); ?></p>
									<div class="form-group">
										<label><?php _e('Merchant', 'affiliatetheme-amazon'); ?></label>
										<select name="merchant" id="merchant">
											<option value="All" selected><?php _e('Alle', 'affiliatetheme-amazon'); ?></option>
											<option value="Amazon" ><?php _e('Amazon', 'affiliatetheme-amazon'); ?></option>
										</select>
									</div>
									<div class="form-group form-dynamic-field" data-hide-on="All" style="display:none">
										<label><?php _e('Sortieren nach', 'affiliatetheme-amazon'); ?></label>
										<select name="sort" id="sort">
											<?php $search_allowed_sort = at_aws_search_allowed_sort(); ?>
											<option value="" selected><?php _e('Sortierung wählen', 'affiliatetheme-amazon'); ?></option>
											<option value="-price" class="form-dynamic-field" data-hide-on="<?php echo $search_allowed_sort; ?>"><?php _e('Preis (Absteigend)', 'affiliatetheme-amazon'); ?></option>
											<option value="price" class="form-dynamic-field" data-hide-on="<?php echo $search_allowed_sort; ?>"><?php _e('Preis (Aufsteigend)', 'affiliatetheme-amazon'); ?></option>
										</select>
									</div>
									<?php $search_allowed_min_price = at_aws_search_allowed_param('MinimumPrice'); ?>
									<div class="form-group form-dynamic-field" data-hide-on="<?php echo $search_allowed_min_price; ?>" style="display:none">
										<label><?php _e('Minimaler Preis', 'affiliatetheme-amazon'); ?></label>
										<input type="number" name="min_price" id="min_price" />
									</div>
									<?php $search_allowed_max_price = at_aws_search_allowed_param('MaximumPrice'); ?>
									<div class="form-group form-dynamic-field" data-hide-on="<?php echo $search_allowed_max_price; ?>" style="display:none">
										<label><?php _e('Maximaler Preis', 'affiliatetheme-amazon'); ?></label>
										<input type="number" name="max_price" id="max_price" />
									</div>
								</div>
							</div>
							<div class="form-container" style="margin-top:30px;margin-bottom:30px;">
								<div class="form-group">
									<h3 class="hndle" style="margin:0 -10px">
										<?php _e('ASIN Grabber', 'affiliatetheme-amazon'); ?>
										<a href="#" class="form-toggle" data-hide-text="<?php _e('- ausblenden', 'affiliatetheme-amazon'); ?>" data-show-text="<?php _e('+ anzeigen', 'affiliatetheme-amazon'); ?>"><?php _e('+ anzeigen', 'affiliatetheme-amazon'); ?></a>
									</h3>
								</div>

								<div class="form-toggle-item" style="display:none;">
									<form class="form-inline" method="post" action="">
										<div class="form-group">
											<input type="text" class="form-control" id="grabburl" name="grabburl" placeholder="<?php _e('URL eintragen...', 'affiliatetheme-amazon'); ?>">
											<button id="grab-link" class="button button-primary"><?php _e('Grab ASINs', 'affiliatetheme-amazon'); ?></button>
										</div>

									</form>
									<div class="clearfix"></div>

									<div class="form-group">
										<textarea name="grabbedasins" id="grabbedasins" cols="30" rows="10" placeholder="<?php _e('ASINs eintragen...', 'affiliatetheme-amazon'); ?>"></textarea>
										<textarea id="leavedasins" cols="30" rows="10" placeholder="<?php _e('Fehlende ASINs...', 'affiliatetheme-amazon'); ?>" class="hidden"></textarea>
										<button class="button button-small" id="asinsremlist" title="hide/show remaining">&lt;&gt;</button>
									</div>
									<div class="clearfix"></div>
								</div>
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
										<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Alle auswählen', 'affiliatetheme-amazon'); ?></label><input id="cb-select-all-1" type="checkbox">
									</th>
									<th scope="col" id="asin" class="manage-column column-asin">
										<span><?php _e('ASIN', 'affiliatetheme-amazon'); ?></span>
									</th>
                                    <th scope="col" id="ean" class="manage-column column-ean">
                                        <span><?php _e('EAN', 'affiliatetheme-amazon'); ?></span>
                                    </th>
                                    <th scope="col" id="image" class="manage-column column-image">
										<span><?php _e('Vorschau', 'affiliatetheme-amazon'); ?></span>
									</th>
									<th scope="col" id="title" class="manage-column column-title">
										<span><?php _e('Titel', 'affiliatetheme-amazon'); ?></span>
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
						<p><?php _e('Du kannst bestimmte URLs hinterlegen, welche regelmäßig automatisch aberufen werden. Nicht importierte Produkte werden dann automatisch angelegt.', 'affiliatetheme-amazon'); ?></p>
						<table class="feed">
							<colgroup>
								<col width="30px">
								<col width="30%">
								<col width="auto">
								<col width="10%">
								<col width="10%">
								<col width="10%">
							</colgroup>
							<thead>
								<tr>
									<th></th>
                                    <th><?php _e('Name','affiliatetheme-amazon')?></th>
									<th><?php _e('Amazon URL','affiliatetheme-amazon')?></th>
									<th><?php _e('Hinzugefügt am','affiliatetheme-amazon')?></th>
									<th><?php _e('Status','affiliatetheme-amazon')?></th>
									<th><?php _e('Aktionen','affiliatetheme-amazon')?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$feed_itmes = at_amazon_feed_read();
								if($feed_itmes) {
									foreach($feed_itmes as $item) {
										$curr_status = $item->status;
										$change_status = ($item->status == '1' ? '0' : '1');
										?>
										<tr class="item closed" data-id="<?php echo $item->id; ?>">
											<td><div class="handle"></div></td>
                                            <td><?php echo $item->category; ?></td>
											<td><a href="<?php echo $item->keyword; ?>" target="_blank"><span class="dashicons dashicons-share-alt2"></span></a> <?php echo $item->keyword; ?></td>
											<td><?php echo $item->last_message; ?></td>
											<td><?php echo at_amazon_feed_status_label($item->status); ?></td>
											<td><a href="#" class="change-status" data-id="<?php echo $item->id; ?>" data-status="<?php echo $change_status; ?>"><?php echo ($curr_status == '1' ? __('pausieren', 'affiliatetheme-amazon') : __('aktivieren', 'affiliatetheme-amazon')); ?></a> | <a href="#" class="delete-keyword" data-id="<?php echo $item->id; ?>"><?php _e('löschen', 'affiliatetheme-amazon'); ?></a></td>
										</tr>
										<tr class="inside" style="display: none;">
											<td colspan="6">
												<form id="feed-item-<?php echo $item->id; ?>" class="edit-feed-item">
                                                    <div class="form-group">
                                                        <label for="category-<?php echo $item->id;?>"><?php _e('Name','affiliatetheme-amazon')?></label>
                                                        <input type="text" name="category" id="category-<?php echo $item->id;?>" value="<?php echo $item->category; ?>"/>
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
										<?php
									}
								} else {
									?>
									<tr>
										<td colspan="5">
											<?php _e('Es wurde bisher keine URL hinterlegt', 'affiliatetheme-amazon'); ?>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>

						<hr>

						<form id="add-new-keyword">
							<input type="url" name="keyword" class="form-control" placeholder="<?php _e("URL",'affiliatetheme-amazon')?>" />
							<input type="text" name="category" class="form-control" placeholder="<?php _e("Name",'affiliatetheme-amazon')?>" />
							<button class="button button-primary"><?php _e('hinzufügen', 'affiliatetheme-amazon'); ?></button>
						</form>

						<div id="feed-messages"></div>
					</div>
				</div>
			</div>
			<!-- END: API Feed Tab-->

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
								<?php printf(__('Wenn du für Amazon Produkte spezielle Button-Texte ausgeben möchtest, kannst du diese hier angeben.<br>
								Falls du das Amazon-Icon verwenden willst, nutze hierfür <mark>%s</mark>.', 'affiliatetheme-amazon'), htmlentities('<i class="fa fa-amazon"></i>')); ?>
							</p>
							<div class="form-container">
								<div class="form-group">
									<label for="amazon_buy_short_button"><?php _e('Kaufen Button (kurz)', 'affiliatetheme-amazon'); ?></label>
									<input type="text" name="amazon_buy_short_button" value="<?php echo (get_option('amazon_buy_short_button') ? htmlentities(get_option('amazon_buy_short_button')) : __('Kaufen', 'affiliatetheme-amazon')); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_buy_button"><?php _e('Kaufen Button', 'affiliatetheme-amazon'); ?></label>
									<input type="text" name="amazon_buy_button" value="<?php echo (get_option('amazon_buy_button') ? htmlentities(get_option('amazon_buy_button')) : __('Jetzt bei Amazon kaufen', 'affiliatetheme-amazon')); ?>" />
								</div>
								<div class="form-group">
									<label for="amazon_not_avail_button"><?php _e('Nicht Verfügbar', 'affiliatetheme-amazon'); ?></label>
									<input type="text" name="amazon_not_avail_button" value="<?php echo (get_option('amazon_not_avail_button') ? htmlentities(get_option('amazon_not_avail_button')) : __('Nicht Verfügbar', 'affiliatetheme-amazon')); ?>" />
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

			<!-- START: Error Handling Tab-->
			<div id="errorhandling" class="at-api-tab">
				<div id="at-import-settings" class="metabox-holder postbox">
					<h3 class="hndle"><span><?php _e('Fehlerbehandlung', 'affiliatetheme-amazon'); ?></span></h3>
					<div class="inside">
						<form action="options.php" method="post" id="<?php echo $plugin_error_handling_options; ?>_form" name="<?php echo $plugin_error_handling_options; ?>_form">
							<?php settings_fields($plugin_error_handling_options); ?>
							<?php do_settings_sections( $plugin_error_handling_options ); ?>
							<div class="form-container">
								<div class="form-group">
									<label for="amazon_error_handling_replace_thumbnails"><?php _e('Produktbilder', 'affiliatetheme-amazon'); ?></label>
									<input type="checkbox" name="amazon_error_handling_replace_thumbnails" id="amazon_error_handling_replace_thumbnails" value="1" <?php if('1' == get_option('amazon_error_handling_replace_thumbnails')) echo 'checked'; ?>> <?php _e('Fehlende Produktbilder (Beitragsbilder) wiederherstellen', 'affiliatetheme-amazon'); ?>
									<p class="form-hint"><?php _e('Sollten zu deinen Produkten die Beitragsbilder (Hauptbild) fehlen, kannst du diese mit dieser Option wiederherstellen. Sobald diese Option gesetzt ist, wird beim nächsten Updateprozess des Produkts das fehlende Beitragsbild wiederhergestellt.', 'affiliatetheme-amazon'); ?></p>
								</div>
								<div class="form-group">
									<?php submit_button(); ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- END: Error Handling Tab-->
		</div>
	</div>

    <div class="afs_ads">&nbsp;</div>
</div>

<style>
	/* TAB: SEARCH */
	#at-import-page table.products thead tr th { text-align: left; border-bottom: 1px solid #eee; padding-bottom: 10px; margin: 0; }
	#at-import-page table.products tbody tr th,
	#at-import-page table.products tbody tr td { border-top: 1px solid #eee; }
	#at-import-page table.products tbody tr:first-of-type td { border: none; }
	/*
	#at-import-page table.products thead tr th:nth-child(1) { width: 40px; }
	#at-import-page table.products thead tr th:nth-child(2) { width: 110px; }
	#at-import-page table.products thead tr th:nth-child(3) { width: 110px; }
    #at-import-page table.products thead tr th:nth-child(4) { width: 150px; }
	#at-import-page table.products thead tr th:nth-child(5) ~ th,
	#at-import-page table.products tbody tr td:nth-child(5) ~ td { width: 150px; text-align: right; }
	*/
	#at-import-page table.products tfoot .taxonomy-select { display: none; }
	#at-import-page table.products .taxonomy-select .form-group { background: #fafafa; float: left; padding: 10px; margin: 10px 20px 10px 0 !important; border: 1px solid #eee; }
	#at-import-page table.products .taxonomy-select .form-control { padding: 5px !important; margin: 5px 0 0 0 !important; border: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;  border-bottom: 1px dashed #bbb !important; }
	#at-import-page table.products .taxonomy-select .select2-container { width: 100% !important; }
	#at-import-page table.products .taxonomy-select input { display: block !important; width: 100% !important; max-width: auto; min-width: 0 !important; }
	#at-import-page table.products .taxonomy-select select + label { display: none !important; }
	#at-import-page table.products .taxonomy-select .form-group .form-control + select + span { display: none; }

	@media(min-width: 1200px) {
		#at-import-page table.products .taxonomy-select .form-group { width: 20%; }
	}
	@media (min-width: 961px) and (max-width: 1199px) {
		#at-import-page table.products .taxonomy-select .form-group { width: 27%; }
	}
	@media (min-width: 783px) and (max-width: 960px) {
		#at-import-page table.products .taxonomy-select .form-group { width: 43%; }
	}
	@media (max-width: 782px) {
		#at-import-page table.products .taxonomy-select .form-group { width: 100% !important; min-width: 300px; }
	}
	@media (max-width: 400px) {
		#at-import-page table.products .taxonomy-select .form-group { min-width: 220px; }
	}
	/* TAB: FEED */
    #at-import-page table.feed { width: 100%; }
    #at-import-page table.feed thead tr th { text-align: left; border-bottom: 1px solid #eee; padding-bottom: 10px; margin: 0; }
    #at-import-page table.feed tbody tr td { border-top: 1px solid #eee; }
    #at-import-page table.feed tbody tr:first-of-type td { border: none; }
    #at-import-page table.feed tbody tr td:nth-child(3) { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 400px; }
    #at-import-page table.feed tbody tr td:nth-child(3) a { text-decoration: none; }
    #at-import-page table.feed thead tr th:nth-child(3) ~ th,
	#at-import-page table.feed tbody tr td:nth-child(3) ~ td { text-align: right; }
    #at-import-page table.feed .item .handle { cursor: pointer; width: 27px; height: 30px; }
    #at-import-page table.feed .item .handle:before { right: 12px; font: 400 20px/1 dashicons; display: inline-block; padding: 8px 10px; top: 0; position: relative; text-decoration: none !important; content: '\f142';}
    #at-import-page table.feed .item.closed .handle:before { content: '\f140'; }
    #at-import-page table.feed .item.closed .inside { display: none; }
	#at-import-page table.feed tbody tr.inside { padding: 0; }
    #at-import-page table.feed tbody tr.inside td { padding: 0; }
    #at-import-page table.feed tbody tr.inside td .edit-feed-item { background: #fafafa; padding: 15px; }
    #at-import-page table.feed tbody tr.inside td .edit-feed-item .row { display: block; clear: both; margin-left: 0; }
    #at-import-page table.feed tbody tr.inside td .form-group { width: auto; background: none; border: none; padding: 0; margin-right: 15px; }

	.api-help { text-decoration: none; position: relative; top: 5px; }
	#checkConnection div.warning { background: #fff; border-left: 4px solid #dcb032; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); margin-bottom: 10px; padding: 1px 12px; }
    #checkConnection div.warning p.warning { color: #444; }
</style>