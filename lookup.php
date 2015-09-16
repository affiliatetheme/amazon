<?php
/**
 * Copyright 2015 - endcore
 * lookup
 */
require_once ABSPATH.'/wp-load.php';
require_once dirname(__FILE__).'/lib/bootstrap.php';
require_once dirname(__FILE__).'/config.php';

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Zend\Service\Amazon;

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

$lookup = new Lookup();
$lookup->setItemId($_GET['asin']);
$lookup->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'Offers', 'OfferFull', 'Images', 'Reviews', 'Variations'));

/* @var $formattedResponse Amazon\SingleResultSet */
$formattedResponse = $apaiIO->runOperation($lookup);

if ($formattedResponse->hasItem()) {
	/*
	 * @TODO: ean
	 */
	
	$item = $formattedResponse->getItem();

    $title = $item->Title;
	$asin = $item->ASIN;
	$ean = $item->getEan();
	$price = $item->getAmountForAvailability();
	$price_list = $item->getAmountListPrice();
    $currency = $item->getCurrencyCode();
    $url = $item->getUrl();
	$images = $item->getAllImages()->getLargeImages();
    $ratingUrl = $item->getRatingUrl();
	$average_rating = $item->getAverageRating();
	$description = $item->getItemDescription();
	$average_rating_rounded = round($average_rating / .5) * .5;
	if($item->getTotalReviews()): $rating_cnt = $item->getTotalReviews(); else : $rating_cnt = 0; endif;
    $total_reviews = $item->getTotalReviews();
	
	?>
	<div class="container">
		<form action="" id="import-product">
			<div class="row">
				<div class="form-group col-xs-12">
					<label>Titel</label> 
					<input type="text" id="title" name="title" class="form-control" value="<?php echo $title; ?>"/>
				</div>
			
				<div class="form-group col-xs-4">
					<label>ASIN</label> 
					<input type="text" id="asin" name="asin" class="form-control" value="<?php echo $asin; ?>" readonly/>
				</div>
				
				<div class="form-group col-xs-4">
					<label>Bewertung</label>
					<?php echo at_get_product_rating_list($average_rating_rounded); ?>
				</div>
				
				<div class="form-group col-xs-4">
					<label>Bewertungen</label>
					<input type="text" id="rating_cnt" name="rating_cnt" class="form-control" value="<?php echo $rating_cnt; ?>" />
				</div>
				
				<div class="form-group col-xs-6">
					<label>Listenpreis</label>
					<input type="text" id="price_list" name="price_list" class="form-control" value="<?php echo $price_list; ?>" readonly/>
				</div>

				<div class="form-group col-xs-6">
					<label>Preis</label>
					<input type="text" id="price" name="price" class="form-control" value="<?php echo $price; ?>" readonly/>
				</div>
			</div>
			
			<?php 
			/*
			 * Description
			 */
			if('1' == get_option('amazon_import_description')) { ?>
				<h3>Beschreibung</h3>
				<textarea name="description" class="widefat product-description" rows="5"><?php echo $description; ?></textarea>
			<?php } ?>
			
			<?php
			/*
			* Taxonomien
			*/
			if(get_products_multiselect_tax_form())
				echo '<h3>Taxonomien</h3>' . get_products_multiselect_tax_form();
				
			/*
			 * Existrierende Produkte
			 */
			if(at_get_existing_products())
                echo '<h3>Existierendes Produkt aktualisieren</h3>' . at_get_existing_products();

			/*
			* Product Image
			*/			
			if($item->hasImages()) {
				$images = $item->getAllImages()->getLargeImages();
				$i = 1;
				?>
				<h3>Produktbild(er) <small class="alignright"><input type="checkbox" name="selectall" class="select-all"/> Alle Bilder überspringen</small></h3>
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
									<label>Bildname</label> <input type="text" name="image[<?php echo $i; ?>][filename]" data-url="<?php echo $image; ?>" id="image[<?php echo $i; ?>][filename]" value="<?php echo $image_filename; ?>" /> 
									.<?php echo $image_ext; ?>
								</div>
								
								<div class="form-group small">
									<label>ALT-Tag</label> 
									<input type="text" name="image[<?php echo $i; ?>][alt]" id="image[<?php echo $i; ?>][alt]" value="" />
								</div>
							</div>
							
							<div class="row">
								<div class="col-xs-6">
									<div class="form-group small"><label>Artikelbild</label> <input type="checkbox" name="image[<?php echo $i; ?>][thumb]" value="true" class="unique" <?php if($i==1) echo 'checked'; ?>/></div>
								</div>
								
								<div class="col-xs-6">
									<div class="form-group small"><label>Überspringen</label> <input type="checkbox" name="image[<?php echo $i; ?>][exclude]" value="true" class="disable-this"/></div>
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
						<input type="hidden" name="action" value="amazon_api_import" />
						<input type="hidden" name="mass" value="false" />
						<button type="submit" id="import" name="import" class="single-import-product button button-primary">Importieren</button>
						<button type="submit" id="tb-close" class="button" onclick="self.parent.tb_remove();return false">Schließen</button>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>	
		</form>
	</div>
	<?php
}

exit();