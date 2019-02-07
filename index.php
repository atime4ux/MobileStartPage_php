<?php
    function Get($node, $childName)
    {
        return $node->getElementsByTagName($childName)->item(0)->nodeValue;
    }

    function Set($node, $childName, $val)
    {
        $node->getElementsByTagName($childName)->item(0)->nodeValue = $val;
    }

    function Request($key)
    {
        $result = $_GET[$key];
        if($result == "")
        {
            $result = $_POST[$key];
        }

        return $result;
    }

    function SortNode($nodes)//정렬
    {
        $sorted = iterator_to_array($nodes);

        //사용안하는 노드 sort값 max로 설정
        foreach($sorted as $node)
        {
            if(Get($node, "USE_YN") == "N")
            {
                if($node->nodeName == "MENU_CATEGORY_INFO")
                {
                    Set($node, "CATEGORY_SORT", $nodes->length);
                }
                elseif($node->nodeName == "MENU_SITE_INFO")
                {
                    Set($node, "SITE_SORT", $nodes->length);
                }
            }
        }

        //정렬
        usort($sorted, function($a, $b){
            $aVal = "0";
            $bVal = "0";

            if($a->nodeName == "MENU_CATEGORY_INFO")
            {
                $aVal = Get($a, "CATEGORY_SORT");
                $bVal = Get($b, "CATEGORY_SORT");
            }
            elseif($a->nodeName == "MENU_SITE_INFO")
            {
                $aVal = Get($a, "CATEGORY_IDX") * 10000 + Get($a, "SITE_SORT");
                $bVal = Get($b, "CATEGORY_IDX") * 10000 + Get($b, "SITE_SORT");
            }

            if($aVal == $bVal)
            {
                if(Get($a, "UPDATE_DATE") >= Get($b, "UPDATE_DATE"))
                {
                    $aVal = $aVal - 1;
                }
                else
                {
                    $bVal = $bVal - 1;
                }
            }
            
            return $aVal - $bVal;            
        });

        //정렬적용
        $sortVal = 0;
        foreach($sorted as $node)
        {
            $sortVal++;

            if($node->nodeName == "MENU_CATEGORY_INFO")
            {
                Set($node, "CATEGORY_SORT", $sortVal);
            }
            elseif($node->nodeName == "MENU_SITE_INFO")
            {
                Set($node, "SITE_SORT", $sortVal);
            }

            $nodes->item(0)->parentNode->appendChild($node);
        }
    }


    //비공개 자료 출력 여부
    $public_yn = "Y";
    $privateKey = "";
    $privateValue = "";    
    if(Request(privateKey) == privateValue)
    {
        $public_yn = "";
    }


    $xmlFileName = "MobileStartPage.xml"; 
    $xmlDoc = new DOMDocument();
    $xmlDoc->load($xmlFileName);


    //xml데이터 카테고리, 사이트로 분류
    $categoryNodes = $xmlDoc->getElementsByTagName("MENU_CATEGORY_INFO");//array();
    $siteNodes = $xmlDoc->getElementsByTagName("MENU_SITE_INFO");//array();


    //ajax 요청이 있는 경우 처리하고 종료
    $ajaxMode = Request("AJAX_MODE");
    if($ajaxMode != "")
    {
        header("Content-Type: application/json");

        $flagSave = false;
        $result = "";
        if($ajaxMode == "ADD_CATEGORY")
        {
            //
        }
        elseif($ajaxMode == "REMOVE_CATEGORY")
        {
            //
        }
        elseif($ajaxMode == "MOD_CATEGORY")
        {
            //
        }
        elseif($ajaxMode == "GET_SITE_INFO")
        {
            $site_idx = Request("SITE_IDX");
            if($site_idx > 0)
            {
                foreach($siteNodes as $site)
                {
                    if(Get($site, "SITE_IDX") == $site_idx)
                    {
                        $obj->category_idx = Get($site, "CATEGORY_IDX");
                        $obj->site_idx = Get($site, "SITE_IDX");
                        $obj->site_name = Get($site, "SITE_NAME");
                        $obj->site_url = Get($site, "SITE_URL");
                        $obj->site_url_mobile = Get($site, "SITE_URL_MOBILE");
                        $obj->site_sort = Get($site, "SITE_SORT");

                        $result = json_encode($obj);
                        break;
                    }
                }
            }
            else
            {
                return;
            }
        }
        elseif($ajaxMode == "SAVE_SITE_INFO")
        {
            $flagSave = true;

            $site_idx = Request("txtSiteIdx");
            $category_idx = Request("ddlCategory");
            $site_name = Request("txtSiteName");
            $site_url = Request("txtSiteUrl");
            $site_url_mobile = Request("txtSiteUrlMobile");
            $site_sort = Request("txtSiteSort");
            $use_yn = Request("ddlUseYN");
            $create_date = date("DATE_W3C");

            if($category_idx > 0)
            {
                $datetime =  new DateTime();
                
                if($site_idx <= 0)
                {
                    //신규
                    $newSiteIdx = 0;
                    foreach($siteNodes as $site)
                    {
                        if(Get($site, "SITE_IDX") > $newSiteIdx)
                        {
                            $newSiteIdx = Get($site, "SITE_IDX");
                        }
                    }
                    $newSiteIdx = $newSiteIdx + 1;


                    $newSite = $xmlDoc->createElement("MENU_SITE_INFO");
                    $newSite->appendChild($xmlDoc->createElement("SITE_IDX", $newSiteIdx));
                    $newSite->appendChild($xmlDoc->createElement("SITE_NAME", $site_name));
                    $newSite->appendChild($xmlDoc->createElement("SITE_URL", $site_url));
                    $newSite->appendChild($xmlDoc->createElement("SITE_URL_MOBILE", $site_url_mobile));
                    $newSite->appendChild($xmlDoc->createElement("SITE_SORT", $site_sort));
                    $newSite->appendChild($xmlDoc->createElement("USE_YN", $use_yn));
                    $newSite->appendChild($xmlDoc->createElement("CREATE_DATE", $datetime->format(DateTime::W3C)));
                    $newSite->appendChild($xmlDoc->createElement("UPDATE_DATE", $datetime->format(DateTime::W3C)));
                    $newSite->appendChild($xmlDoc->createElement("CATEGORY_IDX", $category_idx));

                    $siteNodes->item(0)->parentNode->appendChild($newSite);
                }
                else
                {
                    //수정
                    foreach($siteNodes as $site)
                    {
                        if(Get($site, "SITE_IDX") == $site_idx)
                        {
                            Set($site, "CATEGORY_IDX", $category_idx);
                            Set($site, "SITE_NAME", $site_name);
                            Set($site, "SITE_URL", $site_url);
                            Set($site, "SITE_URL_MOBILE", $site_url_mobile);
                            Set($site, "SITE_SORT", $site_sort);
                            Set($site, "USE_YN", $use_yn);
                            Set($site, "UPDATE_DATE", $datetime->format(DateTime::W3C));
                            break;
                        }
                    }
                }
            }
            else
            {
                return;
            }
        }


        if($flagSave == true)
        {
            SortNode($categoryNodes);//정렬
            SortNode($siteNodes);//정렬
            $xmlDoc->save($xmlFileName);//저장

            if($result == "")
            {
                $result = json_encode("");
            }
        }


        ob_clean();
        echo $result;
        return;
    }
