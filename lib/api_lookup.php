<?php

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Endcore\AmazonApi;
use Endcore\FormattedItemResponse;

// http://ama.local/wp-admin/admin-ajax.php?action=amazon_api_lookup&func=modal&asin=B00EXHLKVY&height=700&width=820&random=1570113404346
add_action('wp_ajax_amazon_api_lookup', 'at_aws_lookup');
add_action('wp_ajax_at_aws_lookup', 'at_aws_lookup');
function at_aws_lookup() {
    $hostAndRegion = at_amazon_get_host_region();

    $config = new Configuration();
    $config->setAccessKey(AWS_API_KEY);
    $config->setSecretKey(AWS_API_SECRET_KEY);
    $partnerTag = AWS_ASSOCIATE_TAG;
    $config->setHost($hostAndRegion['host']);
    $config->setRegion($hostAndRegion['region']);
    $apiInstance = new AmazonApi(new EnGuzzleHttp\Client(), $config);

	// vars
	$asin = (isset($_GET['asin']) ? $_GET['asin'] : '');
	$resources = GetItemsResource::getAllowableEnumValues();

	$lookup = new GetItemsRequest();
	$lookup->setItemIds([$asin]);
    $lookup->setPartnerTag($partnerTag);
	$lookup->setPartnerType(PartnerType::ASSOCIATES);
	$lookup->setResources($resources);

    $invalidPropertyList = $lookup->listInvalidProperties();
    $length = count($invalidPropertyList);
    if ($length > 0) {
        echo "Error forming the request", PHP_EOL;
        foreach ($invalidPropertyList as $invalidProperty) {
            echo $invalidProperty, PHP_EOL;
        }
        return;
    }

    try {
        $getItemsResponse = $apiInstance->getItems($lookup);
        $formattedResponse = new FormattedItemResponse($getItemsResponse);
    } catch (Exception $e) {
        at_write_api_log('amazon', 'system', $e->getMessage());
        http_response_code($e->getCode());
        exit();
    }


	if ($formattedResponse->hasResult()) {
		$item = $formattedResponse->getItem();
        $attributes = $item->getAttributes();

		if($item) {
		    $title = $item->getTitle();
			$asin = $item->getASIN();
			$ean = $item->getEan();
			$price = $item->getPriceAmount();
			$price_list = $item->getPriceList();
			$salesRank = $item->getSalesRank();
		    $currency = $item->getCurrency();
		    $url = $item->getUrl();
			$description = $item->getDescription();
			//$ratings_average = $item->getAverageRating();
			//$ratings_average_rounded = round($ratings_average / .5) * .5;
			//$ratings_count = ($item->getTotalReviews() ? $item->getTotalReviews() : '0');
			$prime = $item->isPrime();
		    $images = $item->getAllLargeImages();

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
							<?php echo at_get_product_rating_list(); ?>
						</div>
						
						<div class="form-group col-xs-4">
							<label><?php _e('Anzahl der Bewertungen', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="ratings_count" name="ratings_count" class="form-control" value="" />
						</div>
					</div>

					<div class="row">
						<div class="form-group col-xs-4">
							<label><?php _e('SalesRank', 'affiliatetheme-amazon'); ?></label>
							<input type="text" id="salesrank" name="salesrank" class="form-control" value="<?php echo $salesRank; ?>" readonly/>
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
                     * Produkteigenschaften
                     */
                    $properties = $attributes;
                    if(is_array($properties)) {?>
                        <div id="item-attributes">
                            <div id="attributes-headline">
                                <h3 id="item-attributes-headline"><?php _e('Produkteigenschaften anzeigen','affiliatetheme-amazon')?></h3>
                            </div>
                            <div id="attribute-content" class="inside acf-fields -top" style="display: none;">

                                <?php
                                $counter = 0;
                                $selector = new acf_field_field_selector();
                                $fields = $selector->get_selectable_item_fields(null,true);
                                $selectable = $selector->get_items("", $fields);
                                $groups = acf_get_field_groups(array('post_type' => 'product'));
                                foreach($groups as $group) {
                                    $hasselectable = false;
                                    foreach($selectable as $field) {
                                        if($field['group']['ID'] == $group['ID']) {
                                            $hasselectable = true;
                                        }
                                    }
                                    if($hasselectable) {
                                        ?>
                                        <div class="acf-field" id="attributes-group-<?php echo $group['key']; ?>">
                                        <h3 id="attributes-group-headline-<?php echo $group['key']; ?>"><?php echo $group['title']; ?></h3>
                                        <?php
                                    }

                                    foreach($selectable as $field){
                                        if($field['group']['ID'] == $group['ID']) {
                                            ?>

                                            <div class="form-group acf-<?php echo $group['key']?> " style="display: none;">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <label for="<?php echo $field['name'] ?>"><?php echo $field['label'] ?></label>
                                                        <input type="text" id="attributes-input-<?php echo $counter ?>" name="<?php echo $field['name'] ?>" value="" class="form-control"/>
                                                    </div>

                                                    <div class="col-sm-8">
                                                        <label for="attributes-select-<?php echo $counter ?>"><?php _e('Wert aus dem Feed auswählen', 'affiliatetheme-affilinet'); ?></label>
                                                        <select id="attributes-select-<?php echo $counter ?>" class="form-control">
                                                            <option value="">-</option>
                                                            <?php
                                                            foreach ($properties as $key=>$value) {
                                                                if ($value != "") {
                                                                    ?>
                                                                    <option value="<?php echo $value ?>"><?php echo $key . " - " . $value ?></option>
                                                                    <?php
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <script type="text/javascript">
                                                    var select = document.getElementById('attributes-select-<?php echo $counter?>');
                                                    select.onchange = function () {
                                                        var input = document.getElementById('attributes-input-<?php echo $counter?>');
                                                        input.value = this.value;
                                                    }
                                                    var attdiv = document.getElementById('attributes-group-headline-<?php echo $group['key']; ?>');
                                                    attdiv.onclick = function(){
                                                        this.classList.toggle('open');

                                                        var content = document.getElementsByClassName('form-group acf-<?php echo $group['key']?>');

                                                        if(content[0].style.display == 'none') {
                                                            for(var i=0;i<content.length;i++)
                                                                content[i].style.display = 'block';
                                                        } else {
                                                            for(var i=0;i<content.length;i++)
                                                                content[i].style.display = 'none';
                                                        }
                                                    }

                                                </script>
                                            </div>
                                            <?php $counter++;
                                        }
                                    }

                                    if($hasselectable) {
                                        ?>
                                        </div>
                                        <?php
                                    }
                                }?>
                            </div>
                        </div>
                        <script type="text/javascript">
                            var attdiv = document.getElementById('attributes-headline');
                            attdiv.onclick = function(){
                                var content = document.getElementById('attribute-content');
                                var headline = document.getElementById('item-attributes-headline');
                                if(content.style.display == 'none'){
                                    content.style.display = 'block';
                                    headline.innerHTML = '<?php _e('Produkteigenschaften ausblenden','affiliatetheme-affilinet')?>';
                                }else
                                {
                                    content.style.display = 'none';
                                    headline.innerHTML = '<?php _e('Produkteigenschaften anzeigen','affiliatetheme-affilinet')?>';
                                }
                            }
                        </script>
                    <?php } ?>
					
					<?php
					/*
					* Taxonomien
					*/
					if(get_products_multiselect_tax_form())
						echo '<h3>' . __('Taxonomien', 'affiliatetheme-amazon') . '</h3>' . get_products_multiselect_tax_form(true, array(), $properties);
						
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
                                <input type="hidden" name="prime" value="<?php echo $prime; ?>" />
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
