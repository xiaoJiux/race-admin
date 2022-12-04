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

function fail($msg='上传失败')
{
	return json_encode(['state'=>'fail','msg'=>$msg]);
}

function getError($errorNo)
{
	switch ($errorNo)
	{
		case 1:
			return '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！';
			break;
		case 2:
			return '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！';
			break;
		case 3:
			return '文件只有部分被上传！';
			break;
		case 4:
			return '没有文件被上传！';
			break;
		case 6:
			return '找不到临时文件夹！';
			break;
		case 7:
			return '文件写入失败！';
			break;
		default:
			return '未知上传错误！';
	}
}

$action=(isset($_GET['action']))?$_GET['action']:'';

$file=$_FILES['file'];
if(!$file)
{
	echo fail('没有文件上传');
	return;
}
if($file['error'])
{
	echo fail(getError($file['error']));
	return;
}
if(!file_exists($file['tmp_name']))
{
	echo fail('找不到临时文件');
	return;
}
if(!is_uploaded_file($file['tmp_name']))
{
	echo fail('非法上传');
	return;
}

$oldname=$file['name'];

$ext=strtolower(strrchr($oldname,'.'));

#文件大小
$filesize=$file['size'];

#检查文件大小，默认为5M
if($filesize>5*1024*1024)
{
	echo fail('文件超出大小限制');
	return;
}

#检查文件类型
/*
可以根据action的值判断上传来源
action=image为图片
action=music为.mp3或.m4a的音频
action=video为.mp4视频
action=all为所有类型
*/
if(!in_array($ext,[".gif",".jpg",".jpeg",".png",".mp3",".m4a",".mp4",".doc",".docx",".rar",".zip",".ppt",".pptx",".xls",".xlsx"]))
{
	echo fail('文件类型错误');
	return;
}

$filename='../upload/'.time().mt_rand(1000,9999).$ext;
		
$result=move_uploaded_file($file['tmp_name'],$filename);
if($result)
{
	echo json_encode(['state'=>'success','msg'=>$filename,'name'=>$oldname]);
}
else
{
	echo fail();
}