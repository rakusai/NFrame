<?php
/**
 * view_utils.php
 * 
 * フレームワークの主にビュー内で使える関数群
 * 
 * Created by Isshu Rakusai
 * Copyright (c) 2013 Nota Inc. All rights reserved.
 */ 

/**
 * template_path($path) -- テンプレート内のフルパスを返す
 *
 * ビュー内で別のビューを呼び出すときに利用できます
 * 利用方法：
 * <? include(template_path("helper/header.html")) ?>
 *
 */
function template_path($path)
{
    return TEMPLATE_DIR.$path;
}

/**
 * h($string) -- HTMLタグをエスケープする
 *
 * 利用方法：
 * <?= h($title) ?>
 * 
 */
function h($string)
{
    return @htmlspecialchars($string, ENT_QUOTES);
}

?>