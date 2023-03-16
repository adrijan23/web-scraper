<?php
include 'functions.php';

$all_categories= get_all_categories();
$all_pages= all_pages($all_categories);
$all_product_urls= get_products($all_pages);
$all_products= get_title_price_pics($all_product_urls);

csv_download($all_products);

echo '<pre>';
print_r($all_products);
echo '</pre>';