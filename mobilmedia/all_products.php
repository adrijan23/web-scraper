<?php
include 'functions.php';

// VARIABLES

$category_url = $_GET['category_url'];

$pagination = $_GET['pagination'];

if (!isset($category_url)) {
  echo "category_url je obavezan argument.";
  die;
}
if (!isset($pagination)) {
  echo "pagination je obavezan argument.";
  die;
}



//$img_urls = [];
/*
$reg='/<(div)\s+(class="std">)(\n|\r)\s*(<h1).*(<\/h1>)(\n|\r)\s*((.|\n)*?)(<\/div>)/';
$data=file_get_contents('https://mobilmedia.ba/informatika/instant-kamera-fotoprinter-canon-zoemini-c-cv123-ssb.html#prettyPhoto');
preg_match($reg, $data, $desc);

print_r($desc);die;
*/
// XPATHS

$xpath_link_proizvoda = "//h2[@class='product-name']//a";

$xpath_product_price = "(//div[@class='product-shop']/div[@class='price-box']/span[@class='regular-price']/span[@class='price']   |

//div[@class='product-shop']/div[@class='price-box']/p[@class='special-price']/span[@class='price'])";

$xpath_product_title = "(//div[@class='product-name']/h1)";

$xpath_product_image = "(//img[@id='image']/@src)";

//TEST
/*$d= file_get_contents('https://mobilmedia.ba/informatika/notebook-asus-x509ub-ej009-nano-edge.html');
preg_match('/<(div)\s+(class="std">)(\n|\r)\s*(<h1).*(<\/h1>)(\n|\r)\s*(.*?)<\/div>/s', $d, $array);
preg_match('/<(div)\s+(class="std">)(\n|\r)\s*(<h1).*(<\/h1>)(\n|\r)\s*((.|\n)*?)(<\/div>)/', $d, $array);
print_r($array);
die; */

// FUNCTIONS

$category_page_url = all_cat_pages($category_url, $pagination);


/*     array(2) {

  [0]=>

  string(62) "https://mobilmedia.ba/dodatna-oprema/igracke-za-djecu.html?p=1"

  [1]=>

  string(62) "https://mobilmedia.ba/dodatna-oprema/igracke-za-djecu.html?p=2" */



$arr_products_urls = arr_products_from_category($category_page_url, $xpath_link_proizvoda);

$all_data = arr_get_price_title_image($arr_products_urls, $xpath_product_price, $xpath_product_title, $xpath_product_image);

$all_data_with_desc = all_data_with_desc($all_data);


//SAVE PARSED PICTURES
save_pics_to_fldr($all_data);

// THIS IF-STATEMENT DOWNLOADS PRODUCTS AS CSV WHEN BUTTON IS PRESSED
if (isset($_POST['btn'])) {
  csv_save_to_fldr($all_data_with_desc);
}

add_header();

echo "<pre>";
//print_r($all_data);
print_r($all_data_with_desc);
echo "</pre>";

// SAVE PRODUCTS AS CSV BUTTON
?>
<div style="text-align: center;">
  <form method="post">
    <button type="submit" name="btn">Save as CSV file</button>
  </form>
</div>
<?php


//echo "<a href='' onclick=csv_download($all_data)> Download CSV file </a>";
//bez fooreach
//$all_data = arr_get_price_title_image_if($arr_products_urls, $xpath_product_price, $xpath_product_title,$xpath_product_image);
//print_r($all_data);die;

add_footer();
