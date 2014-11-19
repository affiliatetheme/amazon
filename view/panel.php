<div class="wrap" id="affiliatetheme-page">
	<div class="affiliatetheme">
		<h1>AffiliateTheme Import » Amazon</h1>
		<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
		
		<div id="affiliatetheme-settings" class="metabox-holder postbox">
			<h3 class="hndle"><span>Einstellungen</span> <a href="#" class="toggle-settings">(anzeigen)</a></h3>
			<div class="inside">
				<form action="options.php" method="post" id="<?php echo $plugin_options; ?>_form" name="<?php echo $plugin_options; ?>_form">
					<?php settings_fields($plugin_options); ?>
					<?php do_settings_sections( $plugin_options ); ?>
					<table class="widefat">
						<tfoot>
						   <tr>
							 <th colspan="2"><?php submit_button(); ?></th>
						   </tr>
						</tfoot>
						<tbody>
							<tr>
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<label for="amazon_public_key">Public Key</label>
								</td>      
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<input type="text" class="widefat" name="amazon_public_key" value="<?php echo get_option('amazon_public_key'); ?>" />
								</td>     
							</tr>
							
							<tr>
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<label for="amazon_secret_key">Secret Key</label>
								</td>      
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<input type="text" class="widefat" name="amazon_secret_key" value="<?php echo get_option('amazon_secret_key'); ?>" />
								</td>     
							</tr>
							
							<tr>
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<label for="amazon_country">Land (AWS)</label>
								</td>      
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<?php $selected_amazon_country = get_option('amazon_country'); ?>
									<select name="amazon_country" id="amazon_country" class="widefat">
										<option value="DE" <?php if($selected_amazon_country == "DE") echo 'selected'; ?>>Deutschland</option>
										<option value="US" <?php if($selected_amazon_country == "US") echo 'selected'; ?>>US</option>
									</select>
								</td>     
							</tr>
							
							<tr>
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<label for="amazon_partner_id">Partner ID</label>
								</td>      
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<input type="text" class="widefat" name="amazon_partner_id" value="<?php echo get_option('amazon_partner_id'); ?>" />
								</td>     
							</tr>
		
							<tr>
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<label for="amazon_benachrichtigung">Benachrichtigung</label>
								</td>      
								<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">
									<?php $selected_amazon_benachrichtigung = get_option('amazon_benachrichtigung'); ?>
									<select name="amazon_benachrichtigung" id="amazon_benachrichtigung" class="widefat">
										<option value="">Nichts</option>
										<option value="email" <?php if($selected_amazon_benachrichtigung == "email") echo 'selected'; ?>>E-Mail Benachrichtigung</option>
										<option value="draft" <?php if($selected_amazon_benachrichtigung == "draft") echo 'selected'; ?>>Produkt als Entwurf setzen</option>
										<option value="email_draft" <?php if($selected_amazon_benachrichtigung == "email_draft") echo 'selected'; ?>>E-Mail Benachrichtigung & Produkt als Entwurf setzen</option>
									</select>
									<br><p style="color:#999">Was soll passieren wenn ein Produkt nicht mehr verfügbar ist?</p>
								</td>     
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>


		<div id="affiliatetheme-steps" class="metabox-holder postbox amazon-api-cont">
			<h3 class="hndle"><span>Amazon durchsuchen</span></h3>
			<div class="inside">
				<div class="form-container">
					<div class="form-group">
						<label for="search">Suche</label>
						<input type="text" name="search" id="search">
					</div>
					
					<div class="form-group">
						<label>Kategorie</label>
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
										
					<div class="form-group submit-group">
						<input type="hidden" name="page" id="page" value="1">
						<input type="hidden" name="max-pages" id="max-pages" value="">
						<a href="#" id="search-link" class="button button-primary">Suche</a>
					</div>
					
					<div class="clearfix"></div>
				</div>
				
				<div id="page-links">
					<a href="#" id="prev-page">« Vorherige Seite</a>
					<a href="#" id="next-page">Nächste Seite »</a>
				</div>
				
				<table class="wp-list-table widefat fixed produkte">
					<colgroup>
						<col width="40">
						<col width="115">
						<col width="75">
						<col width="200">
						<col width="400">
						<col width="100">
						<col width="100">
						<col width="60">
					</colgroup>
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
								<label class="screen-reader-text" for="cb-select-all-1">Alle auswählen</label><input id="cb-select-all-1" type="checkbox">
							</th>
							<th scope="col" id="asin" class="manage-column column-asin" style="width:110px;">
								<span>ASIN</span>
							</th>
							<th scope="col" id="image" class="manage-column column-image" style="width:100px;">
								<span>Vorschau</span>
							</th>
							<th scope="col" id="title" class="manage-column column-title" style="">
								<span>Titel</span>
							</th>
							<th scope="col" id="description" class="manage-column column-description" style="">
								<span>Beschreibung</span>
							</th>
							<th scope="col" id="price" class="manage-column column-price" style="">
								<span>Preis</span>
							</th>
							<th scope="col" id="category" class="manage-column column-category" style="">
								<span>Kategorie</span>
							</th>
							<th scope="col" id="actions" class="manage-column column-action" style="">
								<span>Aktion</span>
							</th>
						</tr>
					</thead>
					<tbody id="results"></tbody>
				</table>
				
				<?php add_thickbox(); ?>
				<div id="my-content-id" style="display:none;">
				     <p>
				          This is my hidden content! It will appear in ThickBox when the link is clicked.
				     </p>
				</div>
			</div>
		</div>
	</div>
</div>
