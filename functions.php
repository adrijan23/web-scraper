<?php
function checkRemoteFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
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
    if($result !== FALSE)
    {
        return 'correct';
    }
    else
    {
        return 'notCorrect';
    }
}
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
            
            $reg='/> ([a-zšđčćžA-ZŠĐČĆŽ|\/|\\|()|\s|&|;|,|-|.]+)<br>/';
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




//THIS FUNCTION RETURNS AN ARRAY OF ALL CATEGORY URLS
function get_all_category_urls(){
    $reg='/<li ( class="open" )?>\n?.*<a.*href="(.*?)">/m';
    $data=file_get_contents('https://proficentar.ba/shop/akumulatorski-alati');
    preg_match_all($reg, $data, $matches, PREG_SET_ORDER, 0);
    
    $category_urls= [];
    
    foreach($matches as $match){
        $match= $match[2];
        $category_url = 'https://proficentar.ba' . $match;
    
        array_push($category_urls, $category_url);
    }
    return $category_urls;
}
;
function all_pages($all_category_urls){
    foreach($all_category_urls as $cat_url){
        //GET PAGINATION OF EACH PAGE
        $reg='/>([0-9]*)<\/a><\/li> <li><a.*?<\/li><\/ul>/';
        $data=file_get_contents($cat_url);
        preg_match($reg, $data, $pagination);
        if(count($pagination)==0){
            $pagination=1;
        }else{
            $pagination=$pagination[1];
        }
        //ADD SUBPAGE URLS FOR CATEGORY PAGE
        $page = 1;

        while ($page <= $pagination) {

            $arr[] = $cat_url . "?page=" . $page;

            $page++;
        }
    }
    return $arr;
}

function get_all_products($all_page_urls){
    $all_product_urls= [];

    foreach($all_page_urls as $page_url){
        $reg='/<div class="item">\n?.*\n?.*?<a href="(.*?)">/';
        $data=file_get_contents($page_url);
        preg_match_all($reg, $data, $matches, PREG_SET_ORDER, 0);

        foreach($matches as $match){
            if(count($match)==0){
                continue;
            }
            $match= $match[1];

            $product_url= 'https://proficentar.ba' . $match;
            //echo $product_url . '<br>';
            
            array_push($all_product_urls, $product_url);
            
        }

    }
    return $all_product_urls;
}