?>

<!DOCTYPE HTML PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.wapforum.org/DTD/xhtml-mobile12.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">
<head>
    <meta http-equiv="Content-Type" content="text/html" />
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, width=device-width" />
    <title>Mobile Start Page</title>
    <link href="/css/MobileStartPage.css" rel="stylesheet" />
    <style>
        ul {
        display:none;
        }
    </style>
    <script src="/js/jquery-1.6.4.js"></script>
    <script src="/js/common.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#txtSearchWord").keydown(function (key) {
                if (key.keyCode == 13) {
                    var search_word = $("#txtSearchWord").val();
                    $("#txtSearchWord").val('');
                    SearchGoogle(search_word);
                }
            });

            $("#hdfCatId").val($("ul:first").attr("id"));
        });
        function ExpandCategory(id)
        {
            $("#hdfCatId").val(id);
            $("#" + id).show(200, function () {
                var offsetTop = ($("#" + id).offset().top * 1) - 30;
                $('html, body').animate({
                    scrollTop: offsetTop
                }, 200);
            });
        }
        function ToggleDisplayProp(id) {
            var cat_id_prev = $("#hdfCatId").val();

            if ($("#" + cat_id_prev).length == 0) {
                ExpandCategory(id);
            }
            else {
                if (id == cat_id_prev) {
                    $("#" + cat_id_prev).hide();
                    $("#hdfCatId").val('');
                }
                else {
                    $("#" + cat_id_prev).hide();
                    ExpandCategory(id);
                }
            }
        }
        function IsMobile() {
            var filter = "win16|win32|win64|mac";

            if (navigator.platform) {
                if (filter.indexOf(navigator.platform.toLowerCase()) >= 0) {
                    //데스크탑
                    return false;
                }
            }

            return true;
        }
        function MovePage(mobileUrl, desktopUrl) {
            if (IsMobile() == true) {
                //window.open(mobileUrl);//크롬에 홈버튼 보이면서 그냥 페이지 이동으로 변경
                document.location.href = mobileUrl;
            }
            else {
                if (desktopUrl == undefined || desktopUrl == null || desktopUrl == '') {
                    desktopUrl = mobileUrl;
                }
                document.location.href = desktopUrl;
            }
        }
        function NewTab() {
            window.open('about:blank');
        }
        function ShowEditSiteArea() {
            $("#divEditSite").css('display', 'block');
        }
        function HideEditSiteArea() {
            $("#divEditSite").css('display', 'none');
        }
        function AddSiteInfo(category_id) {
            $("#txtSiteIdx").val('');
            $("#txtSiteName").val('');
            $("#txtSiteUrl").val('');
            $("#txtSiteUrlMobile").val('');
            $("#txtSiteSort").val('');
            $("#ddlUseYN").val('Y');
            $("#ddlUseYN").attr('disabled', 'disabled');
        }
        function GetSiteInfo(site_idx)
        { 
            ShowEditSiteArea();
            $("#ddlUseYN").removeAttr('disabled');

            var url = window.location.pathname;
            var postdata = GetPostData()
                            + "&SITE_IDX=" + site_idx
                            + "&AJAX_MODE=GET_SITE_INFO";

            CallAjax(url, postdata, {
                Run: function (data) {
                    if (data.site_idx != undefined && data.site_idx != null && site_idx != '') {
                        $("#ddlCategory").val(data.category_idx);
                        $("#txtSiteIdx").val(data.site_idx);
                        $("#txtSiteName").val(data.site_name);
                        $("#txtSiteUrl").val(data.site_url);
                        $("#txtSiteUrlMobile").val(data.site_url_mobile);
                        $("#txtSiteSort").val(data.site_sort);
                    }
                }
            }
                                    , Ajax_Fail);
        }
        function SaveSiteInfo() {
            var url = window.location.pathname;
            var postdata = GetPostData()
                            + "&AJAX_MODE=SAVE_SITE_INFO";

            CallAjax(url, postdata, {
                Run: function (data) {
                    if (data == '') {
                        window.location.reload(true);
                    }
                    else {
                        Ajax_Fail.Run(data);
                    }
                }
            }
                                    , Ajax_Fail);
        }
        function SearchGoogle(search_word) {
            var url = "http://www.google.co.kr/cse";
            var param = "?q=" + encodeURIComponent(search_word);

            window.open(url + param);
        }
        var AddSite_Success = {
            Run: function (data) {
            }
        }
    </script>
