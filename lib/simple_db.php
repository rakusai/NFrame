<?php
/**
 * simple_db.php
 *
 * SQL文のデータベースラッパー
 * 
 * Created by Isshu Rakusai
 * Copyright (c) 2013 Nota Inc. All rights reserved.
 */ 

/**
 * 簡単な使い方
 * SELECT文 -> select_db_rows()
 *   最初の一行だけ取得 -> get_db_row()
 * UPDATE文 -> update_db_rows()
 * INSERT文 -> insert_db_rows()
 * DELETE文 -> delete_db_rows()
 */
/**
 * 設定する項目 別の場所で
  define("DB_SERVER", "db.com");
  define("DB_NAME", "");
  define("DB_USER", "");
  define("DB_PASS", "");
  で必要な情報を定義した後、上記4つの関数を呼んで利用できます。
 */


//特殊なSQLの値
define("SQL_NOW", "==9345-5643-2354==SQL::NOW()==9345-5643-2354=="); 
define("SQL_COUNTUP", "==9345-5643-2354==SQL::SQL_COUNTUP()==9345-5643-2354=="); 
define("SQL_COUNTDOWN", "==9345-5643-2354==SQL::COUNTDOWN()==9345-5643-2354=="); 

/**
 * getdb() -- 初回実行時に初期化を行いデータベース変数を返す
 * この関数を外部から呼ぶ必要はありません
 * 返り値 -- MySQL リンク  
 */

function getdb()
{
    global $DB;
    if ($DB) {
        return $DB;
    } else {
        $DB = mysql_connect(DB_SERVER, DB_USER, DB_PASS)
        or die('Could not connect: ' . mysql_error());
        mysql_select_db(DB_NAME, $DB) or die('Could not select database');
        $query = 'SET NAMES UTF8';
        $result = mysql_query($query, $DB) or die('Query failed: ' . mysql_error());
        return $DB;
    } 
} 

/**
 * closedb() -- データベースを切断する
 * この関数を外部から呼ぶ必要はありません
 */
function closedb()
{
    global $DB;
    if ($DB) {
        mysql_close($DB);
        $DB = false;
    } 
} 

/**
 * select_db_rows($table,$column,$filters,$sorts,$start,$limit,$groups=null, $join=null) -- DBから一定の条件で行を返す
 * $table -- テーブル名
 * $column -- 取得するカンマ区切りのコラム
 * $filters -- array 抽出条件（WHERE節）
 * $sort -- array 並べ替え条件（ORDER節）
 * $start -- 開始番号
 * $limit -- 出力数
 *
 * 戻り値 -- 条件にマッチすれば配列を返す。マッチしなければ空の配列を返す
 * 
 * サンプル
 * 
 * //select_db_rows("board","*",NULL,array("remixed_count DESC"),0,50);
 * //select_db_rows("board","*",array("author"=>"test"),array("remixed_count"),0,50);
 * //select_db_rows("board","*",array("date >"=>"2013-02-20 00:00:00"),array("remixed_count"),0,50);
 * //$join = array("board" => "author", "card" => "author");
 * //$groups = select_db_rows("board","*",$filters,array("updated_at DESC"),0,7,array("board.author"),$join);
 */
function select_db_rows($table, $column, $filters, $sorts, $start, $limit, $groups=null, $join=null)
{

    $db = getdb();
    
    $filterby = get_filter_sql($filters);
    if ($filterby === false){
        // Filterできない（IN節で空配列など）が、全てにマッチしないようにする
        return array();
    }

    $sortby = "";
    if ($sorts) {
        if (is_array($sorts)){
            for($x = 0;$x < count($sorts);$x++) {
                if ($sortby != "")$sortby .= ",";
                $sortby .= $sorts[$x];
            } 
        }else if(is_string($sorts)){
            $sortby = $sorts;
        }
        if ($sortby != "")$sortby = " ORDER BY " . $sortby;
    } 

    $joinon = "";
    if ($join) {
        $joinon = " LEFT JOIN ";
        foreach($join as $key => $val){
            if ($key != $table){
                $joinon .= $key . " ON ";
            }
        }
        $i=0;
        foreach($join as $key => $val){
            $joinon .= $key . ".". $val;
            if ($i++ < 1){ $joinon .= "="; }
        }
        //ambiguousの解決
        $columns = explode(",",$column);
        array_walk($columns,'add_tbl_name',$table);
        $column = implode(",",$columns);
    }
    $groupby = "";
    if ($groups) {
        if (is_array($groups)){
            for($x = 0;$x < count($groups);$x++) {
                if ($groupby != "")$groupby .= ",";
                $groupby .= $groups[$x];
                if ($joinon == "" && $x == 0){
                    $column .= ', COUNT(' . $groups[$x] . ') AS ' . $groups[$x] . "_count";
                }
            } 
        }else if(is_string($groups)){
            $groupby = $groups;
        }
        if ($groupby != "")$groupby = " GROUP BY " . $groupby;
    } 
    
    
    $sql = "SELECT " . $column . " FROM " . $table;
    $sql .= $joinon;
    $sql .= $filterby;
    $sql .= $groupby;
    $sql .= $sortby;
    if ((int)$limit > 0){ $sql .= " LIMIT " . (int)$limit;  }
    if ((int)$start > 0){ $sql .= " OFFSET " . (int)$start; }
    
    $result = mysql_query($sql, $db) or die('SQL:' . $sql  .'Query failed: ' . mysql_error());
    $rows = array();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rows[] = $row;
    } 
    return $rows;
} 

