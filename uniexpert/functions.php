<?php

function checkRemoteFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // don't download content
    // CURLOPT_USERAGENT: some sites will deny us all requests if we dont provide user agent data
    // CURLOPT_SSL_VERIFYPEER: also we are not providing client-side ssl authentication
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_USERAGENT, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    curl_close($ch);
    if ($result !== FALSE) {
        return 'correct';
    } else {
        return 'notCorrect';
    }
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
function login($url, $data)
{
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
    return curl_exec($login);
    ob_end_clean();
    curl_close($login);
    unset($login);
}

function grab_page($site)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($ch, CURLOPT_URL, $site);
    ob_start();
    return curl_exec($ch);
    ob_end_clean();
    curl_close($ch);
}

function get_category_urls()
{
    $all_categories = [];

    $reg = '/<li><a href="(search.*?catid.*?)">.*?<\/a><\/li>/';
    $data = grab_page('https://partner.ue.ba/');
    preg_match_all($reg, $data, $cat_urls);

    foreach ($cat_urls[1] as $category) {
        $category = "https://partner.ue.ba/" . $category;
        array_push($all_categories, $category);
    }
    return $all_categories;
}
function get_products($page_urls)
{
    $all_products = [];

    foreach ($page_urls as $page) {
        $reg = '/aid="(.*?)"/';
        $data = grab_page($page);
        preg_match_all($reg, $data, $matches);

        $matches = $matches[1];
        foreach ($matches as $match) {
            $product_url = "https://partner.ue.ba/product.php?item=" . $match;
            array_push($all_products, $product_url);
        }
    }
    return $all_products;
}
function all_pages($all_category_urls)
{
    foreach ($all_category_urls as $cat_url) {
        //GET PAGINATION OF EACH PAGE
        $reg = '/<li class="paging" pg=".*?"><span>([0-9]+)<\/span><\/li>\s*<li class="paging pagination-next" pg=".*?">/';
        $data = grab_page($cat_url);
        preg_match($reg, $data, $pagination);
        if (count($pagination) == 0) {
            $pagination = 1;
        } else {
            $pagination = $pagination[1];
        }
        //ADD SUBPAGE URLS FOR CATEGORY PAGE
        $page = 1;

        while ($page <= $pagination) {

            $arr[] = $cat_url . "&Pg=" . $page;

            $page++;
        }
    }
    return $arr;
}


function get_title_price_pics($all_product_urls)
{
    $all_products = [];
    $single_product = [];

    define('ROOTPATH', __DIR__);

    $i = 0;

    foreach ($all_product_urls as $product_url) {
        $product_url= trim($product_url);

        $i += 1;
        /*if ($i > 20) {
            break;
        }*/
        
        login('https://partner.ue.ba/login.php', 'username=infoars&password=infoars2020&submit=Prijavi+se');
        $data = grab_page($product_url);

        //GET TITLE
        $reg = '/<h2 class="product_title entry-title">(.*?)</';
        preg_match($reg, $data, $matchTitle);

        if(count($matchTitle)==0){
            echo $product_url . 'adrijan';
  
        }

        $title = $matchTitle[1];
        //IF PRODUCT IS NOT AVAILABLE IN SHOP
        $reg = '/<span>RASPOLOŽIVOST<\/span><\/h2>(\n|\s)+<div class="tbk__text">\s+[a-zA-Z|\s]*<li class="glyphicon glyphicon-remove" style="color: red;">/';
        preg_match($reg, $data, $matchX);
        if (count($matchX) > 0) {
            $title = $title . " (NIJE NA STANJU)";
        }
        $single_product['title'] = $title;

        //GET PRICE
        $reg = '/<span class="amount">.*?<span class="amount">(.*?)<\/span>.*?<\/span>/';
        preg_match($reg, $data, $matchPrice);

        $price = $matchPrice[1];
        $single_product['price'] = $price;

        //GET PICTURE
        $reg = '/<img .*?src="(.*?)"/';
        preg_match($reg, $data, $matchPics);

        if (count($matchPics) > 0) {
            $pictures = $matchPics[1];
            if ($pictures == "https://www.ue.ba/img/product/no-pic.jpg") {
                $single_product['pictures'] = 'Prazno polje';
            } else {
                $single_product['pictures'] = $pictures;
                //SAVE PICTURE TO FOLDER
                $img_check = checkRemoteFile($pictures);
                if ($img_check == 'correct') {
                    $save_dir = ROOTPATH . "\\images\\";
                    $title = str_replace(array("&Scaron;", "&scaron;", "š", "đ", "č", "ć", "ž"), array("S", "s", "s", "dj", "c", "c", "z"), $title);
                    $title = preg_replace('/\\\/', '', $title);
                    $title = preg_replace('/[\/]+/', '', $title);
                    $title = preg_replace('/[,]+/', '', $title);
                    $title = preg_replace('/[(|)]+/', '', $title);
                    $title = preg_replace('/["]+/', '', $title);
                    $title = preg_replace('/[*]+/', '', $title);
                    $title = preg_replace('/[:]+/', '', $title);
		    $title = preg_replace('/[;|<|>|#|&]+/', '', $title);
                    $title = trim($title);
                    //$titleImg= basename($pictures);
                    $filename = $title . date('m-d-Y_his');
                    $complete_save_loc = "$save_dir$filename.jpg";

                    file_put_contents($complete_save_loc, file_get_contents($pictures));
                }
            }
        } else {
            $single_product['pictures'] = 'Prazno polje';
        }

        //GET CATEGORY
        $reg = '/<a href="search\.php\?catid=.*?" rel="tag">(.*?)<\/a>/';
        preg_match($reg, $data, $matchCat);

        $category = $matchCat[1];
        $single_product['category'] = $category;

        //ADD OLX CATEGORY TITLE AND URL
        login("https://www.olx.ba/auth/login","username=office%40infoars.net&password=11ibHds3cd7izCG&zapamtime=on&csrf_token=LhJRlRvQv8wKjRTfOXu0C5zHunEvBB156x7MUWVk");
        $olx= suggest_olx_category($single_product['title']);

        $single_product['OLX category'] = $olx[0];
        $single_product['OLX category URL'] = $olx[1];

        //GET DESCRIPTION
        $reg = '/<div.*?id="tab-description">(.|\n)*?<!-- Related products -->/';
        preg_match($reg, $data, $desc);

        if (count($desc) > 0) {
            //REPLACING CHECKMARK IMAGES WITH EMOJI
            $description = $desc[0];
            $description = preg_replace('/<img .*?alt="☑️".*?\/>/', '☑️', $description);

            //ADDING DESCRIPTION TO SINGLE PRODUCT
            $single_product['desc'] = $description;
        } else {
            $single_product['desc'] = 'Prazno polje';
        }
        //ADDING A SINGLE PRODUCT TO ALL PRODUCTS ARRAY
        $all_products[$product_url] = $single_product;

        if (($i % 10) == 0) {
            sleep(5);
        }
    }
    return $all_products;
}

function csv_download($all_data)
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