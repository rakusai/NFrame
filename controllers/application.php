<?php

//phpinfo();

// ライブラリを読み込む
require_once("../lib/config.php");
require_once("../lib/webapp.php");
require_once("../lib/action_controller.php");
require_once("../lib/simple_db.php");

// コントローラを読み込む
require_once("../controllers/test.php");

// 必要ならここで、DBやセッションの初期化処理
// $login_user = "rakusai";

// URLマッパーから一致する関数を呼び出す
webapp_run(array(
    "/test" => "TestHandler",
    "/" => "IndexHandler",
    "/jsontest" => "JsonTestHandler",
));


?>