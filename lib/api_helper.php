<?php
/**
 * Amazon API - Diverse Hilfsfunktionen
 *
 * @author		Christian Lang
 * @version		1.0
 * @updated     2016/08/01
 */

if ( ! function_exists( 'amazon_array_insert' ) ) {
    /**
     * amazon_array_insert
     * @deprecated since 1.1.8
     *
     */
    function amazon_array_insert(&$array, $position, $insert) {
        if (!is_array($array))
            return;

        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos = array_search($position, array_keys($array));
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }
}

if ( ! function_exists( 'at_aws_array_insert' ) ) {
    /**
     * at_aws_array_insert
     *
     * Array helper
     * @param   array $array
     * @param   int $position
     * @param   int $insert
     * @return  -
     */
    function at_aws_array_insert(&$array, $position, $insert) {
        if (!is_array($array))
            return;

        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos = array_search($position, array_keys($array));
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }
}

if( ! function_exists( 'get_amazon_shop_id' ) ) {
    /**
     * get_amazon_shop_id
     * @param   -
     * @return  int $shop_id
     *
     * @deprecated since 1.1.8
     */
    function get_amazon_shop_id() {
        global $wpdb;

        if ($shop_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'unique_identifier' AND meta_value = 'amazon' LIMIT 0,1")) {
            return $shop_id;
        }

        return false;
    }
}

if( ! function_exists( 'at_aws_get_amazon_shop_id' ) ) {
    /**
     * at_aws_get_amazon_shop_id
     * @param   -
     * @return  int $shop_id
     *
     */
    function at_aws_get_amazon_shop_id() {
        global $wpdb;

        if ($shop_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'unique_identifier' AND meta_value = 'amazon' LIMIT 0,1")) {
            return $shop_id;
        }

        return false;
    }
}

