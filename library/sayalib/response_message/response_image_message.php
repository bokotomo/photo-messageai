<?php
namespace Saya\MessageControllor;

use Saya\MessageControllor;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use TomoLib\UploadFileProvider;
use TomoLib\DatabaseProvider;

class ImageMessageControllor
{
  private $EventData;
  private $Bot;
  private $DatabaseProvider;
  private $UserData;

  public function __construct($Bot, $EventData, $UserData){
    $this->EventData = $EventData;
    $this->Bot = $Bot;
    $this->UserData = $UserData;
    $this->DatabaseProvider = new DatabaseProvider(SQL_TYPE, LOCAL_DATABASE_PATH."/sayadb.sqlite3");
    $this->ImgName = md5($this->UserData["user_id"]."_".$this->DatabaseProvider->getLastAutoIncrement("saya_upload_imgs")).".jpg";
    $this->uploadIMGFile();
  }

  public function uploadIMGFile(){
    $response = $this->Bot->getMessageContent($this->EventData->getMessageId());
    $UploadFileProvider = new UploadFileProvider();
    $FilePath = LOCAL_IMAGES_PATH."/userimg/".$this->ImgName;
    $UploadFileProvider->uploadFileData($FilePath, $response->getRawBody());
  }

  private function chooseCarouselSceneryFilter(){
    $col = new CarouselColumnTemplateBuilder('Good appearance', "景色の見栄えを良くするフィルター", "https://tomo.syo.tokyo/openimg/car.jpg", [
        new PostbackTemplateActionBuilder('決定', "imgtype=appearance&img=".$this->ImgName)
    ]);
    $CarouselColumnTemplates[] = $col;
    
    $col = new CarouselColumnTemplateBuilder('Fantastic', "景色を幻想的にするフィルター", "https://tomo.syo.tokyo/openimg/car.jpg", [
        new PostbackTemplateActionBuilder('決定', "imgtype=fantastic&img=".$this->ImgName)
    ]);
    $CarouselColumnTemplates[] = $col;
    
    $col = new CarouselColumnTemplateBuilder('Pro', "一眼レフカメラフィルター", "https://tomo.syo.tokyo/openimg/car.jpg", [
        new PostbackTemplateActionBuilder('決定', "imgtype=pro&img=".$this->ImgName)
    ]);
    $CarouselColumnTemplates[] = $col;
    
    $carouselTemplateBuilder = new CarouselTemplateBuilder($CarouselColumnTemplates);
    $templateMessage = new TemplateMessageBuilder('Good appearance or Fantastic or Pro', $carouselTemplateBuilder);
  
    return $templateMessage;
  }

  private function chooseCarouselHumanFilter(){
    $col = new CarouselColumnTemplateBuilder('Good appearance', "人の見栄えを良くするフィルター", "https://tomo.syo.tokyo/openimg/human.jpg", [
        new PostbackTemplateActionBuilder('決定', "imgtype=appearance&img=".$this->ImgName)
    ]);
    $CarouselColumnTemplates[] = $col;
    
    $col = new CarouselColumnTemplateBuilder('Fantastic', "人を幻想的にするフィルター", "https://tomo.syo.tokyo/openimg/human.jpg", [
        new PostbackTemplateActionBuilder('決定', "imgtype=fantastic&img=".$this->ImgName)
    ]);
    $CarouselColumnTemplates[] = $col;
    
    $col = new CarouselColumnTemplateBuilder('Pro', "一眼レフカメラフィルター", "https://tomo.syo.tokyo/openimg/human.jpg", [
        new PostbackTemplateActionBuilder('決定', "imgtype=pro&img=".$this->ImgName)
    ]);
    $CarouselColumnTemplates[] = $col;
    
    $carouselTemplateBuilder = new CarouselTemplateBuilder($CarouselColumnTemplates);
    $templateMessage = new TemplateMessageBuilder('Good appearance or Fantastic or Pro', $carouselTemplateBuilder);
  
    return $templateMessage;
  }

  public function responseMessage(){
    $RunScriptPath = LOCAL_SCRIPT_PATH."/image_converter/analyze_image.sh";
    $LocalUserimgPath = LOCAL_IMAGES_PATH."/userimg/".$this->ImgName;
    $ShellRunStr = "sh {$RunScriptPath} {$LocalUserimgPath}";
    $Res = system($ShellRunStr);
    $AnalizeData = json_decode($Res);
    if($AnalizeData->human_num > 0){
      $TextMessageBuilder = new TextMessageBuilder("人の画像だね！この辺りとかどう？");
      $TemplateMessage = $this->chooseCarouselHumanFilter();
    }else{
      $TextMessageBuilder = new TextMessageBuilder("景色の画像だね！この辺りが良さそう！");
      $TemplateMessage = $this->chooseCarouselSceneryFilter();
    }
    $message = new MultiMessageBuilder();
    $message->add($TextMessageBuilder);
    $message->add($TemplateMessage);
    $response = $this->Bot->replyMessage($this->EventData->getReplyToken(), $message);
  } 

}