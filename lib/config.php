<?php
/**
 * config.php
 *
 * ウェブアプリの設定
 * 
 * Created by Isshu Rakusai
 * Copyright (c) 2013 Nota Inc. All rights reserved.
 */ 

define("TEMPLATE_DIR","../views/");

define("DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]."/"); //最後にハイフンをつけること

// データベース関係の接続情報を定義
define("DB_SERVER", "db.com");
define("DB_NAME", "");
define("DB_USER", "");
define("DB_PASS", "");

// グローバル変数の初期化
$DB=false;

// PHP初期化
mb_internal_encoding("UTF-8");
mb_http_output('UTF-8');

//タイムゾーン
date_default_timezone_set("UTC");

?>