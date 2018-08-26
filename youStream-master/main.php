<?php
if (isset($_GET['i'])) {
    $url = 'https://y2mate.com/analyze/ajax';
    $data = array('url' => 'https://www.youtube.com/watch?v=' . $_GET['i'], 'ajax' => '1');
    $options = array(
        'http' => array(
            'header' => "Content-type: application/json",
            'header' => "Referer: https://y2mate.com/youtube/".$_GET['i'],
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $result = json_decode($result, true);
    //var_dump(strip_tags($result["result"], '<a>'));
    if ($result === FALSE) { /* Handle error */
        die("error");
    }
    $html = strip_tags($result["result"], '<a>');

    if(!preg_match_all("|<a.*(?=href=\"([^\"]*)\")[^>]*>([^<]*)</a>|i", $html, $matches)) {
        /*** if no match is found ***/
        echo 'error';
    }
    foreach ($matches[1] as $key => $value) {
        if ($key == 26){
            die($value);
        }
    }
}
die("bad");
?>