if ( ! function_exists( 'at_aws_search_index_list' ) ) {
    /**
     * at_aws_search_index_list
     * @param   boolean $html
     * @param   boolean $first_all
     * @param   array $current
     * @return  array/html
     *
     * Create Dropdown with AWS Search Indexes
     */
    function at_aws_search_index_list($html = true, $first_all = true, $current = array()) {
        $country = get_option('amazon_country');

       // Standard SearchIndexes, Amazon DE
        $items = array(
            'All' => 'Alle Kategorien',
            'UnboxVideo' => 'Amazon Instant Video',
            'Pantry' => 'Amazon Pantry',
            'MobileApps' => 'Apps & Spiele',
            'Automotive' => 'Auto & Motorrad',
            'Baby' => 'Baby',
            'Tools' => 'Baumarkt',
            'Beauty' => 'Beauty',
            'Apparel' => 'Bekleidung',
            'Lighting' => 'Beleuchtung',
            'Books' => 'Bücher',
            'OfficeProducts' => 'Bürobedarf & Schreibwaren',
            'PCHardware' => 'Computer & Zubehör',
            'DVD' => 'DVD & Blu-ray',
            'HealthPersonalCare' => 'Drogerie & Körperpflege',
            'Appliances' => 'Elektro-Großgeräte',
            'Electronics' => 'Elektronik & Foto',
            'ForeignBooks' => 'Fremdsprachige Bücher',
            'VideoGames' => 'Games',
            'HomeGarden' => 'Garten',
            'GiftCards' => 'Geschenkgutscheine',
            'PetSupplies' => 'Haustier',
            'Photo' => 'Kamera & Foto',
            'KindleStore' => 'Kindle-Shop',
            'Classical' => 'Klassik',
            'Luggage' => 'Koffer, Rucksäcke & Taschen',
            'Kitchen' => 'Küche & Haushalt',
            'Grocery' => 'Lebensmittel & Getränke',
            'Music' => 'Musik-CDs & Vinyl',
            'MP3Downloads' => 'Musik-Downloads',
            'MusicalInstruments' => 'Musikinstrumente & DJ-Equipment',
            'Jewelry' => 'Schmuck',
            'Shoes' => 'Schuhe & Handtaschen',
            'Software' => 'Software',
            'Toys' => 'Spielzeug',
            'SportingGoods' => 'Sport & Freizeit',
            'Industrial' => 'Technik & Wissenschaft',
            'Watches' => 'Uhren',
            'Magazines' => 'Zeitschriften'
        );

        if ($country == 'com') { // Amazon US
            $items = array(
                'All' => 'All Departments',
                'UnboxVideo' => 'Amazon Instant Video',
                'Appliances' => 'Appliances',
                'MobileApps' => 'Apps & Games',
                'ArtsAndCrafts' => 'Arts, Crafts & Sewing',
                'Automotive' => 'Automotive',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Music' => 'CDs & Vinyl',
                'Wireless' => 'Cell Phones & Accessories',
                'Fashion' => 'Clothing, Shoes & Jewelry',
                'FashionBaby' => 'Clothing, Shoes & Jewelry - Baby',
                'FashionBoys' => 'Clothing, Shoes & Jewelry - Boys',
                'FashionGirls' => 'Clothing, Shoes & Jewelry - Girls',
                'FashionMen' => 'Clothing, Shoes & Jewelry - Men',
                'FashionWomen' => 'Clothing, Shoes & Jewelry - Women',
                'Collectibles' => 'Collectibles & Fine Arts',
                'PCHardware' => 'Computers',
                'MP3Downloads' => 'Digital Music',
                'Electronics' => 'Electronics',
                'GiftCards' => 'Gift Cards',
                'Grocery' => 'Grocery & Gourmet Food',
                'HealthPersonalCare' => 'Health & Personal Care',
                'HomeGarden' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'KindleStore' => 'Kindle Store',
                'Luggage' => 'Luggage & Travel Gear',
                'Magazines' => 'Magazine Subscriptions',
                'Movies' => 'Movies & TV',
                'MusicalInstruments' => 'Musical Instruments',
                'OfficeProducts' => 'Office Products',
                'LawnAndGarden' => 'Patio, Lawn & Garden',
                'PetSupplies' => 'Pet Supplies',
                'Pantry' => 'Prime Pantry',
                'Software' => 'Software',
                'SportingGoods' => 'Sports & Outdoors',
                'Tools' => 'Tools & Home Improvement',
                'Toys' => 'Toys & Games',
                'VideoGames' => 'Video Games',
                'Wine' => 'Wine'
            );
        }

        if($country == 'com.br') { // Amazon Brazil
            $items = array(
                'All' => 'Todos os departmentos',
                'MobileApps' => 'Apps e Jogos',
                'Books' => 'Livros',
                'KindleStore' => 'Loja Kindle'
            );
        }

        if($country == 'ca') { // Amazon Canada
            $items = array(
                'All' => 'All Departments',
                'MobileApps' => 'Apps & Games',
                'Automotive' => 'Automotive',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Apparel' => 'Clothing & Accessories',
                'Electronics' => 'Electronics',
                'GiftCards' => 'Gift Cards',
                'Grocery' => 'Grocery & Gourmet Food',
                'HealthPersonalCare' => 'Health & Personal Care',
                'Kitchen' => 'Home & Kitchen',
                'Jewelry' => 'Jewelry',
                'KindleStore' => 'Kindle Store',
                'Luggage' => 'Luggage & Bags',
                'DVD' => 'Movies & TV',
                'Music' => 'Music',
                'MusicalInstruments' => 'Musical Instruments, Stage & Studio',
                'OfficeProducts' => 'Office Products',
                'LawnAndGarden' => 'Patio, Lawn & Garden',
                'PetSupplies' => 'Pet Supplies',
                'Shoes' => 'Shoes & Handbags',
                'Software' => 'Software',
                'SportingGoods' => 'Sports & Outdoors',
                'Tools' => 'Tools & Home Improvement',
                'Toys' => 'Toys & Games',
                'VideoGames' => 'Video Games',
                'Watches' => 'Watches'
            );
        }

        if($country == 'cn') { // Amazon China
            $items = array(
                'All' => '全部分类',
                'Appliances' => '大家电',
                'KindleStore' => 'Kindle商店',
                'GiftCards' => 'GiftCards (TBD)',
                'Kitchen' => 'Kitchen (TBD)',
                'MobileApps' => 'MobileApps (TBD)',
                'PCHardware' => 'PCHardware (TBD)',
                'HealthPersonalCare' => '个护健康',
                'MusicalInstruments' => '乐器',
                'OfficeProducts' => '办公用品',
                'Books' => '图书',
                'PetSupplies' => '宠物用品',
                'HomeImprovement' => '家居装修',
                'Home' => '家用',
                'Photo' => '摄影/摄像',
                'Apparel' => '服饰箱包',
                'Baby' => '母婴用品',
                'Automotive' => '汽车用品',
                'VideoGames' => '游戏/娱乐',
                'Toys' => '玩具',
                'Jewelry' => '珠宝首饰',
                'Electronics' => '电子',
                'Beauty' => '美容化妆',
                'Software' => '软件',
                'SportingGoods' => '运动户外休闲',
                'Watches' => '钟表',
                'Shoes' => '鞋靴',
                'Music' => '音乐',
                'Video' => '音像',
                'Grocery' => '食品'
            );
        }

        if($country == 'es') { // Amazon Espana
            $items = array(
                'All' => 'Todos los departamentos',
                'MobileApps' => 'Apps y Juegos',
                'Baby' => 'Bebé',
                'Beauty' => 'Belleza',
                'Tools' => 'Bricolaje y herramientas',
                'GiftCards' => 'Cheques regalo',
                'Automotive' => 'Coche y moto',
                'SportingGoods' => 'Deportes y aire libre',
                'Electronics' => 'Electrónica',
                'Luggage' => 'Equipaje',
                'Kitchen' => 'Hogar',
                'Lighting' => 'Iluminación',
                'Industrial' => 'Industria y ciencia',
                'PCHardware' => 'Informática',
                'MusicalInstruments' => 'Instrumentos musicales',
                'LawnAndGarden' => 'Jardín',
                'Jewelry' => 'Joyería',
                'Toys' => 'Juguetes y juegos',
                'Books' => 'Libros',
                'ForeignBooks' => 'Libros en idiomas extranjeros',
                'MP3Downloads' => 'Música Digital',
                'Music' => 'Música: CDs y vinilos',
                'OfficeProducts' => 'Oficina y papelería',
                'DVD' => 'Películas y TV',
                'Watches' => 'Relojes',
                'Apparel' => 'Ropa y accesorios',
                'HealthPersonalCare' => 'Salud y cuidado personal',
                'Software' => 'Software',
                'Grocery' => 'Supermercado',
                'KindleStore' => 'Tienda Kindle',
                'VideoGames' => 'Videojuegos',
                'Shoes' => 'Zapatos y complementos'
            );
        }

        if($country == 'fr') { // Amazon France
            $items = array(
                'All' => 'Toutes nos boutiques',
                'PetSupplies' => 'Animalerie',
                'MobileApps' => 'Applis & Jeux',
                'Luggage' => 'Bagages',
                'Beauty' => 'Beauté et Parfum',
                'Jewelry' => 'Bijoux',
                'KindleStore' => 'Boutique Kindle',
                'GiftCards' => 'Boutique chèques-cadeaux',
                'HomeImprovement' => 'Bricolage',
                'Baby' => 'Bébés & Puériculture',
                'Shoes' => 'Chaussures et Sacs',
                'Kitchen' => 'Cuisine & Maison',
                'DVD' => 'DVD & Blu-ray',
                'Grocery' => 'Epicerie',
                'OfficeProducts' => 'Fournitures de bureau',
                'Appliances' => 'Gros électroménager',
                'Electronics' => 'High-Tech',
                'HealthPersonalCare' => 'Hygiène et Santé',
                'PCHardware' => 'Informatique',
                'MusicalInstruments' => 'Instruments de musique & Sono',
                'LawnAndGarden' => 'Jardin',
                'Toys' => 'Jeux et Jouets',
                'VideoGames' => 'Jeux vidéo',
                'ForeignBooks' => 'Livres anglais et étrangers',
                'Books' => 'Livres en français',
                'Software' => 'Logiciels',
                'Lighting' => 'Luminaires et Eclairage',
                'Watches' => 'Montres',
                'Music' => 'Musique : CD & Vinyles',
                'Classical' => 'Musique classique',
                'Industrial' => 'Secteur industriel & scientifique',
                'SportingGoods' => 'Sports et Loisirs',
                'MP3Downloads' => 'Téléchargement de musique',
                'Apparel' => 'Vêtements et accessoires'
            );
        }

        if($country == 'in') { // Amazon India
            $items = array(
                'All' => 'All Departments',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Automotive' => 'Car & Motorbike',
                'Apparel' => 'Clothing & Accessories',
                'PCHardware' => 'Computers & Accessories',
                'Electronics' => 'Electronics',
                'GiftCards' => 'Gift Cards',
                'Grocery' => 'Gourmet & Specialty Foods',
                'HealthPersonalCare' => 'Health & Personal Care',
                'HomeGarden' => 'Home & Kitchen',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Jewellery',
                'KindleStore' => 'Kindle Store',
                'Luggage' => 'Luggage & Bags',
                'DVD' => 'Movies & TV Shows',
                'Music' => 'Music',
                'MusicalInstruments' => 'Musical Instruments',
                'OfficeProducts' => 'Office Products',
                'PetSupplies' => 'Pet Supplies',
                'Shoes' => 'Shoes & Handbags',
                'Software' => 'Software',
                'SportingGoods' => 'Sports, Fitness & Outdoors',
                'Toys' => 'Toys & Games',
                'VideoGames' => 'Video Games',
                'Watches' => 'Watches'
            );
        }

        if($country == 'it') { // Amazon Italia
            $items = array(
                'All' => 'Tutte le categorie',
                'Apparel' => 'Abbigliamento',
                'Grocery' => 'Alimentari e cura della casa',
                'MobileApps' => 'App e Giochi',
                'Automotive' => 'Auto e Moto',
                'Beauty' => 'Bellezza',
                'GiftCards' => 'Buoni Regalo',
                'Music' => 'CD e Vinili',
                'OfficeProducts' => 'Cancelleria e prodotti per ufficio',
                'Kitchen' => 'Casa e cucina',
                'HealthPersonalCare' => 'Cura della Persona',
                'Electronics' => 'Elettronica',
                'Tools' => 'Fai da te',
                'DVD' => 'Film e TV',
                'Garden' => 'Giardino e giardinaggio',
                'Toys' => 'Giochi e giocattoli',
                'Jewelry' => 'Gioielli',
                'Lighting' => 'Illuminazione',
                'Industrial' => 'Industria e Scienza',
                'PCHardware' => 'Informatica',
                'KindleStore' => 'Kindle Store',
                'Books' => 'Libri',
                'ForeignBooks' => 'Libri in altre lingue',
                'MP3Downloads' => 'Musica Digitale',
                'Watches' => 'Orologi',
                'Baby' => 'Prima infanzia',
                'Shoes' => 'Scarpe e borse',
                'Software' => 'Software',
                'SportingGoods' => 'Sport e tempo libero',
                'MusicalInstruments' => 'Strumenti musicali e DJ',
                'Luggage' => 'Valigeria',
                'VideoGames' => 'Videogiochi'
            );
        }

        if($country == 'co.jp') { // Amazon Japan
            $items = array(
                'All' => 'すべてのカテゴリー',
                'VideoDownload' => 'Amazon インスタント・ビデオ',
                'MobileApps' => 'Android アプリ',
                'HomeImprovement' => 'DIY・工具',
                'Video' => 'DVD',
                'KindleStore' => 'Kindleストア',
                'Software' => 'PCソフト',
                'Industrial' => 'Industrial (TBD)',
                'GiftCards' => 'GiftCards (TBD)',
                'Kitchen' => 'Kitchen (TBD)',
                'CreditCards' => 'CreditCarts (TBD)',
                'VideoGames' => 'TVゲーム',
                'Toys' => 'おもちゃ',
                'Automotive' => 'カー・バイク用品',
                'Classical' => 'クラシック',
                'Beauty' => 'コスメ',
                'Shoes' => 'シューズ＆バッグ',
                'Jewelry' => 'ジュエリー',
                'SportingGoods' => 'スポーツ&アウトドア',
                'MP3Downloads' => 'デジタルミュージック',
                'PCHardware' => 'パソコン・周辺機器',
                'HealthPersonalCare' => 'ヘルス&ビューティー',
                'Baby' => 'ベビー&マタニティ',
                'PetSupplies' => 'ペット用品',
                'Hobbies' => 'ホビー',
                'Music' => 'ミュージック',
                'Appliances' => '大型家電',
                'Electronics' => '家電&カメラ',
                'OfficeProducts' => '文房具・オフィス用品',
                'Apparel' => '服＆ファッション小物',
                'Books' => '本',
                'MusicalInstruments' => '楽器',
                'ForeignBooks' => '洋書',
                'Watches' => '腕時計',
                'Grocery' => '食品・飲料・お酒',
            );
        }

        if($country == 'com.mx') { // Amazon Mexikon
            $items = array(
                'All' => 'Todos los departamentos',
                'Baby' => 'Bebé',
                'SportingGoods' => 'Deportes y Aire Libre',
                'Electronics' => 'Electrónicos',
                'HomeImprovement' => 'Herramientas y Mejoras del Hogar',
                'Kitchen' => 'Hogar y Cocina',
                'Books' => 'Libros',
                'Music' => 'Música',
                'DVD' => 'Películas y Series de TV',
                'Watches' => 'Relojes',
                'HealthPersonalCare' => 'Salud, Belleza y Cuidado Personal',
                'Software' => 'Software',
                'KindleStore' => 'Tienda Kindle',
                'VideoGames' => 'Videojuegos'
            );
        }

        if($country == 'co.uk') { // Amazon UK
            $items = array(
                'All' => 'All Departments',
                'UnboxVideo' => 'Amazon Instant Video',
                'Pantry' => 'Amazon Pantry',
                'MobileApps' => 'Apps & Games',
                'Baby' => 'Baby',
                'Beauty' => 'Beauty',
                'Books' => 'Books',
                'Music' => 'CDs & Vinyl',
                'Automotive' => 'Car & Motorbike',
                'Classical' => 'Classical',
                'Apparel' => 'Clothing',
                'PCHardware' => 'Computers',
                'Tools' => 'DIY & Tools',
                'DVD' => 'DVD & Blu-ray',
                'MP3Downloads' => 'Digital Music',
                'Electronics' => 'Electronics & Photo',
                'HomeGarden' => 'Garden & Outdoors',
                'GiftCards' => 'Gift Cards',
                'Grocery' => 'Grocery',
                'HealthPersonalCare' => 'Health & Personal Care',
                'Industrial' => 'Industrial & Scientific',
                'Jewelry' => 'Jewellery',
                'KindleStore' => 'Kindle Store',
                'Kitchen' => 'Kitchen & Home',
                'Appliances' => 'Large Appliances',
                'Lighting' => 'Lighting',
                'Luggage' => 'Luggage',
                'MusicalInstruments' => 'Musical Instruments & DJ',
                'VideoGames' => 'PC & Video Games',
                'PetSupplies' => 'Pet Supplies',
                'Shoes' => 'Shoes & Bags',
                'Software' => 'Software',
                'SportingGoods' => 'Sports & Outdoors',
                'OfficeProducts' => 'Stationery & Office Supplies',
                'Toys' => 'Toys & Games',
                'VHS' => 'VHS',
                'Watches' => 'Watches',
            );
        }

        if($first_all == false && $items) {
            unset($items[0]);
        }

        $items = apply_filters('at_aws_search_indexes', $items, $country);

        $output = '';

        if ($html == true) {
            $output .= '<select name="category" id="category" class="form-control">';

            foreach ($items as $k => $v) {
                $selected = ($k == $current ? 'selected' : '');
                $output .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
            }

            $output .= '</select>';
        } else {
            $output = $items;
        }

        return $output;
    }
}

