<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f5f1">
    <title>Staff sign in | Parcel Claims</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        :root{--ink:#172b3a;--muted:#60717b;--line:#d9e0e2;--paper:#f6f5f1;--blue:#124a68;--blue-dark:#10394f;--orange:#d87843;--red:#a13d35}
        *{box-sizing:border-box}body{min-height:100vh;margin:0;background:var(--paper);color:var(--ink);font-family:Inter,"Segoe UI",Arial,sans-serif;-webkit-font-smoothing:antialiased}a{color:inherit}.page{width:min(1100px,calc(100% - 48px));min-height:100vh;margin:auto;display:flex;flex-direction:column}.header{height:84px;display:flex;align-items:center;justify-content:space-between}.brand{display:flex;align-items:center;gap:12px;color:var(--ink);text-decoration:none}.mark{display:grid;place-items:center;width:38px;height:38px;border-radius:8px;background:var(--blue);color:white}.brand-name{font-size:13px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}.brand-name small{display:block;margin-top:3px;color:#7a878e;font-size:9px;font-weight:600;letter-spacing:.16em}.back{color:#516774;font-size:12px;text-decoration:none}.back:hover{text-decoration:underline;text-underline-offset:4px}.main{flex:1;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:80px;padding:35px 0 70px}.intro{max-width:430px}.eyebrow{margin:0 0 16px;color:#a65e36;font-size:10px;font-weight:750;letter-spacing:.18em;text-transform:uppercase}.intro h1{margin:0;font-family:Georgia,"Times New Roman",serif;font-size:clamp(38px,4.1vw,54px);font-weight:500;line-height:1.08;letter-spacing:-.03em}.intro>p:not(.eyebrow){max-width:390px;margin:17px 0 0;color:var(--muted);font-size:14px;line-height:1.75}.access-note{display:flex;gap:12px;margin-top:35px;padding-top:20px;border-top:1px solid #d9dedb;color:#52666e;font-size:11px;line-height:1.65}.access-note>i{flex:0 0 auto;color:var(--blue);padding-top:2px}.access-note strong{display:block;margin-bottom:3px;color:var(--ink);font-size:12px}.intro-photo{position:relative;width:min(100%,390px);height:148px;margin:24px 0 0;overflow:hidden;border-radius:5px;background:#d6d4cc}.intro-photo img{display:block;width:100%;height:100%;object-fit:cover;object-position:center 58%}.intro-photo:after{position:absolute;inset:45% 0 0;content:"";background:linear-gradient(transparent,#0f2b3c99)}.intro-photo figcaption{position:absolute;z-index:1;bottom:11px;left:13px;color:#fff;font-size:10px;text-shadow:0 1px 3px #0008}.form-card{width:100%;max-width:430px;justify-self:end;padding:34px;background:#fff;border:1px solid #e0e5e3;border-radius:6px;box-shadow:0 12px 34px #1937470b}.form-heading{margin-bottom:25px}.form-heading h2{margin:0;font-size:20px;letter-spacing:-.02em}.form-heading p{margin:7px 0 0;color:var(--muted);font-size:12px;line-height:1.6}.error{margin-bottom:18px;padding:11px 13px;border:1px solid #edcfca;border-radius:4px;background:#fff5f3;color:var(--red);font-size:12px;line-height:1.5}.field{margin-top:17px}.field label{display:block;margin-bottom:7px;font-size:12px;font-weight:650}.field input{width:100%;height:46px;padding:0 13px;border:1px solid #cfd9dc;border-radius:4px;background:#fff;color:var(--ink);font:inherit;font-size:13px;outline:none;transition:border-color .15s,box-shadow .15s}.field input::placeholder{color:#9aa6aa}.field input:focus{border-color:var(--blue);box-shadow:0 0 0 3px #124a6814}.password-wrap{position:relative}.password-wrap input{padding-right:56px}.toggle{position:absolute;top:0;right:0;height:46px;padding:0 13px;border:0;background:none;color:#566f7b;font:inherit;font-size:11px;font-weight:650;cursor:pointer}.submit{display:flex;align-items:center;justify-content:center;width:100%;height:46px;margin-top:23px;border:0;border-radius:4px;background:var(--blue);color:white;font:inherit;font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}.submit:hover{background:var(--blue-dark)}.secure{display:flex;justify-content:center;align-items:center;gap:6px;margin-top:18px;color:#78868b;font-size:10px}.secure i{color:#57816f}.footer{display:flex;justify-content:space-between;gap:16px;padding:17px 0;border-top:1px solid #dfe4e1;color:#849096;font-size:10px}.footer p{margin:0}
        @media(max-width:760px){.page{width:min(100% - 36px,520px)}.header{height:70px}.main{grid-template-columns:1fr;gap:26px;align-content:center;padding:30px 0 45px}.intro{max-width:none}.intro h1{font-size:39px}.intro>p:not(.eyebrow){font-size:13px}.access-note{margin-top:20px;padding-top:14px}.form-card{max-width:none;justify-self:stretch;padding:25px}.footer{flex-direction:column;gap:7px}}
        @media(prefers-reduced-motion:reduce){*,*:before,*:after{transition:none!important}}
    </style>
</head>
<body>
    <div class="page">
        <header class="header">
            <a class="brand" href="{{ url('/') }}" aria-label="Parcel Claims home">
                <span class="mark" aria-hidden="true"><i class="fa-solid fa-boxes-stacked"></i></span>
                <span class="brand-name">Parcel Claims<small>Operations portal</small></span>
            </a>
            <a class="back" href="{{ url('/') }}">← Back to customer support</a>
        </header>

        <main class="main">
            <section class="intro">
                <p class="eyebrow">Claims operations</p>
                <h1>Welcome back.</h1>
                <p>Sign in to manage customer verification links, saved payment methods, and claim transactions.</p>
                <div class="access-note"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span><strong>Authorized staff only</strong>Access is limited to the staff account configured for this claims system.</span></div>
                <figure class="intro-photo"><img src="https://images.pexels.com/photos/21838827/pexels-photo-21838827.jpeg?auto=compress&amp;cs=tinysrgb&amp;w=1000" alt="Packages ready for delivery inside a logistics warehouse" loading="lazy"><figcaption>Photo: Wojciech Kotlicki / Pexels</figcaption></figure>
            </section>

            <section class="form-card" aria-labelledby="sign-in-title">
                <div class="form-heading"><h2 id="sign-in-title">Sign in to your account</h2><p>Enter your staff credentials to continue to the operations portal.</p></div>
                @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
                <form method="post" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="field"><label for="email">Work email</label><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@company.com" autocomplete="username" required autofocus></div>
                    <div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required><button class="toggle" type="button" id="toggle-password" aria-controls="password" aria-label="Show password">Show</button></div></div>
                    <button class="submit" type="submit">Sign in securely</button>
                </form>
                <div class="secure"><i class="fa-solid fa-lock" aria-hidden="true"></i>Protected staff sign-in</div>
            </section>
        </main>

        <footer class="footer"><p>© {{ date('Y') }} Parcel Claims</p><p>For customer claim help, return to the support page.</p></footer>
    </div>
    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('toggle-password');
        passwordToggle.addEventListener('click', () => {
            const visible = passwordInput.type === 'password';
            passwordInput.type = visible ? 'text' : 'password';
            passwordToggle.textContent = visible ? 'Hide' : 'Show';
            passwordToggle.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        });
    </script>
</body>
</html>
