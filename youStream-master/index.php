<?php
$step = 0;
if (isset($_GET['q'])){
    $step = 1;
}
if (isset($_GET['i'])){
    $step = 2;
}
if ($step == 1){
    require_once '/vendor/autoload.php';

    if (isset($_GET['q']) && isset($_GET['maxResults'])) {
        /*
         * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
         * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
         * Please ensure that you have enabled the YouTube Data API for your project.
         */
        $DEVELOPER_KEY = 'AIzaSyBiPPSB0tkOTrrB7X1X4TH5GVbqGVR8U60';

        $client = new Google_Client();
        $headers = array('Referer' => "http://gmonroe.org");
        $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), 'headers' => $headers ));
        $client->setHttpClient($guzzleClient);
        $client->setDeveloperKey($DEVELOPER_KEY);

        // Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);

        $htmlBody = '';
        try {

            // Call the search.list method to retrieve results matching the specified
            // query term.
            $searchResponse = $youtube->search->listSearch('id,snippet', array(
                'q' => $_GET['q'],
                'maxResults' => $_GET['maxResults'],
                'type' => 'video',
            ));

            $videos = '';
            $channels = '';
            $playlists = '';

            // Add each result to the appropriate list, and then display the lists of
            // matching videos, channels, and playlists.


            $htmlBody .= <<<END
  
END;
        } catch (Exception $e) {
            $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
                htmlspecialchars($e->getMessage()));
        }
    }
} else if ($step == 2){
    $url = 'http://videomultidownload.com/?act=inforvideo';
    $data = array('link' => 'https://www.youtube.com/watch?v='.$_GET['i']);

// use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'header'  => "Referer: http://videomultidownload.com/",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { /* Handle error */ }
    $result = substr($result, strpos($result, 'Audio Format'));
    $linkArray = array();
    if(preg_match_all('/<a\s+.*?href=[\"\']?([^\"\' >]*)[\"\']?[^>]*>(.*?)<\/a>/i', $result, $matches, PREG_SET_ORDER)){
        foreach ($matches as $match) {
            array_push($linkArray,  $match[1]);
        }
    }

}
?>
<head>
    <meta charset="UTF-8">
    <title>YouStream</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="css/AudioPlayer.css">
    <style>
        #player{
            position: fixed;
            height: 50px;
            bottom: 0px;
            left: 0px;
            right: 0px;
            margin-bottom: 0px;
        }
        .scrollingtable {
            box-sizing: border-box;
            display: inline-block;
            vertical-align: middle;
            overflow: hidden;
            width: auto; /*set table width here if using fixed value*/
            /*min-width: 100%;*/ /*set table width here if using %*/
            text-align: left;
        }
        .scrollingtable * {box-sizing: border-box;}
        .scrollingtable > div {
            position: relative;
            height: 100%;
            padding-top: 20px; /*this determines column header height*/
        }
        .scrollingtable > div:before {
            top: 0;
            background: transparent; /*column header background color*/
        }
        .scrollingtable > div:before,
        .scrollingtable > div > div:after {
            content: "";
            position: absolute;
            z-index: -1;
            width: 100%;
            height: 50%;
            left: 0;
        }
        .scrollingtable > div > div {
            /*min-height: 43px;*/ /*if using % height, make this at least large enough to fit scrollbar arrows*/
            max-height: 100%;
            overflow: scroll; /*set to auto if using fixed or % width; else scroll*/
            overflow-x: hidden;
        }
        .scrollingtable > div > div:after {background: transparent;} /*match page background color*/
        .scrollingtable > div > div > table {
            width: 100%;
            margin-top: -20px; /*inverse of column header height*/
            /*margin-right: 17px;*/ /*uncomment if using % width*/
        }
        .scrollingtable > div > div > table > caption {
            position: absolute;
            top: -20px; /*inverse of caption height*/
            margin-top: -1px; /*inverse of border-width*/
            width: 100%;
            font-weight: bold;
            text-align: center;
        }
        .scrollingtable > div > div > table > * > tr > * {padding: 0;}
        .scrollingtable > div > div > table > thead {
            vertical-align: bottom;
            white-space: nowrap;
            text-align: center;
        }
        .scrollingtable > div > div > table > thead > tr > * > div {
            display: inline-block;
            padding: 0 6px 0 6px; /*header cell padding*/
        }
        .scrollingtable > div > div > table > thead > tr > :first-child:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 20px; /*match column header height*/
        }
        .scrollingtable > div > div > table > thead > tr > * > div[label]:before,
        .scrollingtable > div > div > table > thead > tr > * > div > div:first-child,
        .scrollingtable > div > div > table > thead > tr > * + :before {
            position: absolute;
            top: 0;
            white-space: pre-wrap;
            color: white; /*header row font color*/
        }
        .scrollingtable > div > div > table > thead > tr > * > div[label]:before,
        .scrollingtable > div > div > table > thead > tr > * > div[label]:after {content: attr(label);}
        .scrollingtable > div > div > table > thead > tr > * + :before {
            content: "";
            display: block;
            min-height: 20px; /*match column header height*/
            padding-top: 1px;
        }
        .scrollingtable .scrollbarhead {float: right;}
        .scrollingtable .scrollbarhead:before {
            position: absolute;
            width: 100px;
            top: -1px; /*inverse border-width*/
            background: white; /*match page background color*/
        }
        .scrollingtable > div > div > table > tbody > tr:after {
            content: "";
            display: table-cell;
            position: relative;
            padding: 0;
            top: -1px; /*inverse of border width*/
        }
        .scrollingtable > div > div > table > tbody {vertical-align: top;}
        .scrollingtable > div > div > table > tbody > tr {background: transparent;}
        .scrollingtable > div > div > table > tbody > tr > * {

            padding: 0 6px 0 6px;
            height: 20px; /*match column header height*/
        }
        .scrollingtable > div > div > table > tbody:last-of-type > tr:last-child > * {border-bottom: none;}
        .scrollingtable > div > div > table > tbody > tr:nth-child(even) {background: transparent;} /*alternate row color*/
        .scrollingtable > div > div > table > tbody > tr > * + * {border-left: none;} /*borders between body cells*/
    </style>
    <style>
        body{
            background-image: linear-gradient(rgb(18, 18, 18), rgb(7, 7, 7) 85%);
        }
        .header__txt::before {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        @-webkit-keyframes rotate {
            0% {
                -webkit-transform: rotate(90deg);
                transform: rotate(90deg);
            }
            100% {
                -webkit-transform: rotate(450deg);
                transform: rotate(450deg);
            }
        }

        @keyframes rotate {
            0% {
                -webkit-transform: rotate(90deg);
                transform: rotate(90deg);
            }
            100% {
                -webkit-transform: rotate(450deg);
                transform: rotate(450deg);
            }
        }
        /*
         gist: https://gist.github.com/Rplus/7367c892f71d69f221cd
         source: http://unlimited.kptaipei.tw/docs/
         ref: http://lea.verou.me/2012/04/background-attachment-local/

         demo by @Lea Verou
         http://dabblet.com/gist/2462915
         */
        .main {
            background-image: -webkit-gradient(linear, left top, left bottom, color-stop(30%, #fff), to(rgba(255, 255, 255, 0))), -webkit-gradient(linear, left top, left bottom, from(rgba(255, 255, 255, 0)), color-stop(70%, #fff)), radial-gradient(farthest-side at 50% 0, rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0)), radial-gradient(farthest-side at 50% 100%, rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0));
            background-image: linear-gradient(#fff 30%, rgba(255, 255, 255, 0)), linear-gradient(rgba(255, 255, 255, 0), #fff 70%), radial-gradient(farthest-side at 50% 0, rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0)), radial-gradient(farthest-side at 50% 100%, rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0));
            background-repeat: no-repeat;
            background-color: #fff;
            /* set size in mixin
            background-size */
            /* Opera doesn't support this in the shorthand */
            background-attachment: local, local, scroll, scroll;
            background-position: 50%   0%, 50% 100%, 50%   0%, 50% 100%;
        }

        .navbar {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            line-height: 24px;
            font-size: 12px;
        }
        .navbar time {
            font-weight: bolder;
        }
        .navbar .battery {
            position: relative;
            right: 5px;
            display: inline-block;
            width: 20px;
            height: 10px;
            border: 2px solid #ccc;
        }
        .navbar .battery::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 25%;
            bottom: 0;
            background-color: #a6a6a6;
        }
        .navbar .battery::after {
            content: "";
            position: absolute;
            top: 50%;
            right: 0;
            width: 4px;
            height: 5px;
            -webkit-transform: translate(100%, -50%);
            transform: translate(100%, -50%);
            background-color: #ccc;
        }

        .navbar__left,
        .navbar__right {
            -webkit-box-flex: 1;
            -ms-flex-positive: 1;
            flex-grow: 1;
        }
        .navbar__left > *,
        .navbar__right > * {
            padding: 0 5px;
        }

        .navbar__right {
            text-align: right;
        }

        .header {
            position: relative;
            height: 50px;
            line-height: 50px;
            font-size: 1.5em;
            background-color: #282828;
        }
        .header::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #ccc;
        }
        .header * {
            font-size: inherit;
        }

        .header__txt {
            position: relative;
            line-height: inherit;
            height: 50px;
            padding-left: 40px;
            padding-right: 40px;
            font-family: "Arial";
            text-align: center;
            font-weight: 100;
            -webkit-transition: opacity 0.3s 0.45s;
            transition: opacity 0.3s 0.45s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .header__txt:not([data-count="0"])::after {
            content: "(" attr(data-count) ")";
            padding-left: .25em;
            font-size: smaller;
            font-weight: 100;
        }
        .header__txt::before {
            content: "Searching...";
            opacity: 0;
            color: transparent;
            background-color: #fff;
            -webkit-transition: opacity 0.3s 0.3s, color 0.3s 0.3s;
            transition: opacity 0.3s 0.3s, color 0.3s 0.3s;
        }
        .is-searching .header__txt::before {
            color: inherit;
            opacity: 1;
        }
        .is-focus-input .header__txt {
            -webkit-transition-delay: 0;
            transition-delay: 0;
            opacity: 0;
        }

        .search {
            position: absolute;
            top: 10px;
            right: 10px;
            left: 10px;
            bottom: 10px;
            z-index: 1;
            height: 30px;
            line-height: 30px;
            text-align: right;
        }

        .search__input {
            width: 0;
            height: 30px;
            line-height: 30px;
            padding: 0 12px;
            border: 3px solid #fff;
            border-radius: 50px;
            background-color: #fff;
            text-align: center;
            -webkit-transition: width 0.3s 0.3s;
            transition: width 0.3s 0.3s;
            outline: none;
        }
        .search__input:valid, .search__input:focus {
            width: 100%;
        }

        .search__reset {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 0px;
            height: 0px;
            background: transparent;
            border: none;
            text-indent: 100%;
            overflow: hidden;
            white-space: nowrap;
            color: #ccc;
            outline: none;
            cursor: pointer;
            -webkit-transform: translate(50%, -50%) rotate(45deg) scale(0.5);
            transform: translate(50%, -50%) rotate(45deg) scale(0.5);
            -webkit-transition: width 0.4s 0.3s, height 0.4s 0.3s, -webkit-transform 0.3s 0.3s;
            transition: width 0.4s 0.3s, height 0.4s 0.3s, -webkit-transform 0.3s 0.3s;
            transition: transform 0.3s 0.3s, width 0.4s 0.3s, height 0.4s 0.3s;
            transition: transform 0.3s 0.3s, width 0.4s 0.3s, height 0.4s 0.3s, -webkit-transform 0.3s 0.3s;
        }
        .search__input:valid ~ .search__reset, .search__input:focus ~ .search__reset {
            width: 30px;
            height: 30px;
            -webkit-transform: translate(50%, -50%) rotate(0deg) scale(0.5);
            transform: translate(50%, -50%) rotate(0deg) scale(0.5);
            -webkit-transition: width 0.4s 0.3s, height 0.4s 0.3s, -webkit-transform 0.3s 0.4s;
            transition: width 0.4s 0.3s, height 0.4s 0.3s, -webkit-transform 0.3s 0.4s;
            transition: transform 0.3s 0.4s, width 0.4s 0.3s, height 0.4s 0.3s;
            transition: transform 0.3s 0.4s, width 0.4s 0.3s, height 0.4s 0.3s, -webkit-transform 0.3s 0.4s;
        }
        .search__reset::before, .search__reset::after {
            content: "";
            position: absolute;
            top: 50%;
            right: 50%;
            width: 100%;
            height: 0;
            border: 2px solid;
            border-radius: 3px;
        }
        .search__reset::before {
            -webkit-transform: translate(50%, -50%) rotate(45deg);
            transform: translate(50%, -50%) rotate(45deg);
        }
        .search__reset::after {
            -webkit-transform: translate(50%, -50%) rotate(-45deg);
            transform: translate(50%, -50%) rotate(-45deg);
        }

        .search__label {
            position: absolute;
            top: 0;
            right: 0;
            width: 30px;
            height: 100%;
            border: 3px solid;
            border-color: #fff #fff #fff;
            border-radius: 50%;
            cursor: pointer;
            -webkit-transform: rotate(90deg);
            transform: rotate(90deg);
            -webkit-transition: all 0.3s 0.6s linear;
            transition: all 0.3s 0.6s linear;
        }
        .is-searching .search__label {
            -webkit-animation: rotate 1s linear infinite;
            animation: rotate 1s linear infinite;
        }
        .search__input:valid ~ .search__label, .search__input:focus ~ .search__label {
            -webkit-transition: all 0.3s 0s linear;
            transition: all 0.3s 0s linear;
            visibility: hidden;
        }
        .search__label::after {
            content: "";
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border: 3px solid transparent;
            border-left-color: #fff;
            border-radius: 50%;
            -webkit-transform: rotate(-45deg);
            transform: rotate(-45deg);
            -webkit-transition: all 0.3s 0.6s linear;
            transition: all 0.3s 0.6s linear;
            visibility: visible;
        }
        .search__input:valid ~ .search__label::after, .search__input:focus ~ .search__label::after {
            -webkit-transform: rotate(-90deg);
            transform: rotate(-90deg);
            -webkit-transition: all 0.3s 0s linear;
            transition: all 0.3s 0s linear;
            visibility: hidden;
        }
        .is-searching .search__label::after {
            -webkit-transform: rotate(-60deg);
            transform: rotate(-60deg);
        }
        .search__label::before {
            content: "";
            position: absolute;
            top: 50%;
            right: 50%;
            width: 3px;
            height: 12px;
            background-color: #fff;
            border-radius: 5px;
            -webkit-transform-origin: 50% 0;
            transform-origin: 50% 0;
            -webkit-transform: rotate(-45deg) translateY(13px);
            transform: rotate(-45deg) translateY(13px);
            -webkit-transition: all 0.3s 0.6s linear;
            transition: all 0.3s 0.6s linear;
            visibility: visible;
        }
        .is-searching .search__label::before, .search__input:valid ~ .search__label::before, .search__input:focus ~ .search__label::before {
            -webkit-transition: all 0.3s 0s linear;
            transition: all 0.3s 0s linear;
            width: 0;
            height: 0;
            visibility: hidden;
        }

        .main {
            min-height: 50vh;
            max-height: calc(100% - 74px);
            overflow: auto;
            background-size: 100% 30px, 100% 30px, 100% 10px, 100% 10px;
        }

        .result {
            padding-top: 1rem;
            list-style-type: none;
            text-align: center;
            line-height: 2.5;
            font-size: 24px;
            overflow-x: hidden;
            -webkit-transition: opacity 0.6s;
            transition: opacity 0.6s;
        }
        .is-focus-input .result, .is-loading .result {
            opacity: 0;
        }

        .result__item {
            position: relative;
            -webkit-transform: rotateX(90deg);
            transform: rotateX(90deg);
            -webkit-transform-style: preserve-3d;
            transform-style: preserve-3d;
            white-space: nowrap;
            text-overflow: ellipsis;
            -webkit-transition: left 0.6s, -webkit-transform 0.6s 0.6s;
            transition: left 0.6s, -webkit-transform 0.6s 0.6s;
            transition: transform 0.6s 0.6s, left 0.6s;
            transition: transform 0.6s 0.6s, left 0.6s, -webkit-transform 0.6s 0.6s;
        }
        .result__item:nth-last-of-type(1) {
            -webkit-transition-delay: 1.2s, 0.6s;
            transition-delay: 1.2s, 0.6s;
        }
        .result__item:nth-last-of-type(2) {
            -webkit-transition-delay: 1.3s, 0.7s;
            transition-delay: 1.3s, 0.7s;
        }
        .result__item:nth-last-of-type(3) {
            -webkit-transition-delay: 1.4s, 0.8s;
            transition-delay: 1.4s, 0.8s;
        }
        .result__item:nth-last-of-type(4) {
            -webkit-transition-delay: 1.5s, 0.9s;
            transition-delay: 1.5s, 0.9s;
        }
        .result__item:nth-last-of-type(5) {
            -webkit-transition-delay: 1.6s, 1s;
            transition-delay: 1.6s, 1s;
        }
        .result__item:nth-last-of-type(6) {
            -webkit-transition-delay: 1.7s, 1.1s;
            transition-delay: 1.7s, 1.1s;
        }
        .result__item:nth-last-of-type(7) {
            -webkit-transition-delay: 1.8s, 1.2s;
            transition-delay: 1.8s, 1.2s;
        }
        .result__item:nth-last-of-type(8) {
            -webkit-transition-delay: 1.9s, 1.3s;
            transition-delay: 1.9s, 1.3s;
        }
        .result__item:nth-last-of-type(9) {
            -webkit-transition-delay: 2s, 1.4s;
            transition-delay: 2s, 1.4s;
        }
        .result__item:nth-last-of-type(10) {
            -webkit-transition-delay: 2.1s, 1.5s;
            transition-delay: 2.1s, 1.5s;
        }
        .result__item:nth-of-type(odd) {
            left: -100%;
        }
        .result__item:nth-of-type(even) {
            left: 100%;
        }
        .is-loaded .result__item {
            left: 0;
            -webkit-transform: rotateX(0deg);
            transform: rotateX(0deg);
        }
        .is-focus-input .result__item {
            -webkit-transition-delay: 0.6s;
            transition-delay: 0.6s;
        }

        .result__item__link {
            position: relative;
            max-width: 100%;
            color: inherit;
            text-decoration: none;
        }
        .result__item__link:hover {
            text-decoration: underline;
        }
        .result__item__link::after {
            position: absolute;
            content: "";
            top: 50%;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: currentColor;
            opacity: 1;
            -webkit-transform: rotateX(-90deg) translateY(-50%);
            transform: rotateX(-90deg) translateY(-50%);
        }

        .box {
            width: 375px;
            margin: 4em auto;
            border-top: 60px solid #fff;
            border-bottom: 70px solid #fff;
            -webkit-box-shadow: 0 0 0 24px #fff;
            box-shadow: 0 0 0 24px #fff;
            border-radius: 20px;
            text-align: left;
            background-color: #fff;
            font-family: sans-serif;
            color: #999;
        }

        .wrapper {
            height: 667px;
            outline: 3px solid #ccc;
        }

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            position: relative;
            margin: 0;
            min-height: 100vh;
            padding-top: 1px;
            padding-bottom: 3em;
            text-align: center;
            color: #fff;
            overflow-y: hidden;
        }

        .inner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: auto;
        }

        .intro {
            width: 80%;
            max-width: 36rem;
            padding-bottom: 1rem;
            margin: .5em auto 1em;
            text-transform: capitalize;
            border-bottom: 1px dashed #fff;
        }
        .intro small {
            display: block;
            text-transform: none;
            font-weight: 100;
            font-style: italic;
            opacity: .75;
        }
        .intro label {
            cursor: pointer;
        }
        .intro label::before {
            content: "";
            display: inline-block;
            vertical-align: middle;
            width: 1em;
            height: 1em;
            -webkit-box-shadow: inset 0 0 0 .1em;
            box-shadow: inset 0 0 0 .1em;
            -webkit-transform: scale(0.7);
            transform: scale(0.7);
            opacity: .75;
        }
        #switcher:checked + .intro label::before {
            -webkit-box-shadow: inset 0 0 0 .1em, inset 0 0 0 .25em #fff, inset 0 0 0 1em;
            box-shadow: inset 0 0 0 .1em, inset 0 0 0 .25em #fff, inset 0 0 0 1em;
        }

        .info {
            bottom: 0;
            right: 0;
            margin: 1em;
            font-size: .9em;
            font-style: italic;
            font-family: serif;
            text-align: right;
        }
        .info a {
            color: inherit;
        }

    </style>