if ( ! function_exists( 'at_aws_add_amazon_as_portal' ) ) {
    /**
     * at_aws_add_amazon_as_portal
     *
     * Add Amazon to Product Portal Dropdown
     */
    add_filter('at_add_product_portal', 'at_aws_add_amazon_as_portal', 10, 2);
    function at_aws_add_amazon_as_portal($choices) {
        $choices['amazon'] = 'Amazon';
        return $choices;
    }
}

if ( ! function_exists( 'at_aws_overwrite_product_button_short_text' ) ) {
    /**
     * at_aws_overwrite_product_button_short_text
     *
     * Overwrite Product Button Text (short)
     */
    add_filter('at_product_api_button_short_text', 'at_aws_overwrite_product_button_short_text', 10, 5);
    function at_aws_overwrite_product_button_short_text($var = '', $product_portal = '', $product_shop = '', $pos = '', $short = false) {
        global $post;

        if ('amazon' == $product_portal && 'buy' == $pos) {
            $var = (get_option('amazon_buy_short_button') ? get_option('amazon_buy_short_button') : __('Kaufen', 'affiliatetheme-amazon'));
            $not_avail_button = (get_option('amazon_not_avail_button') ? get_option('amazon_not_avail_button') : __('Nicht Verfügbar', 'affiliatetheme-amazon'));

            if ('1' == get_post_meta($post->ID, 'product_not_avail', true)) {
                $var = $not_avail_button;
            }
        }

        return $var;
    }
}

