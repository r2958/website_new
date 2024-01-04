<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>优化菜单样式</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    .menu {
      background-color: #eeeee9;
      color: #333;
      width: 175px;
      padding: 10px;
    }

    .menu ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .menu ul li {
      margin-bottom: 10px;
    }

    .menu ul li a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: #555;
      font-size: 14px;
      padding: 8px;
      transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
    }

    .menu ul li a:hover {
      background-color: #ddd;
      color: #333;
      transform: scale(1.1) rotate(5deg);
    }

    .menu ul li .icon {
      margin-right: 8px;
    }
  </style>
</head>
<body>

<div class="menu">
  <ul>
    <li class="page"><a href="/index.php"><span class="icon">🏠</span>Home</a></li>
    <li class="page"><a id="CategoryID1" title="Toy Pack" href="/index.php?CategoryID=1"><span class="icon">🎁</span>Toy Pack</a></li>
    <li class="page"><a id="CategoryID2" title="Toy Shoes" href="/index.php?CategoryID=2"><span class="icon">👟</span>Toy Shoes</a></li>
    <li class="page"><a id="CategoryID3" title="Toy Lamp" href="/index.php?CategoryID=3"><span class="icon">💡</span>Toy Lamp</a></li>
    <li class="page"><a id="CategoryID4" title="Toy Clothes" href="/index.php?CategoryID=4"><span class="icon">👕</span>Toy Clothes</a></li>
    <li class="page"><a id="CategoryID5" title="Toy Doll" href="/index.php?CategoryID=5"><span class="icon">🎎</span>Toy Doll</a></li>
    <li class="page"><a id="CategoryID6" title="Toy Ball" href="/index.php?CategoryID=6"><span class="icon">⚽</span>Toy Ball</a></li>
    <li class="page"><a id="CategoryID7" title="Funny Pictures" href="/index.php?CategoryID=7"><span class="icon">😄</span>Funny Pictures</a></li>
    <li class="page"><a id="CategoryID8" title="Entertaining Videos" href="/index.php?CategoryID=8"><span class="icon">🎬</span>Entertaining Videos</a></li>
    <li class="page"><a id="CategoryID9" title="Funny Jokes" href="/index.php?CategoryID=9"><span class="icon">😂</span>Funny Jokes</a></li>
  </ul>
</div>

</body>
</html>

