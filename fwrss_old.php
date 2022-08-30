<?php
    function Request($key)
    {
        $result = $_GET[$key];
        if($result == "")
        {
            $result = $_POST[$key];
        }

        return $result;
    }

    // $url = Request("url");
    // $data = array('key1' => 'value1', 'key2' => 'value2');

    // use key 'http' even if you send the request to https://...
    // $options = array(
    //     'http' => array(
    //         'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
    //         'method'  => 'POST',
    //         'content' => http_build_query($data)
    //     )
    // );
    // $context  = stream_context_create($options);
    // $result = file_get_contents($url, false, $context);

    $url = Request("fwurl");
    if(strlen($url) > 0)
    {
        $result = file_get_contents($url, false);
        if ($result !== FALSE) {
            header("Content-Type: text/xml; charset=UTF-8");

            // $xml = new SimpleXMLElement($result);

            // $channel = $xml->channel;
            // $channel->title = "this is title";
            // $channel->link = "http://atime4ux.iptime.org:5001";
            // unset($channel->pubDate);

            // $idx = 0;
            // foreach($channel->item as $rssItem)
            // {
            //     $rssItem->title = "this is item title".$idx;
            //     $rssItem->link = "http://atime4ux.iptime.org:5001";
            //     $rssItem->description = "this is item description".$idx;
            //     unset($rssItem->pubDate);
            //     unset($rssItem->author);
            //     unset($rssItem->hits);
            //     $idx++;
            // }

            // echo $xml->asXML();

            echo str_replace("http://","https://",$result);
            // echo $result;
        }
    }
?>