if ( ! function_exists( 'at_aws_overwrite_product_button_text' ) ) {
    /**
     * at_aws_overwrite_product_button_text
     *
     * Overwrite Product Button Text (short)
     */
    add_filter('at_product_api_button_text', 'at_aws_overwrite_product_button_text', 10, 5);
    function at_aws_overwrite_product_button_text($var = '', $product_portal = '', $product_shop = '', $pos = '', $short = false) {
        global $post;

        if ('amazon' == $product_portal && 'buy' == $pos) {
            $var = (get_option('amazon_buy_button') ? get_option('amazon_buy_button') : __('Jetzt bei Amazon kaufen', 'affiliatetheme-amazon'));
            $not_avail_button = (get_option('amazon_not_avail_button') ? get_option('amazon_not_avail_button') : __('Nicht Verfügbar', 'affiliatetheme-amazon'));

            if ('1' == get_post_meta($post->ID, 'product_not_avail', true)) {
                $var = $not_avail_button;
            }
        }

        return $var;
    }
}

if ( ! function_exists( 'at_aws_add_field_portal_id' ) ) {
    /**
     * at_aws_add_field_portal_id
     *
     * Add Amazon ASIN Field to Products
     */
    add_filter('at_add_product_fields', 'at_aws_add_field_portal_id', 10, 2);
    function at_aws_add_field_portal_id($fields) {
        $new_field[] = array(
            'key' => 'field_553b75842c246bc',
            'label' => 'Amazon ASIN',
            'name' => 'amazon_asin',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array(
                array(
                    array(
                        'field' => 'field_553b83de246bb',
                        'operator' => '==',
                        'value' => 'amazon',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => 25,
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        );

        at_aws_array_insert($fields['fields'][4]['sub_fields'], 7, $new_field);
        return $fields;
    }
}

if ( ! function_exists( 'at_aws_add_product_tabs_nav' ) ) {
    /**
     * at_aws_add_product_tabs_nav
     *
     * Add Amazon Reviews Tab to Products
     */
    add_filter('at_product_tabs_nav', 'at_aws_add_product_tabs_nav', 10, 2);
    function at_aws_add_product_tabs_nav($content, $post_id) {
        if ('1' != get_option('amazon_show_reviews')) {
            return false;
        }

        $partner_tag = get_option('amazon_partner_id');
        $product_shops = get_field('product_shops', $post_id);
        $shop_id = getRepeaterRowID($product_shops, 'portal', 'amazon', false);
        if($shop_id !== NULL) {
            $asin = $product_shops[$shop_id]['amazon_asin'];
            $link = $product_shops[$shop_id]['link'];
            $url = 'https://www.amazon.de/product-reviews/' . $asin . '/?tag=' . $partner_tag;

            // check current amazon country
            if ($link) {
                preg_match_all("/\\.[a-z]{2,3}(\\.[a-z]{2,3})?/m", $link, $amazon_tld);
                if ($amazon_tld) {
                    if ($tld = $amazon_tld[0][1]) {
                        $url = 'https://www.amazon' . $tld . '/product-reviews/' . $asin . '/?tag=' . $partner_tag;
                    }
                }
            }

            if (!$asin) {
                return false;
            }

            $title = apply_filters('at_amazon_reviews_title', __('Kundenrezensionen', 'affiliatetheme-amazon'));

            $content .= '<li><a href="' . $url . '" rel="nofollow" target="_blank">' . $title . '</a></li>';
        }

        echo $content;
    }
}


if ( ! function_exists( 'at_aws_set_product_notification' ) ) {
    /**
     * at_aws_set_product_notification
     *
     * Set Product on Email Notification list
     */
    function at_aws_set_product_notification($post_id) {
        $products = (get_option('at_amazon_notification_items') ? get_option('at_amazon_notification_items') : array());

        if (!is_array($products))
            return;

        $products[] = $post_id;
        $products = array_unique($products);

        update_option('at_amazon_notification_items', $products);
    }
}

if ( ! function_exists( 'at_aws_remove_product_notification' ) ) {
    /**
     * at_aws_remove_product_notification
     *
     * Remove Product from Email Notification list
     */
    function at_aws_remove_product_notification($post_id) {
        $products = (get_option('at_amazon_notification_items') ? get_option('at_amazon_notification_items') : array());

        if (!is_array($products))
            return;

        if (($key = array_search($post_id, $products)) !== false) {
            unset($products[$key]);
        }

        update_option('at_amazon_notification_items', $products);
    }
}

if ( ! function_exists( 'at_aws_send_notification_mail' ) ) {
    /**
     * at_aws_send_notification_mail
     *
     * Send Amazon Product Notification Email
     */
    if (get_option('amazon_notification') == "email" || get_option('amazon_notification') == "email_draft") {
        if (!wp_next_scheduled('affiliatetheme_send_amazon_notification_mail')) {
            wp_schedule_event(time(), 'daily', 'affiliatetheme_send_amazon_notification_mail');
        }
    } else {
        wp_clear_scheduled_hook('affiliatetheme_send_amazon_notification_mail');
    }
    add_action('wp_ajax_at_send_amazon_notification_mail', 'at_aws_send_notification_mail');
    add_action('affiliatetheme_send_amazon_notification_mail', 'at_aws_send_notification_mail');
    function at_aws_send_notification_mail() {
        $products = (get_option('at_amazon_notification_items') ? get_option('at_amazon_notification_items') : array());
        $to = get_option('admin_email');
        $sitename = get_bloginfo('name');

        if (!is_array($products) || empty($products))
            return;

        if ($products) {
            $product_table = '';
            foreach ($products as $item) {
                if (!get_post_status($item) || get_post_status($item) == 'trash') {
                    at_aws_remove_product_notification($item);
                    continue;
                }

                switch (get_post_status($item)) {
                    case 'publish':
                        $status = __('Online', 'affiliatetheme-amazon');
                        break;

                    default:
                        $status = __('Entwurf', 'affiliatetheme-amazon');
                }

                $product_table .= '
                    <tr>
                        <td style="padding: 5px; border-top: 1px solid #eee;min-width:30px;">' . $item . '</td>
                        <td style="padding: 5px; border-top: 1px solid #eee;"><a href="' . get_permalink($item) . '" target="_blank">' . get_the_title($item) . ' (' . $status . ')</a></td>
                        <td style="padding: 5px; border-top: 1px solid #eee;">' . get_product_last_update($item) . '</td>
                    </tr>
                ';
            }

            if (!$product_table)
                exit;

            $body = file_get_contents(AWS_PATH . '/view/email.html');
            $body = str_replace('%%BLOGNAME%%', $sitename, $body);
            $body = str_replace('%%BLOGURL%%', '<a href="' . home_url() . '" target="_blank">' . home_url('') . '</a>', $body);
            $body = str_replace('%%PRODUCTS%%', $product_table, $body);
            $body = str_replace('%%AMAZON_API_SETTINGS_URL%%', admin_url("admin.php?page=endcore_api_amazon"), $body);

            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $sitename . ': Nicht verfügbare Produkte', $body, $headers);
        }
    }
}

if( ! function_exists( 'at_aws_search_allowed_sort' )) {
    /**
     * at_aws_search_allowed_sort
     * 
     * return allowed search sort
     * @param   array $array
     */
    function at_aws_search_allowed_sort($array = false) {
        $not_allowed = array('All','UnboxVideo');

        if($array) {
            return $not_allowed;
        }

        return implode(',', $not_allowed);
    }
}

if( ! function_exists( 'at_aws_search_check_allowed_sort' )) {
    /**
     * at_aws_search_check_allowed_sort
     * 
     * check allowed sort
     * @param   string $SearchIndex
     */
    function at_aws_search_check_allowed_sort($SearchIndex) {
        $not_allowed = at_aws_search_allowed_sort(true);

        if(in_array($SearchIndex, $not_allowed)) {
            return false;
        }

        return true;
    }
}

if( ! function_exists( 'at_aws_search_allowed_param' )) {
    /**
     * at_aws_search_allowed_param
     * 
     * return allowed param
     * @param   string $param
     * @param   boolean $array
     */
    function at_aws_search_allowed_param($param, $array = false) {
        $not_allowed = array();

        if($param == 'MinimumPrice' || $param == 'MaximumPrice') {
            $not_allowed = array('All','Jewelry','Toys','Watches');
        }

        if($array) {
            return $not_allowed;
        }

        return implode(',', $not_allowed);
    }
}

if( ! function_exists( 'at_aws_search_check_allowed_param' )) {
    /**
     * at_aws_search_check_allowed_param
     * 
     * check allowed param
     * @param   string $param
     * @param   string $SearchIndex
     */
    function at_aws_search_check_allowed_param($param, $SearchIndex) {
        $not_allowed = at_aws_search_allowed_param($param, true);

        if(in_array($SearchIndex, $not_allowed)) {
            return false;
        }

        return true;
    }
}

if ( ! function_exists('at_amazon_notices') ) {
    /**
     * at_amazon_notices function.
     *
     */
    add_action('admin_notices', 'at_amazon_notices');
    function at_amazon_notices() {
        if ((isset($_GET['page']) && $_GET['page'] == 'endcore_api_amazon')) {
            // check php version
            if(version_compare(PHP_VERSION, '5.3.0', '<')) {
                ?>
                <div class="notice notice-error">
                    <p><?php printf(__('Achtung: Um dieses Plugin zu verwenden benötigst du mindestens PHP Version 5.3.x. Derzeit verwendest du Version %s.', 'affiliatetheme-amazon'), PHP_VERSION); ?></p>
                </div>
                <?php
            }

            // check curl
            if(extension_loaded('curl') != function_exists('curl_version')) {
                ?>
                <div class="notice notice-error">
                    <p><?php _e('Um dieses Plugin zu verwenden benötigst du cURL. <a href="http://php.net/manual/de/book.curl.php" taget="_blank">Hier</a> findest du mehr Informationen darüber. Kontaktiere im Zweifel deinen Systemadministrator.', 'affiliatetheme-amazon'); ?></p>
                </div>
                <?php
            }

            // check allow_url_fopen
            if(ini_get('allow_url_fopen') == false) {
                ?>
                <div class="notice notice-error">
                    <p><?php _e('Achtung: Du hast allow_url_fopen deaktiviert. Du benötigst diese Funktionen um das Rating von Amazon zu beziehen.', 'affiliatetheme-amazon'); ?></p>
                </div>
                <?php
            }
        } 
    }
}

if ( ! function_exists('at_fire_filter') ) {
    /**
     * at_fire_filter function.
     *
     */
    add_action('init', 'at_fire_filter');
    function at_fire_filter() {
        // replace product thumbnails
        if(get_option('amazon_error_handling_replace_thumbnails') == '1') {
            add_filter('at_aws_product_thumbnail_regenerate', '__return_true');
        }
    }
}

if ( ! function_exists('at_amazon_product_skip_interval') ) {
    /**
     * at_amazon_product_update_interval function.
     *
     */
    add_action('init', 'at_amazon_product_skip_interval');
    function at_amazon_product_skip_interval() {
        $interval = 3600;

        $product_skip_interval = get_option('amazon_product_skip_interval');

        if($product_skip_interval != 3600 && $product_skip_interval != '') {
            $interval = $product_skip_interval;
        }

        return intval(apply_filters('at_amazon_product_skip_interval', $interval));
    }
}

if ( ! function_exists('at_amazon_start_cronjob') ) {
    /**
     * at_amazon_start_cronjob function.
     *
     */
    add_action('init', 'at_amazon_start_cronjob');
    add_action('init', 'at_amazon_start_feed_cronjob');
    function at_amazon_start_cronjob() {
        $public_key = get_option('amazon_public_key');
        $secret_key = get_option('amazon_secret_key');
        $amazon_update_run_cronjob = get_option('amazon_update_run_cronjob');

        $recurrence = apply_filters('at_amazon_cronjob_recurrence', 'hourly');
        $hook = 'affiliatetheme_amazon_api_update';
        $args = array('hash' => AWS_CRON_HASH);

        if($amazon_update_run_cronjob == 'no') {
            wp_clear_scheduled_hook($hook, $args);
            return false;
        }

        if(!$public_key || !$secret_key) {
            wp_clear_scheduled_hook($hook, $args);
            return false;
        }

        if(wp_next_scheduled($hook, $args)) {
            return false;
        }

        wp_schedule_event(time(), $recurrence, $hook, $args);
    }

    function at_amazon_start_feed_cronjob() {
        $public_key = get_option('amazon_public_key');
        $secret_key = get_option('amazon_secret_key');

        $recurrence = apply_filters('at_amazon_cronjob_recurrence', '5min');
        $hook = 'affiliatetheme_amazon_api_update_feeds';
        $args = array('hash' => AWS_CRON_HASH);

        if(!$public_key || !$secret_key) {
            wp_clear_scheduled_hook($hook, $args);
            return false;
        }

        if(wp_next_scheduled($hook, $args)) {
            return false;
        }

        wp_schedule_event(time(), $recurrence, $hook, $args);
    }
}

add_filter('cron_schedules','at_amazon_cron_schedules');
function at_amazon_cron_schedules($schedules){
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
    return $schedules;
}

if ( ! function_exists('at_amazon_cronjob_next_run') ) {
    /**
     * at_amazon_cronjob_next_run function.
     *
     */
    function at_amazon_cronjob_next_run() {
        $args = array('hash' => AWS_CRON_HASH);

        $timestamp = wp_next_scheduled('affiliatetheme_amazon_api_update', $args);

        if(!$timestamp)
            return '(n/a)';

        $date = date_i18n(get_option( 'date_format' ), $timestamp);
        $time = date_i18n(get_option( 'time_format' ), $timestamp);

        return $date . ' - ' . $time;
    }
}

if ( ! function_exists('at_amazon_rating_hint') ) {
    /**
     * at_amazon_rating_hint function.
     *
     */
    add_action('admin_notices', 'at_amazon_rating_hint');
    function at_amazon_rating_hint() {
        $screen = get_current_screen();
        if($screen->id != 'import_page_endcore_api_amazon') {
            return;
        }

        $option = get_option('rating-removed-hint');
        if($option == 'dismissed') {
            return;
        }
        ?>
        <div class="notice notice-info is-dismissible" data-action="force-dismiss" data-name="rating-removed-hint">
            <p><span class="dashicons dashicons-megaphone"></span> &nbsp; <?php printf(__('Die Amazon Bewertungen werden nicht mehr über die API übertragen. Erfahre <a href="%s" target="_blank">hier</a> mehr.', 'affiliatetheme-amazon'), 'https://affiliatetheme.io/forum/thema/die-amazon-bewertungen-sind-nicht-weiter-bestandteil-unserer-schnittstelle/'); ?></p>
        </div>
        <?php
    }
}

if ( ! function_exists('at_amazon_set_option') ) {
    /**
     * at_amazon_set_option function.
     *
     */
    add_action('wp_ajax_at_amazon_set_option', 'at_amazon_set_option');
    function at_amazon_set_option() {
        $option = $_POST['option'];
        $value = $_POST['value'];

        if ($option && $value) {
            update_option($option, $value);
        }

        exit;
    }
}

if ( ! function_exists('at_amazon_get_current_lang') ) {
    /**
     * at_amazon_get_current_lang function.
     *
     */
    function at_amazon_get_current_lang() {
        $locale = get_locale();

        if (strpos($locale, 'de_') !== false)
            return 'de';

        return 'en';
    }
}

if ( ! function_exists('at_amazon_get_forum_url') ) {
    /**
     * at_amazon_get_forum_url function.
     *
     */
    function at_amazon_get_forum_url() {
        $locale = at_amazon_get_current_lang();

        if($locale == 'de') {
            return 'https://affiliatetheme.io/forum/foren/support/schnittstellen/';
        }

        return 'https://affiliatetheme.io/forum/foren/support-english/apis/';
    }
}

/*
 * Feed: Read
 * */
function at_amazon_feed_read() {
    global $wpdb;

    $feed = $wpdb->get_results (
        "
        SELECT * FROM " . AWS_FEED_TABLE . " ORDER BY last_update ASC
        "
    );

    if($feed)
        return $feed;

    return;
}

/*
 * Feed: Add Keyword
 * */
function at_amazon_feed_write($keyword, $category) {
    if(!$keyword || !$category)
        return;

    global $wpdb;

    $status = $wpdb->insert(
        AWS_FEED_TABLE,
        array(
            'keyword'       => $keyword,
            'category'      => $category,
            'last_message'  => date('d.m.Y G:i:s'),
            'post_status'   => 'publish',
        ),
        array(
            '%s',
            '%s',
            '%s'
        )
    );

    return $status;
}

function at_amazon_feed_set_update($id) {
    global $wpdb;

    $status = $wpdb->update(
        AWS_FEED_TABLE,
        array(
            'last_update'       => date("Y-m-d H:m:s", time()),
        ),
        array(
            'id' => $id
        )
    );

    return $status;
}

add_action('wp_ajax_at_amazon_feed_write_ajax', 'at_amazon_feed_write_ajax');
function at_amazon_feed_write_ajax() {
    $keyword = (isset($_POST['keyword']) ? $_POST['keyword'] : '');
    $category = (isset($_POST['category']) ? $_POST['category'] : '');

    if(!$keyword || !$category) {
        echo json_encode(array('status' => 'error'));
        exit;
    }

    at_amazon_feed_write($keyword, $category);

    echo json_encode(array('status' => 'ok'));
    exit;
}
/*
 *
 * Feed: Status Label
 */
function at_amazon_feed_status_label($status) {
    switch($status) {
        case '1':
            return __('aktiv', 'affiliatetheme-amazon');

        case '0':
            return __('inaktiv', 'affiliatetheme-amazon');
    }

    return;
}

/*
 * Feed: Change Status
 */
function at_amazon_feed_change_status($id, $status) {
    if($id == 'undefined')
        return;

    global $wpdb;

    $status = $wpdb->update(
        AWS_FEED_TABLE,
        array(
            'status'    => $status,
        ),
        array( 'id' => $id ),
        array(
            '%d'	// value2
        ),
        array( '%d' )
    );

    return $status;
}

add_action('wp_ajax_at_amazon_feed_change_status_ajax', 'at_amazon_feed_change_status_ajax');
function at_amazon_feed_change_status_ajax() {
    $id = (isset($_POST['id']) ? $_POST['id'] : '');
    $status = (isset($_POST['status']) ? $_POST['status'] : '');

    if(at_amazon_feed_change_status($id, $status)) {
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    echo json_encode(array('status' => 'error'));
    exit;
}

/*
 * Feed: Change Settings
 */
function at_amazon_feed_change_settings($id, $data) {
    if($id == 'undefined')
        return;

    global $wpdb;

    $status = $wpdb->update(
        AWS_FEED_TABLE,
        $data,
        array( 'id' => $id )
    );
    
    return $status;
}

add_action('wp_ajax_at_amazon_feed_change_settings_ajax', 'at_amazon_feed_change_settings_ajax');
function at_amazon_feed_change_settings_ajax() {
    $id = (isset($_POST['id']) ? $_POST['id'] : '');

    $data = $_POST;

    unset($data['id']);
    unset($data['action']);

    if(isset($data['tax'])) {
        $tax_data = serialize($data['tax']);
        $data['tax'] = $tax_data;
    }

    if(at_amazon_feed_change_settings($id, $data)) {
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    echo json_encode(array('status' => 'error'));
    exit;
}
/*
 * Feed: Delete Keyword
 */
function at_amazon_feed_delete($id) {
    if($id == 'undefined' || !$id)
        return;

    global $wpdb;

    $status = $wpdb->delete(AWS_FEED_TABLE, array('id' => $id));

    return $status;
}

add_action('wp_ajax_at_amazon_feed_delete_ajax', 'at_amazon_feed_delete_ajax');
function at_amazon_feed_delete_ajax() {
    $id = (isset($_POST['id']) ? $_POST['id'] : '');

    if(at_amazon_feed_delete($id)) {
        echo json_encode(array('status' => 'ok'));
        exit;
    }

    echo json_encode(array('status' => 'error'));
    exit;
}

if ( ! function_exists( 'at_amazon_multiselect_tax_form_dropdown' ) ) {
    /**
     * at_amazon_multiselect_tax_form_dropdown
     *
     * Add a dropdown of all available values;
     */
    add_filter('at_mutltiselect_tax_form_product_dropdown', 'at_amazon_multiselect_tax_form_dropdown', 10, 3);
    function at_amazon_multiselect_tax_form_dropdown($output, $properties,$tax)
    {

        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if (preg_match('/amazon/',$actual_link)) {
                $output .= '<select name="taxonomy-select-dropdown-' . $tax . '">';
                $output .= '<option value=""> -</option>';
                foreach ($properties as $key => $value) {
                        $output .= '<option value="' . $value . '">' . $key . " - " . $value . '</option>';
                }
                $output .= '</select>';
                $output .= '<script type="text/javascript">';
                $output .= "var select = document.getElementsByName('taxonomy-select-dropdown-" . $tax . "')[document.getElementsByName('taxonomy-select-dropdown-" . $tax . "').length-1];";
                $output .= "select.onchange = function () {";
                $output .= "var input = document.getElementsByName('tax[" . $tax . "][]')[document.getElementsByName('tax[" . $tax . "][]').length-1];";
                $output .= "input.value = this.value; ";
                $output .= "} </script>";
        }

        return $output;
    }
}

if ( ! function_exists( 'at_amazon_compare_box' ) ) {
    /**
     * at_amazon_compare_box
     *
     * Add Meta-Box to Product Page
     */
    add_action('add_meta_boxes', 'at_amazon_compare_box');
    function at_amazon_compare_box() {
        add_meta_box(
            'amazon_price_compare',
            '<span class="dashicons dashicons-search"></span> ' . __('Amazon Preisvergleich', 'affiliatetheme-amazon'),
            'at_amazon_compare_box_callback',
            'product'
        );
    }
}

if ( ! function_exists( 'at_amazon_compare_box_callback' ) ) {
    /**
     * at_amazon_compare_box_callback
     *
     * Add Meta-Box Content
     */
    function at_amazon_compare_box_callback($post) {
        $ean = get_post_meta($post->ID, 'product_ean', true);
        ?>

        <div id="at-import-page" class="at-import-page-amazon" data-url="<?php echo admin_url(); ?>" data-import-nonce="<?php echo wp_create_nonce("at_amazon_import_wpnonce"); ?>">
            <div class="alert alert-info api-alert">
                <span class="dashicons dashicons-megaphone"></span>
                <p><?php _e('Du kannst mit Hilfe des Preisvergleiches weitere Preise aus verschiedenen Shops zu diesem Produkt hinzufügen. Suche entweder nach der EAN oder einem Keyword und importiere weitere Preise. <br>
                Die neuen Preise werden sofort im oberen Feld hinzugefügt. Bitte speichere das Produkt wenn du fertig bist.', 'affiliatetheme-amazon'); ?></p>
            </div>

            <div class="form-container">
                <div class="form-group">
                    <label for="scompare_ean"><?php _e('EAN', 'affiliatetheme-amazon'); ?></label>
                    <input type="text" name="amazon_compare_ean" id="amazon_compare_ean" value="<?php echo $ean; ?>">
                </div>

                <div class="form-group">
                    <label for="compare_query"><?php _e('Keyword', 'affiliatetheme-amazon'); ?></label>
                    <input type="text" name="amazon_compare_query" id="amazon_compare_query">
                </div>

                <a href="#"
                   class="acf-button blue button amazon-price-compare"><?php _e('Preisvergleich ausführen', 'affiliatetheme-amazon'); ?></a>
            </div>
        </div>

        &nbsp;

        <div id="at-import-window" class="at-import-window-amazon">
            <table class="wp-list-table widefat fixed products">
                <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Alle auswählen', 'affiliatetheme-amazon'); ?></label
                        <input id="cb-select-all-1" type="checkbox">
                    </th>
                    <th scope="col" id="productid" class="manage-column column-productid" style="width: 110px">
                        <span><?php _e('ASIN', 'affiliatetheme-amazon'); ?></span>
                    </th>
                    <th scope="col" id="ean" class="manage-column column-ean" style="width: 110px">
                        <span><?php _e('EAN', 'affiliatetheme-amazon'); ?></span>
                    </th>
                    <th scope="col" id="image" class="manage-column column-image" style="width: 150px">
                        <span><?php _e('Vorschau', 'affiliatetheme-amazon'); ?></span>
                    </th>
                    <th scope="col" id="title" class="manage-column column-title">
                        <span><?php _e('Titel', 'affiliatetheme-amazon'); ?></span>
                    </th>
                    <th scope="col" id="price" class="manage-column column-price" style="width: 110px">
                        <span><?php _e('Preis', 'affiliatetheme-amazon'); ?></span>
                    </th>
                    <th scope="col" id="actions" class="manage-column column-action" style="width: 110px">
                        <span><?php _e('Aktion', 'affiliatetheme-amazon'); ?></span>
                    </th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="7">
                        <a href="#" class="mass-import button button-primary"><?php _e('Ausgewählte Produkte importieren', 'affiliatetheme-amazon'); ?></a>
                    </td>
                </tr>
                </tfoot>
                <tbody id="resultsamazon"></tbody>
            </table>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                // amazonsearchAction
                jQuery('.at-import-page-amazon').bind('keydown', function (event) {
                    if (event.keyCode == 13) {
                        amazonsearchAction();
                        event.preventDefault();
                    }
                });
                jQuery('.at-import-page-amazon .amazon-price-compare').click(function (event) {
                    amazonsearchAction();
                    event.preventDefault();
                });

                // amazonQuickImportAction
                jQuery('.amazon-quick-import').live('click', function (event) {
                    var id = jQuery(this).attr('data-id');

                    amazonQuickImportAction(id);

                    event.preventDefault();
                });

                // amazonMassImportAction
                jQuery('.at-import-window-amazon .mass-import').live('click', function (event) {
                    amazonMassImportAction(this);

                    event.preventDefault();
                });
            });

            var amazonsearchAction = function () {
                var target = jQuery('.at-import-page-amazon .amazon-price-compare');
                var ean = jQuery('.at-import-page-amazon #amazon_compare_ean').val();
                var query = jQuery('.at-import-page-amazon #amazon_compare_query').val();
                var query = (query.length < 3)?ean:query;
                var html = '';

                jQuery(target).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').attr('disabled', true).addClass('noevent');

                jQuery.ajax({
                    url: ajaxurl,
                    dataType: 'json',
                    type: 'POST',
                    data: {action: 'at_aws_search', q:query}
                }).done(function (data) {

                    if (data['items']) {
                        for (var x in data['items']) {
                            if (!data['items'][x].img)
                                data['items'][x].img = 'assets/images/no.gif';

                            if (data['items'][x].exists != "false") {
                                html += '<tr class="item success" data-id="' + data['items'][x].asin + '">';
                                html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-' + data['items'][x].asin + ' name="item[]" value="' + data['items'][x].asin + '" disabled="disabled"></th>';
                            } else {
                                html += '<tr class="item" data-id="' + data['items'][x].asin + '">';
                                html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-' + data['items'][x].asin + ' name="item[]" value="' + data['items'][x].asin + '"></th>';
                            }
                            html += '<td class="productid">' + data['items'][x].asin + '</td>';
                            html += '<td class="ean">' + (data['items'][x].ean ? data['items'][x].ean : '-') + '</td>';
                            if(data['items'][x].img !="assets/images/no.gif") {
                                html += '<td class="image"><img src="' + data['items'][x].img + '"></td>';
                            } else {
                                html += '<td class="image">-</td>';
                            }
                            html += '<td class="title"><a href="' + data['items'][x].url + '" target="_blank">' + data['items'][x].title + '</a></td>';
                            html += '<td class="price">' + data['items'][x].price + '</td>';
                            if (data['items'][x].exists != "false") {
                                html += '<td class="action"></td>';
                            } else {
                                html += '<td class="action"><a href="#" title="Quickimport" class="amazon-quick-import" data-id="' + data['items'][x].asin + '"><i class="fa fa-bolt"></i></a></td>';
                            }
                            html += '</tr>';
                        }
                    } else {
                        html += '<tr><td colspan="5"><?php _e('Es wurde kein Produkt gefunden', 'affiliatetheme-amazon'); ?></td></tr>';
                    }
                }).always(function () {
                    jQuery(target).attr('disabled', false).removeClass('noevent').find('i').remove();
                    jQuery('#at-import-window tbody#resultsamazon').html(html);
                });
            }

            var amazonQuickImportAction = function ( id, mass, i, max_items) {
                mass = mass || false;
                max_items = max_items || "0";
                i = i || "1";
                console.log("quickimport");
                console.log(id);
                var target = jQuery('#results .item[data-id=' + id + ']').find(".action a.amazon-quick-import");
                var ajax_loader = jQuery('.at-ajax-loader');
                var post_id = '<?php echo $post->ID; ?>';
                var nonce = jQuery('.at-import-page-amazon').attr('data-import-nonce');

                jQuery(target).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').addClass('noevent');

                jQuery.ajaxQueue({
                    url: ajaxurl,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        action: 'at_amazon_add_acf',
                        id: id,
                        ex_page_id: post_id,
                        func: 'quick-import',
                        '_wpnonce': nonce
                    },
                    success: function (data) {
                        jQuery(target).find('i').remove();

                        if (data['rmessage']['success'] == "false") {
                            jQuery(target).after('<div class="error">' + data['rmessage']['reason'] + '</div>');
                            jQuery(target).append(' <i class="fa fa-exclamation-triangle"></i>').attr('disabled', true);
                        } else if (data['rmessage']['success'] == "true") {

                            var shopinfo = data['shop_info'];


                            jQuery('[data-key="field_557c01ea87000"] .acf-input .acf-actions [data-event="add-row"]').trigger('click');
                            var field_id = jQuery('div[data-key="field_557c01ea87000"] tr.acf-row').not('div[data-key="field_557c01ea87000"] tr.acf-clone').last().attr('data-id');

                            var pricefield = 'acf-field_557c01ea87000-'+field_id+'-field_553b8257246b5';
                            var priceoldfield = 'acf-field_557c01ea87000-'+field_id+'-field_553c9582146b5';
                            var currencyfield = 'acf-field_557c01ea87000-'+field_id+'-field_553b82b5246b6';
                            var portalfield = 'acf-field_557c01ea87000-'+field_id+'-field_553b83de246bb';
                            var amazonIDfield = 'acf-field_557c01ea87000-'+field_id+'-field_553b75842c246bc';
                            var shopfield = 'acf-field_557c01ea87000-'+field_id+'-field_557c058187007-input';
                            var urlfield = 'acf-field_557c01ea87000-'+field_id+'-field_553b834c246b9';
                            jQuery("#"+pricefield).val(shopinfo['price']);
                            jQuery("#"+priceoldfield).val(shopinfo['price_old']);
                            jQuery("#"+currencyfield).val(shopinfo['currency']);
                            jQuery("#"+portalfield).val(shopinfo['portal']);
                            jQuery("#"+amazonIDfield).val(shopinfo['metakey']);
                            jQuery("#"+shopfield).val(shopinfo['shop']);
                            jQuery("#"+urlfield).val(shopinfo['link']);
                            window.onbeforeunload = function(event){
                                var leaverid = jQuery(event.target.activeElement).context.id;
                                if (leaverid != 'publish') return true;
                            }
                            console.log("timeout, text:"+shopinfo['shopname']);
                            setTimeout(function(){jQuery('.select2-chosen').last().text(shopinfo['shopname'])},1000);
                            jQuery(target).hide();
                            jQuery('body table.products tr[data-id=' + id + ']').addClass('success');
                            jQuery('body table.products tr[data-id=' + id + '] .check-column input[type=checkbox]').attr('disabled', 'disabled');
                            jQuery('body table.products tr[data-id=' + id + '] .action i').removeClass('fa-plus-circle').addClass('fa-check').closest('a').removeClass('quick-import');
                        }
                    }
                });
            };


            var amazonMassImportAction = function (target) {
                var max_items = jQuery('#results .item:not(".success") .check-column input:checkbox:checked').length;
                var i = 1;

                jQuery('#resultsamazon .item:not(".success") .check-column input:checkbox:checked').each(function () {
                    var id = jQuery(this).val();
                    amazonQuickImportAction(id, true, i, max_items);
                    i++;
                });
            };

            // jQuery Queue
            (function ($) {
                var ajaxQueue = $({});
                $.ajaxQueue = function (ajaxOpts) {
                    var oldComplete = ajaxOpts.complete;
                    ajaxQueue.queue(function (next) {
                        ajaxOpts.complete = function () {
                            if (oldComplete) oldComplete.apply(this, arguments);
                            next();
                        };
                        $.ajax(ajaxOpts);
                    });
                };
            })(jQuery);
        </script>
        <?php
    }
}

if ( ! function_exists( 'at_amazon_feed_create_database' ) ) {
    /**
     * at_amazon_feed_create_database
     */
    add_action('init', 'at_amazon_feed_create_database');
    function at_amazon_feed_create_database() {
        define('AT_AMAZON_DATABASE_VERSION', "0.8");
        if (get_option('at_amazon_database_version', "0") != AT_AMAZON_DATABASE_VERSION) {
            $sql = "CREATE TABLE " . AWS_FEED_TABLE . " (
                    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    keyword text,
                    category text,
                    last_message text,
                    last_update timestamp DEFAULT '0-0-0 00:00:00',
                    status int(1) DEFAULT '0',
                    tax text,
                    images int(1) DEFAULT '1',
                    description int(1) DEFAULT '0',
                    post_status text
                );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);


            update_option('at_amazon_database_version', AT_AMAZON_DATABASE_VERSION);
        }
    }
}

