
    </div>

    <div class="bottom-section">
        Registered Names and Trademarks are the copyright and property of their respective owners.
        &copy; 2024 Andyweiren Toy Store. All Rights Reserved.
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const currentPage = window.location.pathname.split("/").pop(); // 当前文件名
  const urlParams = new URLSearchParams(window.location.search);
  const currentCategoryID = urlParams.get('CategoryID');

  const menuLinks = document.querySelectorAll(".left-sidebar .menu ul li a");
  let isActiveSet = false;

  menuLinks.forEach(link => {
    link.classList.remove("active");

    // 优先：CategoryID 匹配
    const categoryID = link.getAttribute("href").split("?CategoryID=")[1]?.split("&")[0];
    if (currentCategoryID && categoryID && currentCategoryID === categoryID) {
      link.classList.add("active");
      isActiveSet = true;
    }
  });

  // 如果没有CategoryID匹配，就看文件名是否匹配
  if (!isActiveSet) {
    menuLinks.forEach(link => {
      const linkPage = link.getAttribute("href").split("/").pop().split("?")[0];
      if (linkPage === currentPage) {
        link.classList.add("active");
        isActiveSet = true;
      }
    });
  }

  // 如果还没匹配，就默认第一个（主页）
  if (!isActiveSet && menuLinks.length > 0) {
    menuLinks[0].classList.add("active");
  }
});
</script>



</body>
</html>