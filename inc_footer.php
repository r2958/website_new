
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

    <script>
        // 🚀 JavaScript 动态效果
        document.addEventListener('DOMContentLoaded', () => {
            const mainImage = document.querySelector('.main-image');
            const thumbnails = document.querySelectorAll('.thumbnail-image');

            // 为每个缩略图添加点击事件监听器
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', () => {
                    // 移除所有缩略图的 active 类
                    thumbnails.forEach(thumb => thumb.classList.remove('active'));

                    // 为当前点击的缩略图添加 active 类
                    thumbnail.classList.add('active');

                    // 将主图的 src 切换为当前缩略图的 data-main-src
                    mainImage.src = thumbnail.getAttribute('data-main-src');
                });
            });

            // 获取所有菜单链接元素
			const menuLinks = document.querySelectorAll(".left-sidebar .menu ul li a");





        const flyButtons = document.querySelectorAll('.add-to-cart-fly');
        const cartLink = document.getElementById('cart-link');

        // 缩略图点击事件
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', () => {
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                thumbnail.classList.add('active');
                mainImage.src = thumbnail.getAttribute('data-main-src');
            });
        });



        // 购物车抛物线动画函数
        function flyToCart(button) {
            // 1. 创建一个新的飞行图片元素
            const flyImage = document.createElement('img');
            flyImage.src = button.getAttribute('data-product-img');
            flyImage.classList.add('fly-image-temp'); // 使用一个临时类名
            document.body.appendChild(flyImage);

            // 获取按钮和购物车的位置
            const buttonRect = button.getBoundingClientRect();
            const cartRect = cartLink.getBoundingClientRect();

            // 2. 设置飞行图片的初始位置和样式
            flyImage.style.cssText = `
                position: fixed;
                z-index: 9999;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                pointer-events: none;
                transition: all 0.8s cubic-bezier(0.5, -0.75, 0.7, 1);
                opacity: 1;
                left: ${buttonRect.left + buttonRect.width / 2 - 25}px;
                top: ${buttonRect.top + buttonRect.height / 2 - 25}px;
            `;

            // 强制浏览器回流，确保动画从初始位置开始
            void flyImage.offsetWidth;

            // 3. 设置飞行图片的最终位置，并触发动画
            flyImage.style.left = `${cartRect.left + cartRect.width / 2 - 25}px`;
            flyImage.style.top = `${cartRect.top + cartRect.height / 2 - 25}px`;

            // 4. 动画结束后，移除飞行图片并显示 +1 标记
            setTimeout(() => {
                flyImage.remove(); // 移除元素
                
                // 创建 +1 标记
                const plusOne = document.createElement('span');
                plusOne.textContent = '+1';
                plusOne.classList.add('plus-one');
                
                // 定位并显示 +1 标记
                plusOne.style.cssText = `
                    position: absolute;
                    left: ${cartRect.left + cartRect.width / 2}px;
                    top: ${cartRect.top}px;
                `;
                document.body.appendChild(plusOne);

                // 动画结束后移除 +1 标记
                setTimeout(() => {
                    plusOne.remove();
                }, 1500); // 与 @keyframes fly-out 的时长一致
            }, 1000);
        }

        // 为每个“加入购物车”按钮添加点击事件
        flyButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                flyToCart(event.currentTarget);
            });
        });





        });


// 获取 URL 中的查询字符串
const queryString = window.location.search;

// 使用 URLSearchParams 来解析查询字符串
const urlParams = new URLSearchParams(queryString);

// 获取 'SearchFor' 参数的值
const searchForValue = urlParams.get('SearchFor');

// 检查是否成功获取到值
if (searchForValue) {
  // 找到搜索框元素（假设其 name 属性为 'SearchFor'）
  const searchInput = document.querySelector('input[name="SearchFor"]');
  
  // 如果找到了搜索框，就更新它的值
  if (searchInput) {
    searchInput.value = searchForValue;
  }
}

    </script>


</body>
</html>
<? //$this->debugShowPageVariables($GLOBALS); ?>
