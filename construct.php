<html>

<head>
  <title></title>
</head>

<body>

<?php

class test
{	function test()
	{      for($i = 10 ; $i < 15 ;$i++)
      {      	echo $i.'<br>';      }	}

	function __construct()
	{		echo 'this is construct';	}
	function __destruct()
	{		echo 'this is destruct';	}
}

$andrew = new test;
//svar_dump($andrew);

?>

</body>

</html>