<?php
/**
 * [Naming conventions]
 * function : camelCase  (Make the beginning of a word a verb / 単語の先頭を動詞にする)
 * method   : camelCase  (Make the beginning of a word a verb / 単語の先頭を動詞にする)
 * Class    : PascalCase (Make the beginning of a word a noun / 単語の先頭を名詞にする)
 * Variable : PascalCase (Make the beginning of a word a noun / 単語の先頭を名詞にする)
 * DEFINE   : UPPERCASE_SNAKECASE
 * 
 * [Comment]
 * don't write comments (コメントは書かない)
 * 
 * [Document]
 * Write all the usage of classes and functions in the document (クラスや関数の使い方は全てドキュメントに記入)
 * 
 * [Indent]
 * Indent is for two characters (インデントは半角スペース2文字分)
 * 
 * [Argument]
 * If there are multiple arguments, put a single space after the delimited comma (引数が複数ある場合は区切りのカンマの後に半角スペースを1つ挟む)
 * 
 * [Server]
 * PHP 7.0.12
 * CentOS release 6.8
 * 
 * [information]
 * 2016 Fukumoto
 * E-mail : bokotomo@me.com
 */

use Saya\MainControllor;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot;
use \LINE\LINEBot\Constant\HTTPHeader;

require_once(__DIR__."/config.php");
require_once(__DIR__."/vendor/autoload.php");
require_once(__DIR__."/library/tomolib/autoload.php");
require_once(__DIR__."/library/sayalib/autoload.php");

$InputData = file_get_contents("php://input");
$HttpClient = new CurlHTTPClient(ACCESS_TOKEN);
$Bot = new LINEBot($HttpClient, ['channelSecret' => SECRET_TOKEN]);
if(isset($_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE])){
  $Signature = $_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE]; 
  $Events = $Bot->parseEventRequest($InputData, $Signature);

  foreach($Events as $event){
    $MainControllor = new MainControllor($Bot, $event);
    $MainControllor->responseMessage();
  }
}