</head>
<body>
    <div id="divMain">
        <div id="divNewTab">
            <span onclick="NewTab()">새탭 생성</span>
        </div>
        <div id="divSearch">
            <input id="txtSearchWord" type="text" style="width:90px" />
        </div>
        <!--북마크섹션 시작-->
        <input id="hdfCatId" type="hidden" />
        <div class="mainCategory">
            <?php
                $showSubMenuCSS = " style=\"display:block;\"";

                foreach($categoryNodes as $category)
                {
                    if((Get($category, "PUBLIC_YN") == $public_yn or $public_yn == "") and Get($category, "USE_YN") == "Y")
                    {
                        //카테고리
                        $category_idx = Get($category, "CATEGORY_IDX");
                        $category_id = "cat_" . $category_idx;
                        $category_name = Get($category, "CATEGORY_NAME");

                        echo "<div class=\"subCategory\">";
                        echo "<span class=\"title\" onclick=\"ToggleDisplayProp('" . $category_id . "');\">" . $category_name . "</span>";

                        //사이트 목록
                        echo "<ul id=\"" . $category_id . "\"" . $showSubMenuCSS . " >";
                        foreach($siteNodes as $site)
                        {
                            if(Get($site, "CATEGORY_IDX") == $category_idx and Get($site, "USE_YN") == "Y")
                            {
                                $site_idx = Get($site, "SITE_IDX");
                                $site_id = "site_" . Get($site, "SITE_IDX");
                                $site_name = Get($site, "SITE_NAME");
                                $site_url = Get($site, "SITE_URL");
                                $site_url_mobile = Get($site, "SITE_URL_MOBILE");
                                $siteClickScript = "MovePage('" . $site_url_mobile . "', '" . $site_url . "');";

                                echo "<li>";
                                echo "<span id=\"" . $site_id . "\" onclick=\"" . $siteClickScript . "\" style=\"margin-right:20px\">" . $site_name . "</span>";
                                
                                if($public_yn == "")
                                {
                                    $modClickScript = "GetSiteInfo('" . $site_idx . "');";
                                    echo "<span style=\"cursor:pointer; margin-right:20px;\" onclick=\"" . $modClickScript . "\" >[수정]</span>";
                                }

                                echo "</li>";
                            }
                            
                        }
                        echo "</ul>";

                        echo "</div>";

                        $showSubMenuCSS = "";
                    }
                }
            ?>
        </div>
        <!--북마크섹션 끝-->
    </div>
    <div id="divCategory">
        <span class="title">이름</span><input id="txtCategoryName" type="text" class="textbox" />
        <span class="title">순서</span><input id="txtCategorySort" type="text" class="textbox" />
    </div>
    <div id="divEditSite">
        <table style="width:100%;">
            <tr>
                <td style="width:140px">
                    <span class="title">카테고리</span>
                </td>
                <td>
                    <select name="ddlCategory" id="ddlCategory">
                        <?php
                            foreach($categoryNodes as $category)
                            {
                                if((Get($category, "PUBLIC_YN") == $public_yn or $public_yn == "") and Get($category, "USE_YN") == "Y")
                                {
                                    $category_idx = Get($category, "CATEGORY_IDX");
                                    $category_name = Get($category, "CATEGORY_NAME");
                                    echo "<option value=\"" . $category_idx . "\">" . $category_name . "</option>";
                                }
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="title">ID</span>
                </td>
                <td>
                    <input id="txtSiteIdx" type="text" class="textbox disabled" readonly="readonly" />
                </td>
            </tr>
            <tr>
                <td>
                    <span class="title">이름</span>
                </td>
                <td>
                    <input id="txtSiteName" type="text" class="textbox" />
                </td>
            </tr>
            <tr>
                <td>
                    <span class="title">URL</span>
                </td>
                <td>
                    <input id="txtSiteUrl" type="text" class="textbox" />
                </td>
            </tr>
            <tr>
                <td>
                    <span class="title">URL MOBILE</span>
                </td>
                <td>
                    <input id="txtSiteUrlMobile" type="text" class="textbox" />
                </td>
            </tr>
            <tr>
                <td>
                    <span class="title">순서</span>
                </td>
                <td>
                    <input id="txtSiteSort" type="text" class="textbox" />
                </td>
            </tr>
            <tr>
                <td>
                    <span class="title">사용</span>
                </td>
                <td>
                    <select id="ddlUseYN">
                        <option value="Y" selected="selected">사용</option>
                        <option value="N">사용안함</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align:right">
                    <span style="cursor:pointer;" onclick="AddSiteInfo()">[신규]</span>
                    <span style="cursor:pointer;" onclick="SaveSiteInfo()">[저장]</span>
                    <span style="cursor:pointer" onclick="HideEditSiteArea()">[닫기]</span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>