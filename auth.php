<?php
/**
 * auth.php - Đăng nhập và đăng ký với giao diện mới
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/settings_helper.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login';
$success = '';
$error = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if (isset($user['status']) && $user['status'] !== 'active') {
                $error = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_membership'] = $user['membership'];
                $_SESSION['user_role'] = $user['role'];
                if ($user['role'] === 'admin') {
                    redirect(SITE_URL . '/admin/dashboard.php');
                } else {
                    redirect(SITE_URL . '/index.php');
                }
            }
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
        }
    }
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, membership, role) VALUES (:name, :email, :phone, :password, 'bronze', 'customer')");
            
            if ($stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashed_password
            ])) {
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
                $mode = 'login';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}

$pageTitle = $mode === 'login' ? 'Đăng nhập' : 'Đăng ký tài khoản';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $pageTitle ?> - Xanh Organic</title>
    <link href="<?= SITE_URL ?>/css/tailwind.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#4C7C44",
                        "primary-light": "#F0F5EE",
                        "secondary": "#F9A826",
                        "background-light": "#FBFBF7",
                        "text-light": "#374151",
                        "text-subtle": "#6B7280",
                        "border-light": "#E5E7EB",
                    },
                    fontFamily: {
                        "display": ["Be Vietnam Pro", "sans-serif"]
                    },
                    borderRadius: {
                        "xl": "1rem",
                        "2xl": "1.5rem"
                    },
                    boxShadow: {
                        'soft': '0 4px 12px 0 rgba(0, 0, 0, 0.05)',
                        'inner-soft': 'inset 0 2px 4px 0 rgba(0,0,0,0.04)',
                    }
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light text-text-light antialiased">
    <div class="relative flex min-h-screen w-full">
        <!-- Left Side - Form -->
        <div class="flex w-full flex-col items-center justify-center p-6 lg:w-1/2">
            <div class="w-full max-w-md">
                <!-- Logo & Title -->
                <div class="mb-4 text-center">
                    <a class="inline-flex items-center gap-2 mb-2" href="<?= SITE_URL ?>">
                        <?php 
                        $siteLogo = getSystemSetting('site_logo', '');
                        $siteName = getSystemSetting('site_name', 'Xanh Organic');
                        if (!empty($siteLogo)): ?>
                            <img src="<?= SITE_URL . '/' . htmlspecialchars($siteLogo) ?>" alt="<?= htmlspecialchars($siteName) ?>" class="h-22 object-contain">
                        <?php else: ?>
                            <svg class="h-14 w-14 text-primary" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="#4C7C44"></path>
                                <path d="M2 17l10 5 10-5" stroke="#4C7C44" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                                <path d="M2 12l10 5 10-5" stroke="#4C7C44" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                            </svg>
                        <?php endif; ?>
                        <!-- <span class="text-2xl font-bold text-primary"><?= htmlspecialchars($siteName) ?></span> Chữ Xnah Organic-->
                    </a>
                    <h1 class="tracking-tight text-3xl font-bold text-gray-800">
                        <?= $mode === 'login' ? 'Chào mừng trở lại' : 'Tạo tài khoản mới' ?>
                    </h1>
                    <p class="text-text-subtle pt-2">
                        <?= $mode === 'login' ? 'Đăng nhập để tiếp tục mua sắm' : 'Bắt đầu hành trình sống khỏe cùng Xanh!' ?>
                    </p>
                </div>

                <!-- Alerts -->
                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-2">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span><?= $success ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-2">
                        <span class="material-symbols-outlined">error</span>
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($mode === 'login'): ?>
                <!-- Demo Account Hint -->
                <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-blue-600 flex-shrink-0 text-sm" style="font-size: 18px;">info</span>
                        <div class="text-xs text-blue-900">
                            <p class="font-semibold mb-1">Tài khoản demo:</p>
                            <div class="space-y-1 bg-white/60 p-1.5 rounded border border-blue-100 text-xs">
                                <p><strong>User:</strong> <code class=" px-1.5 py-0.5 rounded text-xs">user@xanhorganic.com</code> / <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">123456</code></p>
                                <p><strong>Admin:</strong> <code class=" px-1.5 py-0.5 rounded text-xs">admin@xanhorganic.com</code> / <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">admin123</code></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($mode === 'login'): ?>
                    <!-- Login Form -->
                    <form method="POST" class="flex flex-col gap-4">
                        <label class="flex flex-col w-full">
                            <p class="text-sm font-medium leading-normal pb-2 text-gray-700">Email</p>
                            <input type="email" name="email" required
                                   class="form-input flex w-full rounded-xl text-text-light focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light bg-white h-12 placeholder:text-gray-400 px-4 text-base font-normal leading-normal transition-all duration-200 shadow-sm focus:border-primary border"
                                   placeholder="ban@email.com"/>
                        </label>

                        <label class="flex flex-col w-full">
                            <p class="text-sm font-medium leading-normal pb-2 text-gray-700">Mật khẩu</p>
                            <div class="relative flex w-full items-stretch">
                                <input type="password" name="password" id="password" required
                                       class="form-input flex w-full rounded-xl text-text-light focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light bg-white h-12 placeholder:text-gray-400 p-4 text-base font-normal leading-normal transition-all duration-200 shadow-sm focus:border-primary border"
                                       placeholder="Nhập mật khẩu của bạn"/>
                                <button type="button" onclick="togglePassword('password')"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-text-light">
                                    <span class="material-symbols-outlined text-xl">visibility</span>
                                </button>
                            </div>
                        </label>

                        <div class="flex items-center justify-between pt-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded text-primary focus:ring-primary"/>
                                <span class="text-sm text-text-subtle">Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="<?= SITE_URL ?>/forgot_password.php" class="text-sm font-medium text-primary hover:underline">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <button type="submit" name="login"
                                class="flex items-center justify-center font-bold text-white h-12 px-6 rounded-xl bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-200 w-full mt-4 text-base shadow-lg shadow-primary/20 hover:shadow-primary/30">
                            Đăng nhập
                        </button>
                        <p class="pt-4 text-center text-sm text-text-subtle">
                            Chưa có tài khoản?
                            <a href="?mode=register" class="font-bold text-primary hover:underline">Đăng ký ngay</a>
                        </p>
                    </form>
                <?php else: ?>
                    <!-- Register Form -->
                    <form method="POST" class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex flex-col w-full">
                                <p class="text-sm font-medium leading-normal pb-2 text-gray-700">Họ và Tên <span class="text-red-500">*</span></p>
                                <input type="text" name="name" required
                                       class="form-input flex w-full rounded-xl text-text-light focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light bg-white h-12 placeholder:text-gray-400 px-4 text-base font-normal leading-normal transition-all duration-200 shadow-sm focus:border-primary border"
                                       placeholder="Ví dụ: An Nguyễn"/>
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-sm font-medium leading-normal pb-2 text-gray-700">Số điện thoại</p>
                                <input type="tel" name="phone"
                                       class="form-input flex w-full rounded-xl text-text-light focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light bg-white h-12 placeholder:text-gray-400 px-4 text-base font-normal leading-normal transition-all duration-200 shadow-sm focus:border-primary border"
                                       placeholder="09xxxxxxxx"/>
                            </label>
                        </div>

                        <label class="flex flex-col w-full">
                            <p class="text-sm font-medium leading-normal pb-2 text-gray-700">Email <span class="text-red-500">*</span></p>
                            <input type="email" name="email" required
                                   class="form-input flex w-full rounded-xl text-text-light focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light bg-white h-12 placeholder:text-gray-400 px-4 text-base font-normal leading-normal transition-all duration-200 shadow-sm focus:border-primary border"
                                   placeholder="ban@email.com"/>
                        </label>

                        <label class="flex flex-col w-full">
                            <p class="text-sm font-medium leading-normal pb-2 text-gray-700">Mật khẩu <span class="text-red-500">*</span></p>
                            <div class="relative flex w-full items-stretch">
                                <input type="password" name="password" id="register-password" required
                                       class="form-input flex w-full rounded-xl text-text-light focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light bg-white h-12 placeholder:text-gray-400 p-4 text-base font-normal leading-normal transition-all duration-200 shadow-sm focus:border-primary border"
                                       placeholder="Tạo mật khẩu của bạn"/>
                                <button type="button" onclick="togglePassword('register-password')"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-text-light">
                                    <span class="material-symbols-outlined text-xl">visibility</span>
                                </button>
                            </div>
                        </label>

                        <div class="flex items-start pt-2">
                            <p class="text-xs text-text-subtle">
                                Bằng việc đăng ký, bạn đồng ý với 
                                <a class="font-medium text-primary hover:underline" href="#">Điều khoản Dịch vụ</a> và 
                                <a class="font-medium text-primary hover:underline" href="#">Chính sách Bảo mật</a> của chúng tôi.
                            </p>
                        </div>

                        <button type="submit" name="register"
                                class="flex items-center justify-center font-bold text-white h-12 px-6 rounded-xl bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-200 w-full mt-4 text-base shadow-lg shadow-primary/20 hover:shadow-primary/30">
                            Đăng ký ngay
                        </button>

                        <p class="pt-4 text-center text-sm text-text-subtle">
                            Đã có tài khoản?
                            <a href="?mode=login" class="font-bold text-primary hover:underline">Đăng nhập ngay</a>
                        </p>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Side - Image -->
        <div class="relative hidden w-1/2 flex-col items-center justify-center bg-primary-light lg:flex p-12">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20viewBox%3D%220%200%2040%2040%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cg%20fill%3D%22%234C7C44%22%20fill-opacity%3D%220.07%22%20fill-rule%3D%22evenodd%22%3E%3Cpath%20d%3D%22M0%2040L40%200H20L0%2020M40%2040V20L20%2040%22/%3E%3C/g%3E%3C/svg%3E')] opacity-50"></div>
            <div class="z-10 w-full max-w-lg space-y-8 text-center">
                <?php if ($mode === 'login'): ?>
                    <!-- Đăng nhập Image -->
                    <div class="relative w-full aspect-square rounded-2xl shadow-soft overflow-hidden">
                        <div class="w-full h-full bg-center bg-no-repeat bg-cover" 
                             style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDTWbfW23Vp_lDS4MiB1QuORVu16-GBIHQgJih4yI8Jk0KFvEJ9Cs0teSe_hbp5x4Sc09jHNqiQzV4_Pvg8ivg9vFDWS1F-BigbwfEzpbFioEwqZzUhdTCqZroq07Gfx7DIYwnCnEBa40HfEGJPNleSWzekcOX2Ipy44dPlJr4ZHePO6DJ0rfavKGMXsINl-jQ_w01dpP2cfcWTBGFx2A5yA_hf9xny1joK4a5HBsriL3pw-QuIvJIfbbwB7fCTW2j95YQmLMEZpSNr"); background-size: contain;'></div>
                    </div>
                    <div class="p-6 bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft">
                        <h2 class="text-2xl font-bold text-gray-800">"Khởi đầu lối sống lành mạnh, ngay từ hôm nay."</h2>
                        <p class="mt-2 text-text-subtle">Tham gia cùng hàng ngàn người dùng tin tưởng Xanh Organic để mang thực phẩm tươi sạch đến tận nhà.</p>
                    </div>
                <?php else: ?>
                    <!-- Đăng ký Image -->
                    <div class="relative w-full aspect-square rounded-2xl shadow-soft overflow-hidden">
                        <div class="w-full h-full bg-center bg-no-repeat bg-cover" 
                             style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCll8A3mPQzyBcabsJu_08U639k1Qbk_e45fMwD8d6n6JSlatK8KyW9PBmiKHnzojVu0PxpHSh6739dDTL13AAmfA7W5I4Hq9ScmA4Dg4w8pfE5c-v-_SjmpenrjWVa1LZKDvkAWbestnOgD8tCFmeVXggX8uf2mORzhwjGJtoWWqrp7VzJt81-OAhStK_A9GgT6RoijXf0xZZtlrC2XWGYSkj5iM18aPPfH7mJapIfwPN3i39XiWRzTSYfyx9uPmDdR0s-Qh0o0gw"); background-size: contain;'></div>
                    </div>
                    <div class="p-6 bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft">
                        <h2 class="text-2xl font-bold text-gray-800">"Sức khỏe là tài sản quý giá nhất."</h2>
                        <p class="mt-2 text-text-subtle">Hãy bắt đầu hành trình chăm sóc sức khỏe của bạn và gia đình với các sản phẩm hữu cơ chất lượng cao.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const icon = button.querySelector('.material-symbols-outlined');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            field.type = 'password';
            icon.textContent = 'visibility';
        }
    }
    </script>
</body>
</html>