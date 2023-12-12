<?php
$pic = opendir('../picture');
$i=0;
while($handle=readdir($pic))
{
	$i++;
	if($handle!='.' && $handle!='..'){
		$ext=explode('.',$handle);
		//var_dump($ext);exit;
		$file = '../picture/'.$handle;
		$newname = $i.'_01_th.'.$ext[1];
		$cmd = "cp $file $newname";
		$h=@popen($cmd,"r");
		stream_get_contents($h);
		pclose($h);
		//$content = file_get_contents('../picture/'.$handle);
		//file_put_contents($content,rand(1,100).'_01_th.'.$ext[1]);
	}
	if($i>100){
		exit('over');
	}

}


?>
