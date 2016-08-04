<?php
use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Zend\Service\Amazon;

add_action('wp_ajax_amazon_api_lookup', 'at_aws_lookup');
add_action('wp_ajax_at_aws_lookup', 'at_aws_lookup');
function at_aws_lookup() {
	$conf = new GenericConfiguration();
	try {
	    $conf
	    ->setCountry(AWS_COUNTRY)
	    ->setAccessKey(AWS_API_KEY)
        ->setSecretKey(AWS_API_SECRET_KEY)
        ->setAssociateTag(AWS_ASSOCIATE_TAG)
        ->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToSingleResponseSet');
	} catch (\Exception $e) {
	    echo $e->getMessage();
	}

	$apaiIO = new ApaiIO($conf);

	// vars
	$asin = (isset($_GET['asin']) ? $_GET['asin'] : '');

	// start lookup
	$lookup = new Lookup();
	$lookup->setItemId($asin);
	$lookup->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'Offers', 'OfferFull', 'Images', 'Reviews', 'Variations', 'SalesRank'));

	/* @var $formattedResponse Amazon\SingleResultSet */
	$formattedResponse = $apaiIO->runOperation($lookup);

	if ($formattedResponse->hasItem()) {
		$item = $formattedResponse->getItem();

		if($item) {
		    $title = $item->Title;
			$asin = $item->ASIN;
			$ean = $item->getEan();
			$price = $item->getAmountForAvailability();
			$price_list = $item->getAmountListPrice();
			$salesrank = $item->getSalesRank();
		    $currency = $item->getCurrencyCode();
		    $url = $item->getUrl();
			$description = $item->getItemDescription();
			$ratings_average = $item->getAverageRating();
			$ratings_average_rounded = round($ratings_average / .5) * .5;
			//$prime = $item->isPrime();
			$ratings_count = ($item->getTotalReviews() ? $item->getTotalReviews() : '0');
		    $images = $item->getAllImages()->getLargeImages();

			// overwrite with external images
			$amazon_images_external = get_option('amazon_images_external');
			if($amazon_images_external == '1') {
				$images = $item->getExternalImages();
			} 
			?>
			<div class="container">
				<form action="" id="import-product">
					<div class="row">
						<div class="form-group col-xs-12">
							<label><?php _e('Titel', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="title" name="title" class="form-control" value="<?php echo esc_html($title); ?>"/>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-xs-4">
							<label><?php _e('ASIN', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="asin" name="asin" class="form-control" value="<?php echo $asin; ?>" readonly/>
						</div>
						
						<div class="form-group col-xs-4">
							<label><?php _e('Bewertung', 'affiliatetheme-amazon'); ?></label>
							<?php echo at_get_product_rating_list($ratings_average_rounded); ?>
						</div>
						
						<div class="form-group col-xs-4">
							<label><?php _e('Bewertungen', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="ratings_count" name="ratings_count" class="form-control" value="<?php echo $ratings_count; ?>" />
						</div>
					</div>
					<div class="row">
						<div class="form-group col-xs-4">
							<label><?php _e('SalesRank', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="salesrank" name="salesrank" class="form-control" value="<?php echo $salesrank; ?>" readonly/>
						</div>
						
						<div class="form-group col-xs-4">
							<label><?php _e('Listenpreis', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="price_list" name="price_list" class="form-control" value="<?php echo $price_list; ?>" readonly/>
						</div>

						<div class="form-group col-xs-4">
							<label><?php _e('Preis', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="price" name="price" class="form-control" value="<?php echo $price; ?>" readonly/>
						</div>
					</div>
					
					<?php 
					/*
					 * Description
					 */
					if('1' == get_option('amazon_import_description')) { ?>
						<h3><?php _e('Beschreibung', 'affiliatetheme-amazon'); ?></h3>
						<textarea name="description" class="widefat product-description" rows="5"><?php echo $description; ?></textarea>
					<?php } ?>
					
					<?php
					/*
					* Taxonomien
					*/
					if(get_products_multiselect_tax_form())
						echo '<h3>' . __('Taxonomien', 'affiliatetheme-amazon') . '</h3>' . get_products_multiselect_tax_form();
						
					/*
					 * Existrierende Produkte
					 */
					if(at_get_existing_products())
		                echo '<h3>' . __('Existierendes Produkt aktualisieren', 'affiliatetheme-amazon') . '</h3>' . at_get_existing_products();
						echo '<p>' . __('Mit der Auswahl eines Produktes, wird dieses Amazon Produkt einem bestehenden Produkt angehängt, somit kannst du einen Preisvergleich der Anbieter erstellen.', 'affiliatetheme-amazon') . '</p>';

					/*
					* Product Image
					*/			
					if($images) {
						$i = 1;
						?>
						<h3><?php _e('Produktbild(er)', 'affiliatetheme-amazon'); ?> <small class="alignright"><input type="checkbox" name="selectall" class="select-all"/> <?php _e('Alle Bilder überspringen', 'affiliatetheme-amazon'); ?></small></h3>
						<div class="row product-images">
							<?php
		                    foreach ($images as $image) {
		                        $image_info = explode('/', $image);
		                        $image_info = array_pop($image_info);
		                        $image_info = pathinfo($image_info);
		                        $image_filename = sanitize_title($title.'-'.$i);
		                        $image_ext = $image_info['extension'];
								?>
								
								<div class="image col-sm-4" data-item="<?php echo $i; ?>">
									<div class="image-wrapper"><img src="<?php echo $image; ?>" class="img-responsive"/></div>
									<div class="image-info">
										<div class="form-group small">
											<label><?php _e('Bildname', 'affiliatetheme-amazon'); ?></label> <input type="text" name="image[<?php echo $i; ?>][filename]" data-url="<?php echo $image; ?>" id="image[<?php echo $i; ?>][filename]" value="<?php echo $image_filename; ?>" />
											.<?php echo $image_ext; ?>
										</div>
										
										<div class="form-group small">
											<label><?php _e('ALT-Tag', 'affiliatetheme-amazon'); ?></label>
											<input type="text" name="image[<?php echo $i; ?>][alt]" id="image[<?php echo $i; ?>][alt]" value="" />
										</div>
									</div>
									
									<div class="row">
										<div class="col-xs-6">
											<div class="form-group small"><label><?php _e('Artikelbild', 'affiliatetheme-amazon'); ?></label> <input type="checkbox" name="image[<?php echo $i; ?>][thumb]" value="true" class="unique" <?php if($i==1) echo 'checked'; ?>/></div>
										</div>
										
										<div class="col-xs-6">
											<div class="form-group small"><label><?php _e('Überspringen', 'affiliatetheme-amazon'); ?></label> <input type="checkbox" name="image[<?php echo $i; ?>][exclude]" value="true" class="disable-this"/></div>
										</div>
									</div>
									<input type="hidden" name="image[<?php echo $i; ?>][url]" value="<?php echo $image; ?>"/>
								</div>
								<?php
		                        $i++;
		                    }
							?>
						</div>
						<?php
					}
					?>
						
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<input type="hidden" name="currency" value="<?php echo $currency; ?>" />
								<input type="hidden" name="url" value="<?php echo $url; ?>" />
								<input type="hidden" name="ean" value="<?php echo $ean; ?>" />
								<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce("at_amazon_import_wpnonce"); ?>" />
								<input type="hidden" name="action" value="at_aws_import" />
								<input type="hidden" name="mass" value="false" />
								<button type="submit" id="import" name="import" class="single-import-product button button-primary"><?php _e('Importieren', 'affiliatetheme-amazon'); ?></button>
								<button type="submit" id="tb-close" class="button" onclick="self.parent.tb_remove();return false"><?php _e('Schließen', 'affiliatetheme-amazon'); ?></button>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>	
				</form>
			</div>
			<?php
		}
	}

	exit();
}