/**
 * get_db_row($table,$filters,$sorts) -- DBからマッチした条件の先頭行だけ返す
 * $table -- テーブル名
 * $filters -- array 抽出条件（WHERE節）
 * $sort -- array 並べ替え条件（ORDER節）
 *
 * 戻り値 -- 条件にマッチすれば辞書を返す。マッチしなければfalseを返す。
 * 
 * サンプル
 * 
 * //get_db_row("board",array("author"=>3));
 * //get_db_row("board",array("author"=>"test"),array("remixed_count DESC"));
 */
function get_db_row($table, $filters, $sorts=null)
{

    $rows = select_db_rows($table,"*", $filters, $sorts, 0, 1);
    if ($rows){
        return $rows[0];
    }
    return false;
}

/**
 * select_db_rows_by_sql($sql) -- DBから直接SQL文で一定の条件で行を返す
 * $sql -- SQL文
 * 
 * 戻り値 -- 条件にマッチすれば配列を返す。マッチしなければ空の配列を返す
 * 
 */
function select_db_rows_by_sql($sql)
{
    $db = getdb();
	$sql = "SELECT " . $sql;
	$result = mysql_query($sql, $db) or die('SQL:' . $sql  .'Query failed: ' . mysql_error());
	$rows = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$rows[] = $row;
	} 
    
    return $rows;

}


function add_tbl_name(&$val, $key, $prefix)
{
	if (strpos($val,'.') === FALSE){
    	$val = $prefix . ".".$val;
    }
}

/**
 * count_db_rows($table,$column,$filters) -- DBから一定の条件で行の合計数を返す
 * $table -- テーブル名
 * $filters -- array 抽出条件（WHERE節）
 * 
 * 戻り値 -- 条件にマッチすれば数字を返す。マッチしなければ0を返す
 * 
 * サンプル
 * 
 * //count_db_rows("直接",array(FILTER_AUTHOR=>"test"));
 */
function count_db_rows($table, $filters, $groups=null)
{
    $db = getdb();
    $filterby = get_filter_sql($filters);
    if ($filterby === false){
        // Filterできない（IN節で空配列など）が、全てにマッチしないようにする
        return 0;
    }
    $groupby = "";
    if ($groups) {
        if (is_array($groups)){
            $groupby = implode(",",$groups);
        }else if(is_string($groups)){
            $groupby = $groups;
        }
        if ($groupby != "")$groupby = " GROUP BY " . $groupby;
    } 

    $sql = "SELECT COUNT(*) AS count FROM " . $table;
    $sql .= $filterby;
    $sql .= $groupby;
    $result = mysql_query($sql, $db) or die('Query failed: ' . mysql_error());
    $count = 0;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $count = $row["count"];
    } 
    return $count;
} 


/**
 * update_db_rows($table, $filters, $row_data) -- DBを更新する
 * $table -- テーブル名
 * $filters -- array 抽出条件（WHERE節）
 * $row_data -- array 保存するデータの連想配列
 * 
 * 戻り値 -- mysql_queryの戻り値を返す
 *
 */
function update_db_rows($table, $filters, $row_data)
{
    $db = getdb();
    $update = "";
    reset($row_data);
    while (list($key, $val) = each($row_data)) {
        if ($update != "")$update .= ",";
        if ($key == "password"){ //パスワードキー
            $update .= "`" . $key . "`= '" . md5($val) . "'";
        }else if ($val === SQL_NOW) {
           $update .= "`" . $key . "`= " . "CURRENT_TIMESTAMP ";
        }else if ($val === SQL_COUNTUP) {
           $update .= "`" . $key . "`= " . $key."+1 ";
        }else if ($val === SQL_COUNTDOWN) {
           $update .= "`" . $key . "`= " . $key."-1 ";
        }else{
            $update .= "`" . $key . "`= '" . mysql_real_escape_string($val) . "'";
        }
    } 
    $filterby = get_filter_sql($filters);
    if ($filterby === false){
        // Filterできない（IN節で空配列など）が、全てにマッチしないようにする
        return false;
    }

    $sql = "UPDATE " . $table . " SET "; //更新日
    $sql .= $update;
    $sql .= $filterby;
    $result = mysql_query($sql, $db) or die('Query failed: ' . mysql_error());
    
    return $result;
}


