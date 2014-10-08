<?php
/**************************************************************************************************
* THƯ VIỆN PHÂN TRANG
*==================================================================================================
* CREATE BY : hungnd88@appota.com
* TIME CREATE : 25/03/2013
* *************************************************************************************************
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Pagin_customize
{
    function pagin_customize()
    {
    }
    public function paging($page, $totalRecord, $limit, $segment, $link, $ajax=false, $urlx='') {
        $totalPage = ceil($totalRecord/$limit);
        $paging ='';
        if (!$segment) {
            $segment = 2;
        }
        if ($totalPage>1){
            if ($page > 1) {
                $previous = $page-1;
                if ($ajax)
                {
                    $paging .="<li><a href="."javascript:manager('$urlx/1','0','add-html')>FIRST</a></li>";
                    $paging .="<li><a href="."javascript:manager('$urlx/$previous','0','add-html')><i class='fa fa-chevron-left'></i></a></li>";
                }
                else
                {
                    $paging .="<li> <a href=" . $link . "/1> First </a></li> ";
                    $paging .="<li> <a href=" . $link . "/".$previous."> Previous </a></li> ";
                }
            }

            if ($page <= $segment) {
                if ($segment + $page <=$totalPage){
                    for ($i = 1; $i <= ($segment+$page); $i++) {
                        if ($page == $i) {
                            if ($ajax) $paging .= "<li class='active'><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                            else $paging .= "<li class='active'><a href=" . $link . "/" . $i . ">". $i ."</a></li>";
                        } else {
                            if ($ajax) $paging .="<li><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                            else $paging .=" <li><a href=" . $link . "/" . $i . ">" . $i . "</a></li>";
                        }
                    }
                } else{
                    for ($i = 1; $i <= $totalPage; $i++) {
                        if ($page == $i) {
                            if ($ajax) $paging .= "<li class='active'><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                            else $paging .= "<li class='active'><a href=" . $link . "/" . $i . ">". $i ."</a></li>";
                        } else {
                            if ($ajax) $paging .="<li><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                            else $paging .=" <li><a href=". $link . "/" . $i . ">" . $i . "</a></li>";
                        }
                    }
                }
            }
            elseif ($page > ($totalPage-$segment)) {
                for ($i = ($totalPage-$segment); $i <= $totalPage; $i++) {
                    if ($page == $i) {
                        if ($ajax) $paging .= "<li class='active'><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                        else $paging .= "<li class='active'> <a href=" . $link . "/" . $i . ">". $i ."</a></li>";
                    } else {
                        if ($ajax) $paging .="<li><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                        else $paging .=" <li><a href=" . $link . "/" . $i . ">" . $i . "</a></li>";
                    }
                }
            } else {
                for ($i = ($page-$segment); $i <=($page+$segment); $i++) {
                    if ($page == $i) {
                        if ($ajax) $paging .= "<li class='active'><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                        else $paging .= "<li class='active'> <a href=" . $link . "/" . $i . ">". $i ."</a></li>";
                    } else {
                        if ($ajax) $paging .="<li><a href="."javascript:manager('$urlx/$i','0','add-html')>$i</a></li>";
                        else $paging .=" <li><a href=" . $link . "/" . $i . ">" . $i . "</a></li>";
                    }
                }
            }
            if ($page < $totalPage){
                $next = $page+1;
                if ($ajax)
                {
                    $paging .="<li><a href="."javascript:manager('$urlx/$next','1','add-html')><i class='fa fa-chevron-right'></i></a></li>";
                    $paging .="<li><a href="."javascript:manager('$urlx/$totalPage','1','add-html')>LAST</a></li>";
                }
                else
                {
                    $paging .=" <li><a href=" . $link . "/".$next."> Next </a></li>";
                    $paging .=" <li><a href=" . $link . "/".$totalPage."> Last </a></li>";
                }
            }
            return $paging;
        } else {
            $paging = '';
            return $paging;
        }

    }
}

/* End of file pagin.php */
/* Location: ./application/libraries/pagin.php */
?>