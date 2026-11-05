<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f5f1">
    <title>Reset password | {{ $brandSettings["brand_name"] }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        :root{--ink:#172b3a;--muted:#60717b;--line:#d9e0e2;--paper:#f6f5f1;--blue:{{ $brandSettings["brand_primary_color"] }};--blue-dark:{{ $brandSettings["brand_primary_color"] }};--orange:{{ $brandSettings["brand_accent_color"] }};--red:#a13d35}
        *{box-sizing:border-box}body{min-height:100vh;margin:0;background:var(--paper);color:var(--ink);font-family:Inter,"Segoe UI",Arial,sans-serif;-webkit-font-smoothing:antialiased}a{color:inherit}.page{width:min(1100px,calc(100% - 48px));min-height:100vh;margin:auto;display:flex;flex-direction:column}.header{height:84px;display:flex;align-items:center;justify-content:space-between}.brand{display:flex;align-items:center;gap:12px;color:var(--ink);text-decoration:none}.mark{display:grid;place-items:center;width:38px;height:38px;border-radius:8px;background:var(--blue);color:white}.brand-name{font-size:13px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}.brand-name small{display:block;margin-top:3px;color:#7a878e;font-size:9px;font-weight:600;letter-spacing:.16em}.back{color:#516774;font-size:12px;text-decoration:none}.back:hover{text-decoration:underline;text-underline-offset:4px}.main{flex:1;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:80px;padding:35px 0 70px}.intro{max-width:430px}.eyebrow{margin:0 0 16px;color:#a65e36;font-size:10px;font-weight:750;letter-spacing:.18em;text-transform:uppercase}.intro h1{margin:0;font-family:Georgia,"Times New Roman",serif;font-size:clamp(38px,4.1vw,54px);font-weight:500;line-height:1.08;letter-spacing:-.03em}.intro>p:not(.eyebrow){max-width:390px;margin:17px 0 0;color:var(--muted);font-size:14px;line-height:1.75}.form-card{max-width:390px;padding:30px;background:white;border-radius:6px;box-shadow:0 15px 50px #0e243412}.form-heading{margin-bottom:20px}.form-heading h2{margin:0;color:var(--ink);font-size:20px;letter-spacing:-.02em}.form-heading p{margin:7px 0 0;color:var(--muted);font-size:12px;line-height:1.55}.field{margin-top:16px}.field label{display:block;margin-bottom:7px;color:#344e59;font-size:10px;font-weight:700}.field input{width:100%;height:44px;padding:0 12px;border:1px solid #d7e0e3;border-radius:5px;background:#fff;color:#1b3441;font:inherit;font-size:12px;outline:0;transition:border .15s,box-shadow .15s}.field input::placeholder{color:#a2afb4}.field input:focus{border-color:#52879a;box-shadow:0 0 0 3px #14536b13}.error{margin:0 0 16px;padding:11px 12px;border:1px solid #f0d3d0;border-radius:5px;background:#fff5f4;color:#a33e37;font-size:11px;line-height:1.5}.status{margin:0 0 16px;padding:11px 12px;border:1px solid #d1e7dd;border-radius:5px;background:#f0fdf4;color:#0f5132;font-size:11px;line-height:1.5}.submit{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;min-height:47px;margin-top:21px;border:0;border-radius:5px;background:var(--blue);color:#fff;font-size:12px;font-weight:750;cursor:pointer;transition:background .15s}.submit:hover{background:var(--blue-dark)}.secure{display:flex;gap:10px;margin-top:19px;padding-top:16px;border-top:1px solid #edf0f1;color:#74838a;font-size:10px;line-height:1.6}.secure i{margin-top:2px;color:var(--blue)}.footer{display:flex;justify-content:space-between;gap:18px;width:min(1100px,calc(100% - 48px));margin:0 auto;padding:19px 0;border-top:1px solid #e1e8e9;color:#85939a;font-size:9px}.footer a{color:#55717c;text-decoration:none}
        @media(max-width:760px){.page{width:min(100% - 36px,520px)}.header{height:70px}.main{grid-template-columns:1fr;gap:26px;align-content:center;padding:30px 0 45px}.intro{max-width:none}.intro h1{font-size:39px}.intro>p:not(.eyebrow){font-size:13px}.form-card{max-width:none;justify-self:stretch;padding:25px}.footer{flex-direction:column;gap:7px}}
        @media(prefers-reduced-motion:reduce){*,*:before,*:after{transition:none!important}}
    </style>
</head>
<body>
    <div class="page">
        <header class="header">
            <a class="brand" href="{{ url('/') }}" aria-label="{{ $brandSettings["brand_name"] }} home">
                <span class="mark" aria-hidden="true">@if($brandSettings["brand_logo_url"])<img src="{{ $brandSettings["brand_logo_url"] }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="fa-solid fa-boxes-stacked"></i>@endif</span>
                <span class="brand-name">{{ $brandSettings["brand_name"] }}<small>{{ $brandSettings["brand_tagline"] }}</small></span>
            </a>
            <a class="back" href="{{ route('admin.login') }}">← Back to sign in</a>
        </header>

        <main class="main">
            <section class="intro">
                <p class="eyebrow">Password recovery</p>
                <h1>Set a new password</h1>
                <p>Choose a secure password for your account. Your new password must be at least 8 characters long.</p>
            </section>

            <section class="form-card" aria-labelledby="reset-title">
                <div class="form-heading"><h2 id="reset-title">Create new password</h2><p>Enter your new password below.</p></div>
                @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
                @if(session('status'))<div class="status" role="alert">{{ session('status') }}</div>@endif
                <form method="post" action="{{ route('admin.password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <div class="field"><label for="password">New password</label><input id="password" name="password" type="password" placeholder="Enter your new password" autocomplete="new-password" required autofocus></div>
                    <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm your new password" autocomplete="new-password" required></div>
                    <button class="submit" type="submit">Reset password</button>
                </form>
                <div class="secure"><i class="fa-solid fa-lock" aria-hidden="true"></i>Secure password update</div>
            </section>
        </main>

        <footer class="footer"><p>© {{ date('Y') }} {{ $brandSettings["brand_name"] }}</p><p>For customer claim help, return to the support page.</p></footer>
    </div>
</body>
</html>