if ( ! function_exists( 'at_amazon_add_prime_icon' ) ) {
    /**
     * at_amazon_add_prime_icon
     */
    add_filter('at_product_price', 'at_amazon_add_prime_icon', 10, 3);
    function at_amazon_add_prime_icon($output, $post_id, $shop_id) {
        $product_shops = get_field('product_shops', $post_id);

        if (isset($product_shops[$shop_id])) {
            $current_shop = $product_shops[$shop_id];
            $current_portal = $current_shop['portal'];

            if ($current_portal == 'amazon') {
                $asin = $current_shop['amazon_asin'];

                if ($asin) {
                    $prime = get_post_meta($post_id, 'product_amazon_prime_' . $asin, true);

                    if ($prime == 'true') {
                        $output .= ' <i class="at at-prime"></i>';
                    }
                }
            }
        }

        return $output;
    }
}

add_action( 'admin_footer', 'at_amazon_inline_css' );
add_action( 'wp_footer', 'at_amazon_inline_css' );
function at_amazon_inline_css() {
    ?>
    <style type="text/css">
        .product-price .at-prime, .import_page_endcore_api_amazon #results .title .at-prime { height: 15px; width: 53px; background: url('<?php echo AWS_URL . 'assets/img/icon-prime.png'; ?>') no-repeat center center; background-size: 53px 15px; display: inline-block; }
    </style>
    <?php
}
