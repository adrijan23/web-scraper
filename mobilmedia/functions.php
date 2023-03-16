<?php

function login($url,$data){
    $fp = fopen("cookie.txt", "w");
    fclose($fp);
    $login = curl_init();
    curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($login, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($login, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($login, CURLOPT_TIMEOUT, 40000);
    curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($login, CURLOPT_URL, $url);
    curl_setopt($login, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($login, CURLOPT_POST, TRUE);
    curl_setopt($login, CURLOPT_POSTFIELDS, $data);
    ob_start();
    return curl_exec ($login);
    ob_end_clean();
    curl_close ($login);
    unset($login);    
}
function grab_page($site){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($ch, CURLOPT_URL, $site);
    ob_start();
    return curl_exec ($ch);
    ob_end_clean();
    curl_close ($ch);
}
function suggest_olx_category($product_title){
    $olx=[];

    $titleEncoded= rawurlencode($product_title);
    $link= 'https://www.olx.ba/objava/predloziKat?naziv=' . $titleEncoded;

    $reg='/prijedlog_pretraga.*?>(.*?)</';
    $data=file_get_contents($link);
    preg_match_all($reg, $data, $matches);

    $suggested_categories=[];

    $matches= $matches[1];

    if(count($matches) == 0){
        array_push($olx, 'Nepoznat naziv OLX kategorije');
        array_push($olx, 'Nepoznat URL OLX kategorije');
    }else{
        foreach ($matches as $match){
            $match=json_decode('"'.$match.'"');
            $match= $match . '<br>';

            $match= str_replace('&nbsp;','>',$match);
            $match= str_replace('&raquo;','',$match);
            
            $reg='/> ([a-zšđčćžA-ZŠĐČĆŽ|\/|()|\s|&|;|,|-]+)<br>/';
            preg_match($reg, $match, $finalMatch);
            $cat= $finalMatch[1];
            $cat= trim($cat);

            //echo $cat . '<br>';
            array_push($suggested_categories, $cat);
        }
        //return $suggested_categories;
        $firstRecomendation = $suggested_categories[0];
        $firstRecomendation= preg_replace('/[(]+/', '\(', $firstRecomendation);
        $firstRecomendation= preg_replace('/[)]+/', '\)', $firstRecomendation);
        $firstRecomendation= preg_replace('/[\/]+/', '\/', $firstRecomendation);
        //echo $firstRecomendation;
        array_push($olx, $firstRecomendation);

        $reg='/<a href="(.*?)" title="'. $firstRecomendation .'">/';
    
        //$data=file_get_contents('https://www.olx.ba/objava/brzaobjava');
        $page= grab_page('https://www.olx.ba/objava/brzaobjava');
        preg_match($reg, $page, $url);

        $url= 'https://www.olx.ba' . $url[1];
        array_push($olx, $url);
    }
    return $olx;

}


// LIST ALL CATEGORY SUBPAGES (ARRAY)

function all_cat_pages($category_url, $pagination)
{
	$page = 1;

	while ($page <= $pagination) {

		$arr[] = $category_url . "?p=" . $page;

		$page++;
	}
	
	return $arr;
	



	//			array(2) {
	//
	//			  [0]=>
	//
	//			  string(42) "https://mobilmedia.ba/informatika.html?p=1"
	//
	//			  [1]=>
	//
	//			  string(42) "https://mobilmedia.ba/informatika.html?p=2"
	//
	//		}

};



// LIST ALL PRODUCT LINKS FROM SUBCATEGORY PAGES BY GET METHOD

function arr_products_from_category($category_url, $xpath_link_proizvoda)
{


	$url_products = [];

	if (isset($category_url)) {

		foreach ($category_url as $c) {

			$html = new DOMDocument();

			@$html->loadHtmlFile($c);

			$xpath = new DOMXPath($html);

			$arr = $xpath->query($xpath_link_proizvoda);

			// print_r($xpath->query($xpath_link_proizvoda));

			foreach ($arr as $n) {

				$link = $n->getAttribute("href");

				array_push($url_products, $link);
			}
		}

		return $url_products;



		/*array(9) {
                      
                      

				  [0]=>

				  string(75) "https://mobilmedia.ba/informatika/notebook-asus-x509ub-ej009-nano-edge.html"

				  [1]=>

				  string(85) "https://mobilmedia.ba/informatika/podloga-za-stolicu-podna-eshark-esl-cm1-tatami.html"

				  [2]=>

				  string(81) "https://mobilmedia.ba/informatika/podloga-za-tastaturu-eshark-esl-kp1-yugake.html"

				  [3]=>

				  string(83) "https://mobilmedia.ba/informatika/play-station-4-dualshock-controller-v2-black.html"

				  [4]=>

				  string(82) "https://mobilmedia.ba/informatika/mis-white-shark-gmp-1901-black-foot-podloga.html"

				  [5]=>

				  string(75) "https://mobilmedia.ba/informatika/mis-white-shark-gm-5002-octavius-rgb.html"

				  [6]=>

				  string(67) "https://mobilmedia.ba/informatika/kalkulator-canon-ls-122tsdbl.html"

				  [7]=>

				  string(86) "https://mobilmedia.ba/informatika/tastatura-white-shark-gk-1926-legionnaire-metal.html"

				  [8]=>

				  string(62) "https://mobilmedia.ba/informatika/set-white-shark-gc-4102.html"

				}*/
	} else {
		//?category_url=https://mobilmedia.ba/informatika.html&pagination=1 PRISTUPNI LINK
		echo "Link should be in this format: ?category_url=&pagination=";
	}
}



// THIS IS NOT FINISHED. IT SHOULD CREATE MULTIDIMENSIONAL ARRAY OF URLS WITH ITS BELONGING PRICE, TITLE AND IMAGE URL. ALSO IT SHOULD CONTAINT DESCRIPTION BUT I WILL ADD IT AS SEPARATE FUNCTION BECAUSE WE NEED MORE COMPLEX FUNCTION TO PARSE CONTENT FROM DIFFERENT SOURCES.

function arr_get_price_title_image($arr_products_urls, $xpath_product_price, $xpath_product_title, $xpath_product_image)
{
	$single_product = [];
	$all_products = [];

	foreach ($arr_products_urls as $l) {
		$doc = new DOMDocument();

		@$doc->loadHtmlFile($l);

		$xpath = new DOMXPath($doc);

		$title = $xpath->query($xpath_product_title);

		foreach ($title as $p) {
			$single_product["title"] = $p->nodeValue;
		}

		$price = $xpath->query($xpath_product_price);

		foreach ($price as $p) {
			$priceTrimed=trim($p->nodeValue);
			$single_product["price"] = $priceTrimed;
		}

		$image = $xpath->query($xpath_product_image);

		foreach ($image as $p) {
			$single_product["pictures"] = $p->nodeValue;
		}

		//ADD OLX CATEGORY TITLE AND URL
        	login("https://www.olx.ba/auth/login","username=office%40infoars.net&password=11ibHds3cd7izCG&zapamtime=on&csrf_token=LhJRlRvQv8wKjRTfOXu0C5zHunEvBB156x7MUWVk");
        	$olx= suggest_olx_category($single_product['title']);

        	$single_product['OLX category'] = $olx[0];
        	$single_product['OLX category URL'] = $olx[1];

		$all_products[$l] = $single_product;
	}
	return $all_products;
}

function arr_get_price_title_image_if($arr_products_urls, $xpath_product_price, $xpath_product_title, $xpath_product_image)
{
	$single_product = [];
	$all_products = [];

	foreach ($arr_products_urls as $l) {
		$doc = new DOMDocument();

		@$doc->loadHtmlFile($l);

		$xpath = new DOMXPath($doc);

		$title = $xpath->query($xpath_product_title);

		if (count($title) > 0) {
			$single_product["title"] = $title[0]->nodeValue;
		}

		$price = $xpath->query($xpath_product_price);

		if (count($price) > 0) {
			$single_product["price"] = $price[0]->nodeValue;
		}


		$image = $xpath->query($xpath_product_image);

		if (count($image) > 0) {
			$single_product["pictures"] = $image[0]->nodeValue;
		}

		$all_products[$l] = $single_product;
	}
	return $all_products;
}

//DATA WITH PRODUCT DESCRIPTION
function all_data_with_desc($all_data){

	$product_urls= array_keys($all_data);

	$all_data_with_desc=[];

	foreach($product_urls as $url){
		$reg='/<(div)\s+(class="std">)(\n|\r)\s*(<h1).*(<\/h1>)(\n|\r)\s*(.*?)<\/div>/s';
		$data=file_get_contents($url);
		preg_match($reg, $data, $desc);

		$description=$desc[7];
		$description= preg_replace('#<[^>]+>#', ' ', $description);
		$description= preg_replace('!\s+!', ' ', $description);


		//$index= array_search($url, $product_urls);

		$product= ($all_data[$url]);
		$product['desc']= $description;

		$all_data_with_desc[$url]= $product;
	}
	return $all_data_with_desc;
}

//THIS FUNCTION SAVES PARSED PICTURES TO FOLDER
function save_pics_to_fldr($all_data) {
	foreach($all_data as $product){
		$img_url= $product['pictures'];
		$save_dir= 'img/';
		$filename= $product['title'];
		$complete_save_loc= $save_dir . $filename . ".jpg";
		
		file_put_contents($complete_save_loc, file_get_contents($img_url));
	}
}

//DOWNLOAD PRODUCTS AS CSV
function csv_download($all_data){
	$filename= 'shopProducts.csv';

	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=$filename");

	$output=fopen("php://output", "w");

	$header= array("Title", "Price", "Pictures", "OLX category", "OLX Category URL", "Description");
	 
	fwrite($output, chr(239) . chr(187) . chr(191));
	fputcsv($output, $header);

	foreach($all_data as $row){
	fputcsv($output, $row);
	}
	fclose($output);

	exit();
}
function csv_save_to_fldr($all_data)
{
    //$filename= 'shopProducts.csv';

    //header("Content-type: text/csv");
    //header("Content-Disposition: attachment; filename=$filename");
    //header('Content-type: application/csv; charset=utf-8');
    //header('Content-Encoding: UTF-8');
    $output = fopen("csv/products.csv", "w");

    $header = array("URL", "Title", "Price", "Pictures", "Category", "OLX category", "OLX category URL", "Description");

    fwrite($output, chr(239) . chr(187) . chr(191));

    fputcsv($output, $header);

    foreach ($all_data as $product) {
        $row = [];
        $url = array_search($product, $all_data);
        array_push($row, $url);
        foreach ($product as $product_element) {
            array_push($row, $product_element);
        }
        fputcsv($output, $row);
    }
    fclose($output);
}



// EXAMPLE ARRAY OF PRODUCTS

// Note: every product have its unique URL. Thats why I put URL as key in array.

//	$all_products = [
//
//	'http...' => [
//
//	'title'   => 'Mikser',
//
//	'price'   => '100',
//
//	'pictures'  => ["1.jpg"],
//
//	'desc'   => 'blue'
//
//




//]];
//
//print_r($all_products); die;





// OVA TABELA RADI
/*

function arr_get_price_title_image($list_urls, $xpath_product_price, $xpath_product_title,

$xpath_product_image){

		echo "<table>";
       

		foreach($list_urls as $l){

			$doc = new DOMDocument();

			@$doc->loadHtmlFile($l);

			$xpath = new DOMXPath($doc);

			echo "<tr>";

			echo "<td>". $l ."<td>";

			$title = $xpath->query($xpath_product_title);

				foreach ($title as $p){

					echo "<td>" . $p->nodeValue . '</td>';	

				}

			$price = $xpath->query($xpath_product_price);

				foreach ($price as $p){

					echo '<td>' . 

					trim(preg_replace("/[KM\r\n]+/"," ",$p->nodeValue)). 

					'</td>';	

				}			

			$image = $xpath->query($xpath_product_image);

				foreach ($image as $i){

					echo '<td style="width:30%">' . $i->nodeValue . '</td>';		

				}

			echo '</tr>';



		}

		echo '</table>';

}

 */















/* foreach($array as $index=>$value)

{

  if(empty($array[$index]))

  {

    $array[$index] = $newValue;

  }



} */





/* $list = array (

    array('aaa', 'bbb', 'ccc', 'dddd'),

    array('123', '456', '789'),

    array('"aaa"', '"bbb"')

);



$fp = fopen('file.csv', 'w');



foreach ($list as $fields) {

    fputcsv($fp, $fields);

} 



fclose($fp); */



// GET HTML HEAD AND TITLE TO WEBSITE

function add_header()
{ ?>

	<!DOCTYPE html>

	<html>

	<head>
		<meta charset="UTF-8">
  		<meta http-equiv="X-UA-Compatible" content="IE=edge">
  		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Mobilmedia shop - Adrijan</title>

	</head>

	<body style="font-family:verdana;">
		<h1 style="text-align: center;">Proizvodi sa web-shopa</h1>

		<?php
		// DIO KODA KOJI GOVORI SA KOJIH STRANA SU PROIZVODI IZLISTANI
		if ($_GET['pagination'] == 1) { ?>
			<p style="text-align: center;">Izlistani su proizvodi sa stranice broj: <?php echo $_GET['pagination'] ?></p>
		<?php } else { ?>
			<p style="text-align: center;">Izlistani su proizvodi sa stranica: 1-<?php echo $_GET['pagination'] ?></p>
		<?php }; ?>
		<br>

	<?php


}

// GET FOOTER TO WEBSITE

function add_footer()
{ ?>
		<br>
		<p style="text-align: right;"><b>&copy;2021</b></p>
	</body>

	</html>

<?php

	// print </body></html>

}

?>