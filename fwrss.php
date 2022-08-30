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

    $url = Request("fwurl");
    if(strlen($url) > 0)
    {
        if(strpos($url, "ATime4Ux"))
        {
            $url = str_replace("ATime4Ux", "%", $url);
            $url = urldecode($url);
        }

        $result = file_get_contents($url, false);
        if ($result !== FALSE) {
            header("Content-Type: text/xml; charset=UTF-8");

            $result = str_replace("http://", "https://" ,$result);
            $result = str_replace("file-ex.ssenhosting.com", "file.ssenhosting.com" ,$result);
            
            $xml = new SimpleXMLElement($result);

            $channel = $xml->channel;
            $channel->title .= "(fwrss)";
            $channel->link .= "?fwrss=1";

            $channel->children('itunes', true)->subtitle .= "(fwrss)";

            $itemCnt = count($channel->item);
            $itemLimit = 60;
            if($itemCnt > $itemLimit)
            {
                for($i=0; $i<($itemCnt-$itemLimit); $i++)
                {
                    unset($channel->item[$itemLimit]);
                }
            }
            
            echo $xml->asXML();
            // echo $result;
        }
    }
?>