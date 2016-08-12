<?php
use ApaiIO\ApaiIO;
use ApaiIO\Helper\DotDotText;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\Zend\Service\Amazon;

add_action('wp_ajax_amazon_api_search', 'at_aws_search');
add_action('wp_ajax_at_aws_search', 'at_aws_search');
add_action('wp_ajax_nopriv_at_aws_search', 'at_aws_search');
function at_aws_search() {
    $conf = new GenericConfiguration();

    try {
        $conf->setCountry(AWS_COUNTRY)
        ->setAccessKey(AWS_API_KEY)
        ->setSecretKey(AWS_API_SECRET_KEY)
        ->setAssociateTag(AWS_ASSOCIATE_TAG)
        ->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToResponseSet');
    } catch (\Exception $e) {
        echo $e->getMessage();
    }

    $apaiIO = new ApaiIO($conf);

    // vars
    $grabbedasins = (isset($_POST['grabbedasins']) ? $_POST['grabbedasins'] : '');    
    $keywords = (isset($_POST['q']) ? $_POST['q'] : '');
    $title = (isset($_POST['title']) ? $_POST['title'] : '');    
    $category = (isset($_POST['category']) ? $_POST['category'] : 'All');
    $page = (isset($_POST['page']) ? $_POST['page'] : '');
    $sort = (isset($_POST['sort']) ? $_POST['sort'] : '');
    $merchant = (isset($_POST['merchant']) ? $_POST['merchant'] : '');
    $min_price = (isset($_POST['min_price']) ? $_POST['min_price'] : '');
    $max_price = (isset($_POST['max_price']) ? $_POST['max_price'] : '');
    
    // overwrite keywords with asins
    if($grabbedasins) {
        $keywords = implode("|", explode("\n", $_POST['grabbedasins']));
    } 

    // start query
    $search = new Search();
    $search->setAvailability('Available');
    $search->setResponseGroup(array('Large', 'ItemAttributes', 'EditorialReview', 'OfferSummary', 'SalesRank'));
    $search->setPage($page);
    $search->setCategory($category);
    $search->setKeywords($keywords);
    $search->setTitle($title);
    $search->setSort($sort);
    $search->setMerchantId($merchant);
    if($min_price) {
        $search->setMinimumPrice($min_price);
    }
    if($max_price) {
        $search->setMaximumPrice($max_price);
    }
    

    /* @var $formattedResponse Amazon\ResultSet */
    $formattedResponse = $apaiIO->runOperation($search);

    if($formattedResponse) {
        /* @var $singleItem Amazon\Item */
        foreach ($formattedResponse as $singleItem) {
            try {
                $data = array(
                    'ean' => $singleItem->getEan(),
                    'asin' => $singleItem->ASIN,
                    'title' => $singleItem->Title,
                    'description' => DotDotText::truncate($singleItem->getItemDescription()),
                    'url' => $data['url'] = $singleItem->DetailPageURL,
                    'price' => $data['price'] = $singleItem->getUserFormattedPrice(),
                    'price_list' => ($singleItem->getFormattedListPrice() ? $singleItem->getFormattedListPrice() : 'kA'),
                    'price_amount' => $singleItem->getAmountForAvailability(),
                    'currency' => ($singleItem->getCurrencyCode() ? $singleItem->getCurrencyCode() : 'EUR'),
                    'category' => $singleItem->getBinding(),
                    'category_margin' => $singleItem->getMarginForBinding(),
                    'reviews_average' => $singleItem->getAverageRating(),
                    'reviews_total' => $singleItem->getTotalReviews(),
                    'external' => $singleItem->isExternalProduct(),
                    'exists' => 'false'
                );

                if ($singleItem->SmallImage != null && $singleItem->SmallImage->Url) {
                    $data['img'] = $singleItem->SmallImage->Url->getUri();
                }

                if ($check = at_get_product_id_by_metakey('product_shops_%_' . AWS_METAKEY_ID, $singleItem->ASIN, 'LIKE')) {
                    $data['exists'] = $check;
                }

                $output['items'][] = $data;
            } catch (\Exception $e) {
                //$output['items'][] = $e->getMessage();
                at_write_api_log('amazon', 'system', $e->getMessage());
                continue;
            }
        }
    }

    $output['rmessage']['totalpages'] = $formattedResponse->totalPages();
    $output['rmessage']['errormsg'] = $formattedResponse->getErrorMessage();

    echo json_encode($output);

    exit();
}