</head>
<header class="header">
    <h1 class="header__txt" data-count="0">Search</h1>
    <form id="searchss" class="search"  action="" method="GET">
        <input class="search__input" id="search__input" type="text" value="<?php echo($_GET['q']);?>" name="q" required="required" value="" tabindex="1">
        <input name="maxResults" type="text" hidden value="25"/>
        <button class="search__reset" type="reset">x</button>
        <label class="search__label" for="search__input"></label>
    </form>
</header>
<div class="scrollingtable">
    <div>
        <div>
<table id="resultss" style="width:100%;background-color: transparent;">
    <tr style="height:40px">
        <th style="width:10%"></th>
        <th style="width:90%"></th>
        <th class="scrollbarhead"></th>
    </tr>
</table>
    </div>
        </div>
</div>
<!-- Audio player container-->
<div id='player'></div>

<!-- Audio player js begin-->
<script src="js/AudioPlayer.js"></script>

<script>
    var iconImage = null;
    function search(q){
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                var src = xhr.responseText;
                var pcs = src.split("[||~|~~|~||]");
                var titles = pcs[0].split("_+_~_+_");

                var ids = pcs[1].split(",");
                var firstROW = ' <tr style="height:40px"><th style="width:10%"></th><th style="width:90%"></th><th class="scrollbarhead"></th></tr>';
                var allRows = '';
                for(var i = 0; i<titles.length; i++){
                    var title = titles[i].replace("'", " ");
                   allRows += "<tr>" +
                        "<td><img style=\"height:40px;\" src=\"https://i.ytimg.com/vi/" + ids[i] + "/default.jpg\"/></td>" +
                        '<td><a style="color:white; font-family:Arial;text-decoration-line: none;" onClick="getMusicURL(\'' + ids[i] + '\', \'' + title + '\');">'+ titles[i] + '</a></td>' +
                        "</tr>";
                    
                }
                document.getElementById("resultss").innerHTML = firstROW + allRows;

            }
        }
        xhr.open('GET', 'https://gmonroe.org/archive/youStream/search.php?q=' + q + '&maxResults=25', true);
        xhr.send(null);
    }
    function getMusicURL(id, name){
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState == XMLHttpRequest.DONE) {
                var url = xhr.responseText;
                AP.update([
                    {'icon': iconImage, 'title': name, 'file': url}
                ]);
            }
        }
        xhr.open('GET', 'https://gmonroe.org/archive/youStream/main.php?i=' + id, true);
        xhr.send(null);
    }
    (function() {
        for(var n=8, r=document.querySelector("tbody>tr"), p=r.parentNode; n--; p.appendChild(r.cloneNode(true)));
    })();
    document.getElementById("search__input")
        .addEventListener("keyup", function(event) {
            event.preventDefault();
            if (event.keyCode === 13) {
                search(this.value);
            }
        });
    // test image for web notifications

    AP.init({
        container:'#player',//a string containing one CSS selector
        volume   : 0.7,
        autoPlay : true,
        notification: false,
        playList: [
            {'icon': iconImage, 'title': 'YouStream', 'file': 'mp3/welcome.mp3'}
        ]
    });
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
    /* global window, document, console, jQuery */

    jQuery(function($) {
        'use strict';

        var eles = {};

        eles.box = document.querySelector('.box');
        eles.headerTxt = eles.box.querySelector('.header__txt');
        eles.searchForm = eles.box.querySelector('.search');
        eles.searchInput = eles.searchForm.querySelector('.search__input');
        eles.result = eles.box.querySelector('.result');
        eles.resultItem = eles.result.querySelector('.result__item');

        var resultItemTpl = eles.resultItem.outerHTML;
        var searchURL = eles.searchForm.getAttribute('data-action');

        var search = function() {
            eles.result.innerHTML = '';
            eles.box.classList.remove('is-loaded');
            eles.box.classList.add('is-searching');

            var queryString = eles.searchInput.value;

            // jquery
            $.getJSON(searchURL, {q: queryString})
                .done(function(data) {
                    var countLimit = ~~(Math.random() * 10) + 3;
                    var items = data.items.slice(0, 1000);

                    var results = [];
                    var i = 0;

                    if (data.incomplete_results || !items.length) {
                        items = [
                            {
                                full_name: 'no result!',
                                html_url: '###'
                            }
                        ];
                    }

                    var itemsLen = items.length;

                    for (; i < itemsLen; i++) {if (window.CP.shouldStopExecution(1)){break;}
                        results[i] = resultItemTpl.replace(/(<a[^>]+?)>.+?(<\/a>)/, '$1 href="' + items[i].html_url + '">' + items[i].full_name + '$2');
                    }
                    window.CP.exitedLoop(1);

                    eles.result.innerHTML = results.join('');

                    setTimeout(function() {
                        eles.headerTxt.innerText = queryString;
                        eles.headerTxt.setAttribute('data-count', itemsLen);
                        eles.box.classList.remove('is-searching');
                        eles.box.classList.add('is-loaded');
                    }, 1000);
                });

            resetSearch();
        };

        var resetSearch = function() {
            eles.searchInput.value = '';
            $(eles.searchInput).blur(); // jquery
        };

        eles.searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            search();
        });

        eles.searchInput.addEventListener('focus', function() {
            eles.box.classList.add('is-focus-input');
        });

        eles.searchInput.addEventListener('blur', function() {
            eles.box.classList.remove('is-focus-input');
        });

    });
    //# sourceURL=pen.js
</script>