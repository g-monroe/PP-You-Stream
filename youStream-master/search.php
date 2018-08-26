<?php
require_once '/vendor/autoload.php';

if (isset($_GET['q']) && isset($_GET['maxResults'])) {
    /*
     * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
     * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
     * Please ensure that you have enabled the YouTube Data API for your project.
     */
    $DEVELOPER_KEY = 'KEY';

    $client = new Google_Client();
    $headers = array('Referer' => "http://gmonroe.org");
    $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), 'headers' => $headers ));
    $client->setHttpClient($guzzleClient);
    $client->setDeveloperKey($DEVELOPER_KEY);

    // Define an object that will be used to make all API requests.
    $youtube = new Google_Service_YouTube($client);

    $htmlBody = '';

        // Call the search.list method to retrieve results matching the specified
        // query term.
        $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $_GET['q'],
            'maxResults' => $_GET['maxResults'],
            'type' => 'video',
            'videoCategoryId' => '10'
        ));
        foreach ($searchResponse['items'] as $searchResult) {
            switch ($searchResult['id']['kind']) {
                case 'youtube#video':
                    $videos = $videos . $searchResult['snippet']['title'] . "_+_~_+_";
                    $ids = $ids . $searchResult['id']['videoId'] . ",";
                    break;
            }
        }
        // Add each result to the appropriate list, and then display the lists of
        // matching videos, channels, and playlists.


  die($videos."[||~|~~|~||]".$ids);
}
?>
