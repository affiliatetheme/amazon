<?php
use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;

add_action('wp_ajax_at_amazon_add_acf', 'at_amazon_add_acf');
function at_amazon_add_acf()
{
    $nonce = $_POST['_wpnonce'];

    if (!wp_verify_nonce($nonce, 'at_amazon_import_wpnonce')) {
        die('Security Check failed');
    }

    // vars
    $id = $_POST['id'];
    // quick import
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
    $lookup->setItemId($id);
    $lookup->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'Offers', 'OfferFull', 'Images', 'Variations'));
    $formattedResponse = $apaiIO->runOperation($lookup);

    if ($formattedResponse->hasItem()) {
        $item = $formattedResponse->getItem();

        if($item) {
            $price = $item->getAmountForAvailability();
            $price_list = $item->getAmountListPrice();
            $url = $item->getUrl();
            $currency = $item->getCurrencyCode();

        }
    }

    $portal = 'amazon';
    $output['rmessage']['success'] = 'true';
    $output['shop_info']['price'] = $price;
    $output['shop_info']['currency'] = (strtolower($currency) == 'eur' )? 'euro':strtolower($currency);
    $output['shop_info']['portal'] = $portal;
    $output['shop_info']['metakey'] = $id;
    $output['shop_info']['link'] = $url;
    $output['shop_info']['shop'] = (at_aws_get_amazon_shop_id() ? at_aws_get_amazon_shop_id() : '');
    $output['shop_info']['shopname'] = 'Amazon';
    $output['shop_info']['price_old'] = ($price_list ? $price_list : '');
    echo json_encode($output);
    exit();
}