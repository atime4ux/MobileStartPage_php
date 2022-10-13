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

    $idx = Request("idx");
    if(strlen($idx) > 0)
    {
        $url = "";

        $jsonConfig = new stdClass();
        $privateKey = "";
        $privateValue = "";
        
        $configFileName = "fwrss.config.json";
        $handle = @fopen($configFileName,'r');
        if($handle !== false){
            $jsonConfig = json_decode(file_get_contents($configFileName));

            foreach($jsonConfig->fwrss as $fwrss)
            {
                if($fwrss->idx == $idx)
                {
                    $url = $fwrss->url;
                    break;
                }
            }
        }

        if(strlen($url) > 0)
        {
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
                $channel->children('itunes', true)->keywords .= "(fwrss)";
                //$channel->children('itunes', true)->image->attributes()->href = ((string)$channel->children('itunes', true)->image->attributes()->href) . "?(fwrss)";
    
                $itemCnt = count($channel->item);
                $itemLimit = 100;
                if($itemCnt > $itemLimit)
                {
                    for($i=0; $i<($itemCnt-$itemLimit); $i++)
                    {
                        unset($channel->item[$itemLimit]);
                    }
                }

                for($i=0; $i<count($channel->item); $i++)
                {
                    $channel->item[$i]->title .= "(fwrss)";
                }
                
                echo $xml->asXML();
            }
        }
    }
?>