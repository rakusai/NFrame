<?php

// テストページ
function TestHandler()
{
    //print $_GET["test"];
    //print "Hello" . $param . " " . $param2 . " | " . $DB2;

    // $users = get_db_row("users",array("id"=>3));
    
    $templates = array(
    "a" => "b",
    "b" => "c",
    );
    
//    render_error("エラーですよ");
    render_html("index/test.html",$templates);
}

// ページ2
function IndexHandler()
{
    print "Success!<br />";
    
    print $_SERVER["DOCUMENT_ROOT"] . "<br />";
    print $_SERVER['HTTP_HOST'] . "<br />";

    //render_error("エラーですよ");

}

// Jsonのテスト
function JsonTestHandler()
{
    $obj = array(
    "test" => "test2",
    "hello" => "world"
    );
    render_json($obj);
}




?>