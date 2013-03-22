<?php
/**
 * action_controller.php
 *
 * ウェブアプリフレームワークのコントローラーレベルの関数群
 * 
 * Created by Isshu Rakusai
 * Copyright (c) 2013 Nota Inc. All rights reserved.
 */ 

/*
 * HTMLファイルに変数を渡して出力
 */ 
function render_html($template_path,$dictionary)
{
    // 変数をこの関数内のスコープに展開
    extract($dictionary);
    extract($GLOBALS);
    
    // 常に含める変数をここで定義
    // global $login_user;
    
    // HTMLを出力
    require(TEMPLATE_DIR . $template_path);

}

/*
 * JSONを出力する
 */ 
function render_json($obj, $callback = null)
{
	$js = json_encode($obj);
	$js = preg_replace("/[\r\n]/","",$js);
	
	header("Content-Type:text/javascript; charset=utf8");
	if (isset($callback) && $callback != ""){
		print "$callback(" . $js . ");";
	}else{
		print $js;
	}

}

/*
 * エラーをHTMLで出力する
 */ 
function render_error($msg, $status=500)
{
    $params = array("msg"=>$msg);

    header("HTTP/1.0 $status");
    render_html("index/error.html",$params);
}

?>