function get_title_price_pics($all_product_urls){
    $all_products= [];
    $single_product = [];

    define('ROOTPATH', __DIR__);

    $i=0;

    foreach($all_product_urls as $product_url){
        if($product_url == 'https://proficentar.ba'){
            continue;
        }

        $i+=1;
        /*if($i>2){
            break;
        }*/
        
        $data=file_get_contents($product_url);

        //GET TITLE
        $reg='/<h1>(.*?)<\/h1>/';
        preg_match($reg, $data, $matchTitle);

        $title= $matchTitle[1];
        $single_product['title']=$title;

        //GET PRICE
        $reg='/<span class="product-price".*?><span>(.*?)</';
        preg_match($reg, $data, $matchPrice);

        $price= $matchPrice[1];
        $single_product['price']=$price;

        //GET PICTURE
        $reg='/<div class="product-gallery">((.|\n)*?)src="(.*?)"/';
        preg_match($reg, $data, $matchPics);

        if(count($matchPics) > 0){
            $pictures= $matchPics[3];
            $single_product['pictures']=$pictures;
            //SAVE PICTURE TO FOLDER
            $img_check= checkRemoteFile($pictures);
            if($img_check == 'correct'){
                $save_dir= ROOTPATH ."\\product_img\\";
                $title = str_replace(array("&Scaron;","&scaron;","š","đ","č","ć","ž"),array("S", "s","s","dj","c","c","z"),$title);
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
        }else{
            $single_product['pictures']='Prazno polje';
        }

        //GET CATEGORY
        $reg='/<ul class="breadcrumbs">((.|\n)*?)href="\/shop\/.*?>(.*?)</';
        preg_match($reg, $data, $matchCat);

        $category= $matchCat[3];
        $single_product['category']=$category;

	//ADD OLX CATEGORY TITLE AND URL
        login("https://www.olx.ba/auth/login","username=office%40infoars.net&password=11ibHds3cd7izCG&zapamtime=on&csrf_token=LhJRlRvQv8wKjRTfOXu0C5zHunEvBB156x7MUWVk");
        $olx= suggest_olx_category($single_product['title']);

        $single_product['OLX category'] = $olx[0];
        $single_product['OLX category URL'] = $olx[1];

        //GET DESCRIPTION
        $reg='/<div class="product-description(.*?)<\/div>((.*?)<\/table>(.*?)<\/div>)?/s';
		$data=file_get_contents($product_url);
		preg_match($reg, $data, $desc);

        if (count($desc)>0){
            //REPLACING CHECKMARK IMAGES WITH EMOJI
            $description=$desc[0];
            $description= preg_replace('/<img .*?alt="☑️".*?\/>/', '☑️', $description); 
            
            //SAVE PICTURES FROM DESCRIPTION IF EXISTING
            $reg='/<p>(<img.*?\/>)<\/p>/';
            //$data=file_get_contents($product_url);
            preg_match($reg, $data, $images);

            if (count($images)>0){
                $reg='/src="(.*?)"/';
                $data=$images[0];
                preg_match_all($reg, $data, $img_urls);

                $img_urls=$img_urls[1];

                foreach ($img_urls as $img){
                    $img_check= checkRemoteFile($img);
                    if($img_check == 'notCorrect'){
                        continue;
                    }
                    $title = str_replace(array("&scaron;","š","đ","č","ć","ž"),array("s","s","dj","c","c","z"),$title);
                    $title= preg_replace('/[\/]+/', '', $title);
                    $title= preg_replace('/[,]+/', '', $title);
                    $title= preg_replace('/[(|)]+/', '', $title);
                    //$title= basename($img);
                    $save_dir= ROOTPATH."/desc_img/";
                    $filename= $title . date('m-d-Y_his');
                    $complete_save_loc= $save_dir . $filename . ".jpg";
                
                    file_put_contents($complete_save_loc, file_get_contents($img));  
                }
            }
            //ADDING DESCRIPTION TO SINGLE PRODUCT
            $single_product['desc']= $description;
        }else{
            $single_product['desc']= 'Prazno polje';
        }
        //ADDING A SINGLE PRODUCT TO ALL PRODUCTS ARRAY
        $all_products[$product_url] = $single_product;

        if(($i % 10)==0){
            sleep(5);
        }
    
    }
    return $all_products;
}
/* THIS FUNCTION IS USED INSIDE ALL PRODUCTS
function get_description($all_products){
	$product_urls= array_keys($all_products);

	$all_data_with_desc=[];

	foreach($product_urls as $url){
		$reg='/<div class="product-description(.*?)<\/div>((.*?)<\/table>(.*?)<\/div>)?/s';
		$data=file_get_contents($url);
		preg_match($reg, $data, $desc);

        //REPLACING CHECKMARK IMAGES WITH EMOJI
		$description=$desc[0];
		$description= preg_replace('/<img .*?alt="☑️".*?\/>/', '☑️', $description); 

        //SAVE PICTURES FROM DESCRIPTION IF EXISTING
        $reg='/<p>(<img.*?\/>)<\/p>/';
		$data=file_get_contents($url);
		preg_match($reg, $data, $images);

        if (count($images)>0){
            $reg='/src="(.*?)"/';
            $data=$desc[0];
            preg_match_all($reg, $data, $img_urls);

            $img_urls=$img_urls[1];
            $productArr= ($all_products[$url]);
            $title=$productArr['title'];

            foreach ($img_urls as $img){
                $save_dir= 'desc_img/';
                $filename= $title . date('m-d-Y_his');
                $complete_save_loc= $save_dir . $filename . ".jpg";
              
                file_put_contents($complete_save_loc, file_get_contents($img));  
            }
        }

        //ADDING DESCRIPTION TO PRODUCTS ARRAY
		$product= ($all_products[$url]);
		$product['desc']= $description;

		$all_data_with_desc[$url]= $product;


	}
	return $all_data_with_desc;
}
*/
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





