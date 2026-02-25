<?php
session_start();
require_once 'config/db_config.php';

// If user is already logged in, redirect them based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/pos.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bigasan | Smart POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --accent: #facc15;
            --accent-dark: #ca8a04;
            --dark-surface: #0f172a;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { 
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            min-height: 100vh;
            /* Using flex-column and padding to ensure scrolling works on very small screens */
            display: flex;
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #f8fafc; 
            margin: 0; 
            padding: 20px;
            overflow-x: hidden;
        }

        /* Decorative background element - hidden on mobile for performance */
        @media (min-width: 768px) {
            body::before {
                content: ""; position: absolute; width: 300px; height: 300px;
                background: var(--accent); filter: blur(150px);
                opacity: 0.1; top: -50px; left: -50px; z-index: -1;
            }
        }

        .login-card { 
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem; /* Reduced padding for mobile */
            border-radius: 1.5rem; 
            width: 100%; 
            /* Responsive Width */
            max-width: 400px; 
            transition: var(--transition);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        @media (min-width: 768px) {
            .login-card { padding: 3rem; border-radius: 2rem; max-width: 440px; }
        }

        .login-card:hover {
            transform: translateY(-5px);
            border-color: rgba(250, 204, 21, 0.3);
        }

        .brand-icon-wrapper {
            width: 70px; height: 70px;
            background: rgba(250, 204, 21, 0.1);
            border-radius: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            transition: var(--transition);
        }

        .login-card:hover .brand-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            background: var(--accent);
        }

        .login-card:hover .brand-icon-wrapper i { color: var(--dark-surface); }

        .form-label { color: #94a3b8; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; }

        .form-control {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff !important;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            transition: var(--transition);
            font-size: 1rem; /* Prevents iOS auto-zoom on input */
        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.1);
        }

        .password-group { position: relative; }
        
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.6);
            padding: 10px; /* Larger hit area for mobile */
            font-size: 1.2rem;
            z-index: 10;
        }

        .btn-harvest {
            background: var(--accent);
            color: var(--dark-surface);
            border: none;
            padding: 0.875rem;
            border-radius: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: var(--transition);
        }

        .btn-harvest:hover, .btn-harvest:active {
            background: #fff;
            transform: scale(1.02);
        }

        .alert-modern {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-radius: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center">
            <div class="brand-icon-wrapper">
                <i class="ph-fill ph-leaf" style="font-size: 2.25rem; color: var(--accent); transition: var(--transition);"></i>
            </div>
            <h2 class="fw-800 mb-1" style="letter-spacing: -1px; font-size: 1.75rem;">Bigasan natin</h2>
            <p class="text-secondary small mb-4 mb-md-5">Enterprise Rice Inventory</p>
        </div>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-modern text-center py-2 mb-4">
                <i class="ph-bold ph-x-circle me-1"></i> Access Denied
            </div>
        <?php endif; ?>

        <form action="auth/login_process.php" method="POST">
            <div class="mb-3 mb-md-4">
                <label class="form-label text-uppercase">Operator ID</label>
                <input type="text" name="username" class="form-control" required placeholder="Username">
            </div>
            <div class="mb-4 mb-md-5">
                <label class="form-label text-uppercase">Security Key</label>
                <div class="password-group">
                    <input type="password" name="password" id="passInput" class="form-control" required placeholder="••••••••">
                    <i class="ph ph-eye toggle-password" id="eyeIcon"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-harvest w-100">
                Secure Login <i class="ph-bold ph-fingerprint ms-2"></i>
            </button>
        </form>
    </div>

    <script>
        const eyeIcon = document.querySelector('#eyeIcon');
        const passInput = document.querySelector('#passInput');

        eyeIcon.addEventListener('click', () => {
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            eyeIcon.classList.toggle('ph-eye');
            eyeIcon.classList.toggle('ph-eye-slash');
        });
    </script>
</body>
</html>