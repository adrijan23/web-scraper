<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proficentar</title>
</head>

<body>

    <?php


    include 'functions.php';

    
    $all_category_urls = get_all_category_urls();
    //print_r($all_category_urls);

    $all_page_urls = all_pages($all_category_urls);

    $all_product_urls = get_all_products($all_page_urls);

    $all_products = get_title_price_pics($all_product_urls);

    //$products_with_desc=get_description($all_products);

    csv_download($all_products);

    echo '<pre>';
    print_r($all_products);
    echo '</pre>';



    ?>
</body>

</html>