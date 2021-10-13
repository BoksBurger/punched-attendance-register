<?php
$xml = 'https://www.brainyquote.com/link/quotefu.rss';
$arr = array();
try {
    $data = simplexml_load_file($xml);
    foreach ($data->channel->item as $item) {
    array_push($arr, array("author"=>$item->title, "quote"=>$item->description));    
}
} catch (Exception $ex){
    //Something went wrong.
}
echo json_encode($arr);
?>