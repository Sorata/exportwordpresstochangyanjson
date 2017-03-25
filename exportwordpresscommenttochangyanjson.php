<?php
#将本文件放到WordPress根目录，访问一次即可在同目录生成对应的Json。可以直接在畅言后台导入，保留盖楼回复关系。
//取数据库留言输出到搜狐畅言Json格式
include("wp-blog-header.php");
$dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
$db = new PDO($dsn,DB_USER,DB_PASSWORD);
$db->exec("set names utf8");
$rs = $db->query("SELECT distinct(comment_post_ID) FROM `wp_comments`");
if ($db->errorCode() != '00000'){echo join("\t",$db->errorInfo());}
$result = $rs->fetchAll();
$commentArr=array();
$uidarray=array();
foreach($result as $k=>$v){
	$postid=$v['comment_post_ID'];
	$postlink=get_permalink($postid);
	$rs = $db->query("SELECT post_date,guid,post_title FROM `wp_posts` where ID={$postid}");
	$post = $rs->fetchAll();
	$post=$post[0];
	$posttime=strtotime($post['post_date'])*1000;
	$rs = $db->query("SELECT * FROM `wp_comments` where comment_post_ID={$postid}");
	$comments = $rs->fetchAll();
	$tmpc=array();
	foreach($comments as $val){
		$ctime=strtotime($val['comment_date'])*1000;
		if(!isset($uidarray[$val['comment_author']])){$uidarray[$val['comment_author']]=count($uidarray)+1;}
		if($val['comment_parent']==0){$val['comment_parent']='';}
		$tmpc[]=array(
			"cmtid"=>"{$val['comment_ID']}",
			"ctime"=>$ctime,
			"content"=>"{$val['comment_content']}",
			"replyid"=>"{$val['comment_parent']}",
			"user"=>array(
				"userId"=>"{$uidarray[$val['comment_author']]}",
				"nickname"=>"{$val['comment_author']}",
				"usericon"=>"",
				"userurl"=>"",
				"usermetadata"=>array("area"=> "","gender"=> "","kk"=> "","level"=> 1)
			),
			"ip"=>"{$val['comment_author_IP']}",
			"useragent"=>"{$val['comment_agent']}",
			"channeltype"=>"1",
			"from"=>"",
			"spcount"=>"",
			"opcount"=>"",
			"attachment"=>array(0=>array( "type"=>"1", "desc"=>"","url"=>""))
		);
	}
	$commentArr[]=array(
		"title"=>"{$post['post_title']}",
		"url"=>"{$postlink}",
		"ttime"=>$posttime,
		"sourceid"=>"{$postid}",
		"parentid"=>"",
		"categoryid"=>"",
		"ownerid"=>"1",
		"metadata"=>"",
		"comments"=>$tmpc
	);
}
if(file_exists("changyan.json")){unlink("changyan.json");}
$fp=fopen("changyan.json", "a+");
foreach($commentArr as $v){
fwrite($fp,json_encode($v)."\r\n");
}
fclose($fp);
echo "over";