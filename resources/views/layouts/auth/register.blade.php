<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register MYBAAK</title>
    <link rel="icon" href="{{ asset('assets/images/logouis.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('assets/images/gedunguis.JPG');
            /* Keep original background */
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Darker overlay */
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* Dark overlay */
        }

        .login-box {
            background: rgba(0, 0, 0, 0.5);
            /* Darker background for the form */
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            z-index: 1;
            /* Ensure form appears above overlay */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .logo-container {
            position: absolute;
            top: 20px;
            left: 100px;
            z-index: 2;
            /* Ensure logo is on top */
        }

        .logo {
            width: 100px;
            /* Reduced logo size */
        }

        p.text-left {
            text-align: left;
            color: #FFF742;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .input-field {
            width: 100%;
            padding: 14px;
            margin-bottom: 8px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: black;
            font-size: 14px;
        }

        .input-field.is-error {
            border-color: #e53935;
        }

        .field-error {
            display: block;
            color: #ff6b6b;
            font-size: 12px;
            text-align: left;
            margin-bottom: 12px;
            margin-top: -2px;
        }

        .field-error i {
            margin-right: 4px;
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container .toggle-password {
            position: absolute;
            right: 14px;
            top: 14px;
            cursor: pointer;
            color: #666;
            z-index: 10;
        }

        .password-container .input-field {
            padding-right: 40px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #087C39;
            /* Green color for the button */
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #FFF742;
            /* Yellow color on hover */
        }

        .alternative-actions p,
        .remember-me,
        .new-to-baak p {
            text-align: left;
            margin-bottom: 10px;
        }

        .alternative-actions p a,
        .new-to-baak a {
            color: #007bff;
            text-decoration: none;
        }

        .alternative-actions p a:hover,
        .new-to-baak a:hover {
            text-decoration: underline;
        }

        .remember-me {
            color: #888;
        }

        .new-to-baak p {
            color: #888;
            margin-top: 20px;
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 94px;
            padding: 20px 0;
            background: linear-gradient(45deg, #087C39, #FFF742);
            /* Green to yellow gradient */
            text-align: center;
            font-size: 12px;
            color: white;
            z-index: 10;
            /* Ensure footer is on top of other content */
        }

        footer a {
            color: #087C39;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Mengatur posisi logo untuk tampilan mobile */
        @media (max-width: 768px) {
            .logo-container {
                position: absolute;
                top: 10%;
                left: 50%;
                transform: translateX(-50%);
                z-index: 2;
            }

            .logo {
                width: 80px;
                /* Ukuran logo lebih kecil pada mobile */
            }


        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="{{ asset('assets/images/logouis.png') }}" alt="Logo" class="logo">
        <!-- Replace with actual logo path -->
    </div>

    <div class="login-box">
        <form class="login-form" action="{{ route('registerproses') }}" method="post">
            @csrf

            <p class="text-left">Daftar Akun MYBAAK UIS</p>

            {{-- Nama --}}
            <input type="text" name="name" placeholder="Nama Lengkap"
                   class="input-field {{ $errors->has('name') ? 'is-error' : '' }}"
                   value="{{ old('name') }}">
            @error('name')
                <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
            @enderror

            {{-- Email --}}
            <input type="email" name="email" placeholder="Email (contoh: nama@uis.ac.id)"
                   class="input-field {{ $errors->has('email') ? 'is-error' : '' }}"
                   value="{{ old('email') }}">
            @error('email')
                <span class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
            @enderror

            {{-- Password --}}
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Sandi (minimal 8 karakter)"
                       class="input-field {{ $errors->has('password') ? 'is-error' : '' }}">
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
            </div>
            @error('password')
                <span class="field-error" style="margin-bottom:12px;"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
            @enderror

            <button type="submit" class="submit-btn">Daftar</button>
            {{-- <div class="alternative-actions">
                <p><a href="#">Gunakan Kode Masuk</a></p>
                <p><a href="#">Lupa sandi?</a></p>
            </div> --}}
        </form>
        <div class="new-to-baak">
            <p>Baru di MYBAAK? <a href="{{ route('login') }}">Masuk sekarang.</a></p>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div style="font-size: 16px;">
                <p>Jalan Teuku Umar - Lubuk Baja, Lubuk Baja Kota, Lubuk Baja, Kota Batam, Kepulauan Riau 29432</p>
            </div>
            <div class="cookie-preference">
                {{-- <p><a href="#">Preferensi Cookie</a> | <a href="#">Informasi Perusahaan</a></p> --}}
            </div>
            <div class="language">
                {{-- <p><a href="#">Bahasa Indonesia</a></p> --}}
            </div>
        </div>
    </footer>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function(e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye icon class
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        const regForm = document.querySelector('form');
        if (regForm) {
            regForm.addEventListener('submit', function(e) {
                const btn = this.querySelector('button[type="submit"]');
                if (this.dataset.submitting === 'true') {
                    e.preventDefault();
                    return false;
                }
                this.dataset.submitting = 'true';
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Mendaftar...';
                }
            });
        }
    </script>
</body>

</html>