/**
 * insert_db_rows($table, $row_data) -- DBに行を追加する
 * $table -- テーブル名
 * $row_data -- array 挿入するデータの連想配列
 * 
 * 戻り値 -- 挿入した行のidを返す。
 */
function insert_db_rows($table, $row_data)
{
    $db = getdb(); 
    $sql = "INSERT INTO " . $table . " (";
    
    $i = 0;
    foreach ($row_data as $key => $val){
        if ($i > 0) $sql .= ",";
        $sql .= $key;
        $i++;
    }
    $sql .= ") VALUES (";
    
    $i = 0;
    foreach ($row_data as $key => $val){
        if ($i > 0) $sql .= ",";
        if ($key == "password"){ //パスワードキー
            $sql .= "'" . md5($val) . "'";
        }else if ($val === SQL_NOW) {
            $sql .= "CURRENT_TIMESTAMP ";
        }else{
            $sql .= "'" . mysql_real_escape_string($val) . "'";
        }
        $i++;
    } 
    $sql .= ");";
    
    $result = mysql_query($sql, $db) or die('Query failed: ' . mysql_error(). "|".$sql); 
    $id = mysql_insert_id();

    return $id;
} 

/**
 * delete_db_rows($table, $filters) -- DBの行を削除する
 * $table -- テーブル名
 * $filters -- array  抽出条件（WHERE節）
 *
 * 戻り値 -- mysql_queryの戻り値を返す
 * 
 */
function delete_db_rows($table, $filters)
{
    $db = getdb();

    $filterby = get_filter_sql($filters);
    if ($filterby === false){
        // Filterできない（IN節で空配列など）が、全てにマッチしないようにする
        return false;
    }

    $sql = "DELETE FROM " . $table;
    $sql .= $filterby;
    $result = mysql_query($sql, $db) or die('Query failed: ' . mysql_error());

    return $result;
}

/**
 *
 * my_real_escape_string($val) -- mysqlエスケープしてシングルクオートで囲む。
 * この関数は外部から呼ぶことはありません。
 *
 */
function my_real_escape_string($val)
{
    return "'" . mysql_real_escape_string($val) . "'";
}

/**
 *
 * get_filter_sql($filters) -- 配列からSQLのWHERE文を生成。
 * この関数は外部から呼ぶことはありません。
 *
 */
function get_filter_sql($filters)
{
    $filterby = "";
    if ($filters) {
        foreach ($filters as $label => $val){
            if ($filterby != "")$filterby .= " AND ";
            if ($filterby == "")$filterby = " WHERE ";
            if (is_array($val)){
                if (count($val) <=0){
                    return false;
                }
                $filterby .= $label . " IN (";
                $filterby .= implode(",",array_map("my_real_escape_string",$val));
                $filterby .= ")";
            }else if(preg_match("/\s*<>$/",$label,$match)){
                $filterby .= substr($label,0,strlen($label)-strlen($match[0])) . " <> '" . mysql_real_escape_string($val) . "'";
            }else if(preg_match("/\s*(<|>)$/",$label,$match)){
                $filterby .= substr($label,0,strlen($label)-strlen($match[0])) . " ".$match[1]." '" . mysql_real_escape_string($val) . "'";
            }else{
              if($label==FILTER_CREATE_START){
                  $filterby .= FILTER_CREATE . " >= '" . mysql_real_escape_string($val) . "'";
              }else if($label==FILTER_CREATE_END){
                  $filterby .= FILTER_CREATE . " < '" . mysql_real_escape_string($val) . "'";
              }else if($label==FILTER_UPDATE_START){
                  $filterby .= FILTER_UPDATE . " >= '" . mysql_real_escape_string($val) . "'";
              }else if($label==FILTER_UPDATE_END){
                  $filterby .= FILTER_UPDATE . " < '" . mysql_real_escape_string($val) . "'";
              }else {
                  $filterby .= $label . " = '" . mysql_real_escape_string($val) . "'";
              }

            }
        } 
    } 
    return $filterby;
}



/**
 * DB配列を、基準となる配列を元に並べ替える。
 * $targetarray - 並べ替えたい配列
 * $basearray - 元になる配列
 * $key_column - 共通のカラム名
 * $sort - 並べ替え順 デフォルトはSORT_ASC
 *
 */

function db_array_multisort(&$targetarray, &$basearray, $key_column, $sort)
{

	$base_order = array();
	$new_order = array();

	$i = 0;
	foreach($basearray as $val){
		$base_order[$val[$key_column]] = $i;
		$i++;
	}
	
	foreach($targetarray as $key => &$val){
		$new_order[$key] = $base_order[$val[$key_column]];
	}
	
	//targetarrayをnew_orderの順に並び替える
	array_multisort($new_order,$sort,$targetarray);
	
	return true;
	
}

/**
 *
 * DBの結果の2重配列から、特定のコラムのデータにマッチする行のデータを返す
 *
 */

function search_db_rows(&$rows,$column, $data){
	foreach($rows as $row){
		if ($row[$column] == $data){
			return $row;
		}
	}
	return false;
}

