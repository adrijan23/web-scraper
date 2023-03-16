<?php
include 'functions.php';

$all_category_urls = get_category_urls();
$all_pages = all_pages($all_category_urls);
$product_urls= get_products($all_pages);
$all_products= get_title_price_pics($product_urls);

csv_download($all_products);

echo '<pre>';
print_r($all_products);
echo '</pre>';

