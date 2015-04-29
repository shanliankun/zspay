<?php

class ZsPageComponent extends Object {
	
	
	/**
	 * 分页组件
	 *
	 * @param string $mpurl    当前地址 ：如 $mpurl = news/newscontent
	 * @param int $page     当前页
	 * @param int $total    总条数
	 * @param int $pernum   每页显示的条数
	 * @param 1.普通分页，2.搜索条数过多时候最大支持1000条
	 * @return 分页的html代码
	 */
	function setMulitpage($url, $currentpage=1, $total, $perpage)
	{
		$page_num_address = $url;
		$page = ceil($total/$perpage);
		$page_str = '';//分页结果
		if(intval($total)>0)
		{
			//$first_page = '首页';
			$pre_page = '上一页';
			$next_page = '下一页';
			//$last_page = '末尾页';
			$jump = 5 ;//跨度
			
			$url_a = parse_url($url);
			$path = $url_a['path'];
			
			if(isset($url_a['query']))
			{//echo "存在";
				$query = $url_a['query'];//debug($query);
				//echo $query;exit();
				if(strpos($query,'page') !== false)
				{
					$query = strstr($query,'page',true);
				}else{
					$query = $query."&";
				}

				if(strpos($query,'pageSize') !== false)
				{
					$query2 = strstr($query,'pageSize',true);
				}else{
					$query2 = $query;
				}

				//debug($query);

			}else{
				//echo '不存在';
				$query = '';
				$query2 = '';
			}
			//debug("条件：".$query);

			$page_str .= "<div class='c-pag'>";
			//新增显示多少页start
			if( isset($_COOKIE['pageSize']) && empty($_GET['pageSize'])){
				$perpage = $_COOKIE['pageSize'];
			}
			$page10 = $page20 = $page50 = $pageSize = '';
			if( $perpage == 20){
				$page20 = 'selected';
				$pageSize = 'pageSize=20&';
			}elseif ( $perpage == 50){
				$page50 = 'selected';
				$pageSize = 'pageSize=50&';
			}else{
				$page10 = 'selected';
				$pageSize = 'pageSize=10&';
			}
			$page_str .= "<div class='c-mq'> <span class='c-other' style='margin-right: 10px;'>每页显示:</span> ";
			$page_str .= "<select class='selecttag input_ar_out' onchange='window.location=options[(this.selectedIndex)].value;document.cookie=\"pageSize=\"+options[(this.selectedIndex)].value.substr(-2)+\";path=/\";' value='10' name='pageSize' style='width:50px; margin-right: 10px;' >
<option ".$page10." value='".$page_num_address."&pageSize=10'>10</option>
<option ".$page20." value='".$page_num_address."&pageSize=20'>20</option>
<option ".$page50." value='".$page_num_address."&pageSize=50'>50</option>
</select>";
			$page_str .= "</div>";
			//新增显示多少页end

			$page_str .= " <ul class='c-f'>";
			$pre_page = ($currentpage-1) ? ($currentpage-1):1;
			if($currentpage != 1)
			{
				$page_str .= '<li class="c-next"><a href="'.$path.'?'.$query.$pageSize.'page='.$pre_page.'" >上一页</a></li>';
			}
			if($page<=$jump)
			{

				for($i=1;$i<=$page;$i++)
				{

					if($i == $currentpage)
					{
						$page_str .= '<li class="c-number bh-on"><a href="'.$path.'?'.$query.$pageSize.'page='.$i.'"';
						$page_str .= ' class="bh-on"';
					}else{
						$page_str .= '<li class="c-number"><a href="'.$path.'?'.$query.$pageSize.'page='.$i.'"';

					}
					$page_str .= '>'.$i.'</a></li>';
				}


			}


			if($page>$jump)
			{


				$total_jump_num = ceil($page/$jump); //一共有几个跨度
				$now_jump_num = ceil($currentpage/$jump);//当前页处在的跨度


				if($currentpage>=3)
				{
					for($i=-2;$i<3;$i++)
					{
						if(($currentpage+2) > $page)
						{
							$page_now = $page-2+$i	;

						}else{
							$page_now = $currentpage+$i;
						}
						if($page_now <= $page)
						{

							if($page_now == $currentpage)
							{
								$page_str .= '<li class="c-number bh-on"><a href="'.$path.'?'.$query.$pageSize.'page='.$page_now.'"';
								$page_str .= ' class="bh-on"';
							}else{
								$page_str .= '<li class="c-number"><a href="'.$path.'?'.$query.$pageSize.'page='.$page_now.'"';
							}
							$page_str .= '>'.$page_now.'</a>';
						}
					}

				}else{
					for($i=1;$i<$jump+1;$i++)
					{
						$page_now = ($now_jump_num-1)*$jump+$i;
						if($page_now <= $page)
						{

							if($page_now == $currentpage)
							{
								$page_str .= '<li class="c-number bh-on"><a href="'.$path.'?'.$query.$pageSize.'page='.$page_now.'"';
								$page_str .= ' class="bh-on"';
							}else{
								$page_str .= '<li class="c-number"><a href="'.$path.'?'.$query.$pageSize.'page='.$page_now.'"';
							}
							$page_str .= '>'.$page_now.'</a></li>';
						}
					}
				}

			}
			if($currentpage+1<=$page)
			{
				$page_str .= '<li class="c-next"><a href="'.$path.'?'.$query.$pageSize.'page='.($currentpage+1).'"  target="_parent" >下一页</a></li>';
			}
			$page_str .= "</ul>";
			$page_str .= "<div class='c-mq'> <span class='c-other'>共".$page."页</span> <span class='c-other'>到第</span> <span class='c-srk'><input type='text' id='page' class='c-sr'/></span> <span class='c-srk'>页</span> </div> <span class='c-button'><a href='javascript:void(0);'  onclick='sub_page()'>确定</a></span> ";

			$page_str .= "</div>";
			$page_str .= "<script type='text/javascript'>
			function sub_page()
			{
				var pagevalue=$('#page').val();
				preg1= new RegExp(/\d+/);
				if(pagevalue.match(preg1)!=pagevalue){
					alert('不合法输入');
					$('#page').val('');
					return false;
				}
				if($('#page').val()>+$page)
				{ 
					alert('超出范围');
					return false;
					location.href='".$path.'?'.$query.$pageSize.'page='.$page."';
				}else
				{
					location.href='".$path."?".$query.$pageSize."page='+$('#page').val();
				}
			}</script>";
		
			return $page_str;

			//echo "分页类：".$page_str;
			$page = array();
			$page['page_str'] = $page_str;
			$page['query'] = $query.$pageSize;
			return $page;
		}
	}

}