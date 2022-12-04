<?php
/**
 * 作用：SDCMS富媒体编辑器
 * 官网：Https://www.sdcms.cn
 * 作者：IT平民
 * ===========================================================================
 * 您可以使用本软件用于商业目的，但是您必须保留SDCMS的版权，并且确保代码开源；
 * 未经授权不允许对本程序代码以任何形式任何目的的再发布。
 * ===========================================================================
 
 说明：本文件仅供本地开发测试，请勿用于实际运营，原因：未做任何安全过滤处理（白猫、黑猫请绕道）。
 说明：本文件仅供本地开发测试，请勿用于实际运营，原因：未做任何安全过滤处理（白猫、黑猫请绕道）。
 说明：本文件仅供本地开发测试，请勿用于实际运营，原因：未做任何安全过滤处理（白猫、黑猫请绕道）。
 
 如果需要过滤方法，请至sdcms官网或用户QQ群交流，我们可提供解决方案。
**/


#开启错误提示（On：开启，Off：关闭）
ini_set('display_errors','On');

#错误提示类型，（0：关闭，E_ALL：全部显示）
error_reporting(E_ALL);

#设置编码
header("Content-Type:text/html;charset=utf-8");

function get_all_picurl($a,$b='')
{
	#去掉反斜杠
	$a=stripslashes($a);
	$num=preg_match_all('/<img.*?src="(.*?)".*?>/is',$a,$match);
	if($num)
	{
		$d=[];
		for($i=0;$i<$num;$i++)
		{
			if(empty($b))
			{
				$d[$i]=$match[1][$i];
			}
			else
			{
				if(!strpos($match[1][$i],$b) && strpos($match[1][$i],"://"))
				{
					$d[$i]=$match[1][$i];
				}
			}
			
		}
		return array_unique($d);
	}
	else
	{
		return '';
	}
}

function saveRemote($url)
{
	$info=['state'=>'错误','url'=>'','file'=>[]];
	$url=str_replace('&amp;','&',$url);
	if(strpos($url,'http')!==0)
	{
		$info['state']='链接不是http链接';
		return $info;
	}
	preg_match('/(^https*:\/\/[^:\/]+)/', $url, $matches);
	$host_with_protocol=count($matches)>1? $matches[1]:'';

	#判断是否是合法 url
	if(!filter_var($host_with_protocol, FILTER_VALIDATE_URL))
	{
		$info['state']='非法URL';
		return $info;
	}

	preg_match('/^https*:\/\/(.+)/',$host_with_protocol,$matches);
	$host_without_protocol=count($matches)>1?$matches[1]:'';

	#此时提取出来的可能是 ip 也有可能是域名，先获取 ip
	$ip=gethostbyname($host_without_protocol);
	#判断是否是私有 ip
	if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE))
	{
		$info['state']='非法IP';
		return $info;
	}
	#获取请求头并检测死链
	$heads=get_headers($url, 1);
	if (!(stristr($heads[0], "200") && stristr($heads[0], "OK")))
	{
		$info['state']='链接不可用';
		return $info;
	}
	#格式验证(扩展名验证和Content-Type验证)
	if(isset($heads['Content-Type']))
	{
		switch($heads['Content-Type'])
		{
			case 'image/gif':
				$ext='.gif';
				break;
			case 'image/jpeg':
				$ext='.jpg';
				break;
			case 'image/png':
				$ext='.png';
				break;
			case 'image/bmp':
				$ext='.bmp';
				break;
			default:
				$info['state']='非法图片';
				return $info;
				break;
		 }
	}
	if(!in_array($ext,[".png",".jpg",".jpeg",".gif",".bmp"]) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image"))
	{
		$info['state']='链接contentType不正确';
		return $info;
	}
	#打开输出缓冲区并获取远程图片
	ob_start();
	$context=stream_context_create(array('http'=>array('follow_location'=>false)));
	readfile($url,false,$context);
	$img=ob_get_contents();
	ob_end_clean();
	preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/",$url,$m);
	/*if(check_bad($img)>0)
	{
		$info['state']='非法图像文件';
		return $info;
	}*/
	$size=strlen($img);
	if($size>5*1024*1024)
	{
		$info['state']='文件大小超出网站限制';
		return $info;
	}
	$filepath='../upload/';
	$newname=time().mt_rand(100,999).$ext;
	if(!(file_put_contents($filepath.$newname, $img) && file_exists($filepath.$newname)))
	{
		$info['state']='移动失败';
	}
	else
	{
		$info['state']='SUCCESS';
		$info['url']=$filepath.$newname;
	}
	return $info;
}

$a='';
if(isset($_POST['content']))
{
	$a=$_POST['content'];
}
if(empty($a))
{
	echo '';
	exit;
}
#去掉反斜杠
if(!get_magic_quotes_gpc())
{
	$a=stripslashes($a);
}
list($host)=explode(':',$_SERVER['HTTP_HOST']);
$d=get_all_picurl($a,$host);
if(is_array($d))
{
	foreach($d as $key=>$val)
	{
		$info=saveRemote($val);
		if($info['state']=='SUCCESS')
		{
			$a=str_replace($val,$info['url'],$a);
		}
		/*
		else
		{
			$a=$info['state'];
		}
		*/
	}
}
echo $a;