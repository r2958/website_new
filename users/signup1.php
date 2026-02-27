<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册 - 注册新账户</title>
    <meta name="keywords" content="用户注册, 注册, 新账户, Toys Company Cn">
    <meta name="description" content="欢迎注册Toys Company Cn新账户，填写必要信息即可开始您的购物之旅。">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link type="text/css" rel="stylesheet" href="style3.css?version=2024011" media="screen" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        /* 🎨 全局样式与配色优化 - 橙色主题 */
        body {
            font-family: 'PingFang SC', 'Helvetica Neue', Helvetica, 'Segoe UI', Arial, sans-serif;
            background-color: #fcf6f3;
            margin: 0;
            padding: 0;
            color: #2c3e50;
        }
        .top-menu {
            background-color: #ff9800;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-end;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .top-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }
        .top-menu ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .top-menu ul li a:hover {
            color: #f39c12;
            transform: translateY(-2px);
        }
        .top-menu ul li .icon {
            margin-right: 8px;
        }
        .main-wrapper {
            display: flex;
            flex-direction: column;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            gap: 20px;
        }
        /* 注册页面特定样式 */
        .register-container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 0;
        }
        .register-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        .register-card h2 {
            font-size: 28px;
            font-weight: bold;
            color: #e67e22;
            margin-bottom: 25px;
            position: relative;
        }
        /* 注册表单样式 */
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            text-align: left;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-group:focus-within {
            border-color: #ff9800;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
        }
        .input-group i {
            font-size: 20px;
            color: #999;
            margin-left: 12px;
        }
        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 0;
            border: none;
            outline: none;
            background: transparent;
            font-size: 16px;
            color: #333;
        }
        /* 密码输入框的眼睛图标样式 */
        .password-toggle {
            cursor: pointer;
            padding: 0 12px;
            color: #999;
            transition: color 0.3s;
        }
        .password-toggle:hover {
            color: #333;
        }
        .form-divider {
            border-top: 1px dashed #e0e0e0;
            margin: 20px 0;
        }
        .submit-btn {
            background-color: #ff9800;
            color: #fff;
            padding: 15px 20px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
            margin-top: 10px;
        }
        .submit-btn:hover {
            background-color: #f57c00;
            transform: translateY(-2px);
        }
        .login-link {
            display: block;
            margin-top: 20px;
            font-size: 14px;
            color: #e67e22;
            text-decoration: none;
        }
        .login-link:hover {
            text-decoration: underline;
        }
        .bottom-section {
            background-color: #ff9800;
            color: #fff;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }
        /* 验证错误信息样式 */
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: none; /* 默认隐藏 */
        }
        /* 提交按钮禁用时的样式 */
        .submit-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="top-menu">
        <ul>
            <li><a href="/index.php"><span class="icon">🏠</span>主页</a></li>
            <li><a href="/users/login.php" title="登录"><span class="icon">🔑</span>登录</a></li>
            <li><a href="/users/signup.php" title="注册"><span class="icon">📝</span>注册</a></li>
            <li><a href="/cart.php" id="cart-link" title="购物车"><span class="icon">🛒</span>购物车</a></li>
        </ul>
    </div>
    <div class="main-wrapper">
        <div class="register-container">
            <div class="register-card">
                <h2>新用户注册</h2>
                <form id="register-form" class="register-form" action="/users/register_process.php" method="post">
                    <h3>账户信息</h3>
                    <div class="form-group">
                        <label for="username">用户名</label>
                        <div class="input-group">
                            <i class="ri-user-3-line"></i>
                            <input type="text" id="username" name="username" placeholder="请输入用户名" required>
                        </div>
                        <div id="username-error" class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="password">密码</label>
                        <div class="input-group">
                            <i class="ri-lock-line"></i>
                            <input type="password" id="password" name="password" placeholder="请输入密码" required>
                            <i class="ri-eye-line password-toggle" data-target="password"></i>
                        </div>
                        <div id="password-error" class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">确认密码</label>
                        <div class="input-group">
                            <i class="ri-lock-2-line"></i>
                            <input type="password" id="confirm-password" name="confirm_password" placeholder="请再次输入密码" required>
                            <i class="ri-eye-line password-toggle" data-target="confirm-password"></i>
                        </div>
                        <div id="confirm-password-error" class="error-message"></div>
                    </div>
                    <div class="form-divider"></div>
                    <h3>个人及联系信息</h3>
                    <div class="form-group">
                        <label for="company_name">公司名称</label>
                        <div class="input-group">
                            <i class="ri-building-line"></i>
                            <input type="text" id="company_name" name="company_name" placeholder="请输入公司名称" required>
                        </div>
                        <div id="company-name-error" class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="email">电子邮件</label>
                        <div class="input-group">
                            <i class="ri-mail-line"></i>
                            <input type="email" id="email" name="email" placeholder="请输入常用邮箱" required>
                        </div>
                        <div id="email-error" class="error-message"></div>
                    </div>
                    <button type="submit" class="submit-btn">立即注册</button>
                    <a href="/users/login.php" class="login-link">已有账户？返回登录</a>
                </form>
            </div>
        </div>
    </div>
    <div class="bottom-section">
        Registered Names and Trademarks are the copyright and property of their respective owners.
        &copy; 2024 Andyweiren Toy Store. All Rights Reserved.
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', (event) => {
                    const targetId = event.currentTarget.getAttribute('data-target');
                    const targetInput = document.getElementById(targetId);
                    if (targetInput.type === 'password') {
                        targetInput.type = 'text';
                        event.currentTarget.classList.remove('ri-eye-line');
                        event.currentTarget.classList.add('ri-eye-off-line');
                    } else {
                        targetInput.type = 'password';
                        event.currentTarget.classList.remove('ri-eye-off-line');
                        event.currentTarget.classList.add('ri-eye-line');
                    }
                });
            });

            const registerForm = document.getElementById('register-form');
            const submitBtn = document.querySelector('.submit-btn');

            registerForm.addEventListener('submit', (event) => {
                event.preventDefault(); // 阻止默认表单提交

                // 隐藏所有错误信息
                document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

                let isValid = true;

                // 验证用户名
                const username = document.getElementById('username').value.trim();
                if (username.length < 6) {
                    document.getElementById('username-error').innerText = '用户名不能少于6个字符。';
                    document.getElementById('username-error').style.display = 'block';
                    isValid = false;
                }

                // 验证密码
                const password = document.getElementById('password').value;
                if (password.length < 8) {
                    document.getElementById('password-error').innerText = '密码不能少于8个字符。';
                    document.getElementById('password-error').style.display = 'block';
                    isValid = false;
                }

                // 验证确认密码
                const confirmPassword = document.getElementById('confirm-password').value;
                if (password !== confirmPassword) {
                    document.getElementById('confirm-password-error').innerText = '两次输入的密码不一致。';
                    document.getElementById('confirm-password-error').style.display = 'block';
                    isValid = false;
                }

                // 验证公司名称
                const companyName = document.getElementById('company_name').value.trim();
                if (companyName.length === 0) {
                    document.getElementById('company-name-error').innerText = '公司名称不能为空。';
                    document.getElementById('company-name-error').style.display = 'block';
                    isValid = false;
                }

                // 验证电子邮件格式
                const email = document.getElementById('email').value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    document.getElementById('email-error').innerText = '请输入有效的电子邮件地址。';
                    document.getElementById('email-error').style.display = 'block';
                    isValid = false;
                }

                if (isValid) {
                    // 验证通过，禁用按钮，防止重复提交
                    submitBtn.disabled = true;
                    submitBtn.innerText = '注册中...';

                    // 创建一个临时的表单并提交
                    const tempForm = document.createElement('form');
                    tempForm.action = '/users/register_process.php'; // 实际的后端处理页面
                    tempForm.method = 'POST';
                    tempForm.style.display = 'none';

                    const fields = {
                        username: username,
                        password: password,
                        company_name: companyName,
                        email: email
                    };

                    for (const key in fields) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = fields[key];
                        tempForm.appendChild(input);
                    }

                    document.body.appendChild(tempForm);
                    tempForm.submit();
                }
            });
        });
    </script>
</body>
</html>