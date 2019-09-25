<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 180);

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Zend\Service\Amazon;

add_action('wp_ajax_amazon_api_test', 'at_aws_test');
add_action('wp_ajax_at_aws_test', 'at_aws_test');
function at_aws_test() {
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


    for ($i = 0; $i < 1000; $i++) {
        $lookup = new Lookup();
        $lookup->setItemId($asin);
        $lookup->setResponseGroup(array('ItemAttributes', 'OfferSummary', 'Offers', 'OfferFull', 'Variations', 'SalesRank', 'Images'));
        $lookup->setAvailability('Available');
        $formattedResponse = $apaiIO->runOperation($lookup);
        $item = $formattedResponse->getItem();

        if (!($item instanceof Amazon\Item)) {
            var_dump('ERROR');
//            var_dump($formattedResponse);
            var_dump($formattedResponse->getTextContent());

            if ($item instanceof Amazon\SingleResultSet) {
                throw new \Exception($item->getErrorMessage(), 505);
            }
            die;
        } else {

        }
    }

    echo '0K';

    var_dump('done');
    die;

    exit();
}
