<?php
/**
 * webapp.php
 *
 * ウェブアプリフレームワークのアプリレベルの関数群
 * 
 * Created by Isshu Rakusai
 * Copyright (c) 2013 Nota Inc. All rights reserved.
 */ 

/**
 * webapp_run($urls) -- URLマッピングと対応する関数を実行 
 *
 * 正規表現で()で囲った部分は、関数の引数として渡します
 * サンプル
   webapp_run(array(
    "/test" => "TestHandler",
    "/user/(.*)" => "UserHandler",
    "/" => "TopHandler",
   ));
 */
function webapp_run($urls) 
{
    $request_uri = $_SERVER["REQUEST_URI"];
    $request_uri = preg_replace("/\?.*$/","",$request_uri);

    $found = false;    
    foreach ($urls as $url_pattern => $func_name){
        // URLパターンが一致すればリクエストハンドラ関数を実行
        // メモ: URLのパターンマッチでは / を多用するため、prefixには#を使用
        if (preg_match("#^".$url_pattern."$#",$request_uri,$matches)){
            if (count($matches) > 1){
                array_shift($matches);
                call_user_func_array($func_name,$matches);
            } else {
                call_user_func($func_name);
            }
            $found = true;
            break;        
        }
    }   
    if (!$found){
        // 一致するパターンがなければ404を出力
        header('HTTP/1.0 404 Not Found');
        print "404 Not Found";
    }
}

?>