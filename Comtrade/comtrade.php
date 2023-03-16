<?php
include "functions.php";

echo login('https://www.ct4partners.ba/Login.aspx?ReturnUrl=%2f', '__VIEWSTATE=%2FwEPDwUKMTg3NDYyMDE2NmQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgIFCWZhc3RPcmRlcgUJYXV0b2xvZ2luI%2BJafK70kUGqNdHhCG94aoeeBtN%2BSINACE%2BSDmJoqMc%3D&__VIEWSTATEGENERATOR=C2EE9ABB&__EVENTVALIDATION=%2FwEdAAXj6q6FO1Xs8MzKtpxXeO1yKhoCyVdJtLIis5AgYZ%2FRYe4sciJO3Hoc68xTFtZGQEgL%2FZL%2BXTkTjXSuTzlgQojYIDqEdS4MA14REhKtTC0%2FCvQ%2B0sEVgUoMIdDtysVJMYe3%2FDYMjhEHpqgHCHgZtfgP&username=RANKO.PETRUSIC&password=InfoArts2020&fastOrder=on');

//$all_category_urls = get_category_urls();
//$all_pages = all_pages($all_category_urls);
//$product_urls= get_products($all_pages);
//$all_products= get_title_price_pics($product_urls);

//csv_download($all_products);

echo '<pre>';
//print_r($all_products);
echo '</pre>';
