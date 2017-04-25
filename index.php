<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>redfox test</title>
    <link rel="stylesheet" type="text/css" media="all" href="img/style.css" />
  </head>
  <body>
<form action="index.php" method="post">
  <input type="text" name="url" value="">
  <br>
  <input type="submit" name="submit" value="send">
</form>

<?php
if(!empty($_POST['url'])){
$url = file_get_contents( $_POST['url'] );

// h2
preg_match( '/<h2>(.*?)<\/h2>/is', $url, $header );
echo $header[0];

// img
preg_match( '/<div id="big_img_detail">(.*?)<\/div>/is', $url, $img );
$img = $img[0];
$img = str_replace("src=\"", "src=\"http://ru.redfoxoutdoor.com", $img);
$img = str_replace("href=\"", "href=\"http://ru.redfoxoutdoor.com", $img);
echo $img;

// price
preg_match( '/<div class="price">(.*?)<\/div>/is', $url, $price );
$price = $price[0];
$str = $price;
$str = str_replace(' ', '', $str);
function add_percent($matches)
{
return $matches[1]*1.1;
}
echo "<span id=\"price\">";
echo preg_replace_callback("/(\d{1,10})/","add_percent",$str)." руб.</span>";
}
?>

  </body>
</html>
