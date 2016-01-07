<?php
require_once('../application.php');
/*
require_once('../application.php');
require_once($CFG->serverroot . '/common/captcha/class.Captcha.php');
$d = new Captcha();
$d->showCaptchaImage();
*/
//生成验证码图片
Header("Content-type: image/PNG");
$im = imagecreate(60,30);
$back = ImageColorAllocate($im, 245,245,245);
imagefill($im,0,0,$back); //背景
$vcodes = '';
srand((double)microtime()*1000000);
//生成4位数字
for($i=0;$i<5;$i++){
$font = ImageColorAllocate($im, rand(100,255),rand(0,100),rand(100,255));
$authnum=rand(0,9);
$vcodes.=$authnum;
imagestring($im, 5, 2+$i*10, 1, $authnum, $font);
}

for($i=0;$i<100;$i++) //加入干扰象素
{ 
$randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
imagesetpixel($im, rand()%70 , rand()%30 , $randcolor);
} 
ImagePNG($im);
ImageDestroy($im);
$_SESSION['VCODE'] = $vcodes;


?>