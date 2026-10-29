<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f5f1">
    <title>Parcel Claims | Claims support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        :root{color-scheme:light;--ink:#172b3a;--muted:#586b78;--line:#dce2e3;--paper:#f6f5f1;--white:#fff;--blue:#124a68;--blue-dark:#10394f;--orange:#d87843;--green:#356d5a}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--paper);color:var(--ink);font-family:Inter,"Segoe UI",Arial,sans-serif;-webkit-font-smoothing:antialiased}a{color:inherit}.shell{width:min(1140px,calc(100% - 48px));margin:auto}
        .topline{height:36px;background:var(--blue-dark);color:#d7e4e8;font-size:12px;letter-spacing:.025em}.topline .shell{height:100%;display:flex;align-items:center;justify-content:space-between}.topline p{margin:0}.availability{display:flex;align-items:center;gap:8px}.dot{width:7px;height:7px;border-radius:50%;background:#87c7a8;box-shadow:0 0 0 3px #87c7a822}
        header{height:78px;background:#fff;border-bottom:1px solid #e5e8e5}.nav{height:100%;display:flex;align-items:center;justify-content:space-between}.brand{display:flex;align-items:center;gap:12px;text-decoration:none}.brandmark{display:grid;place-items:center;width:38px;height:38px;border-radius:9px;background:var(--blue);color:#fff}.brandname{font-size:14px;font-weight:750;letter-spacing:.07em;text-transform:uppercase}.brandname small{display:block;margin-top:3px;color:#7b898d;font-size:9px;font-weight:600;letter-spacing:.16em}.navright{display:flex;align-items:center;gap:30px}.navright a{font-size:13px;text-decoration:none;color:#51616b}.navright a:hover{color:var(--blue)}.navright .staff{padding:10px 15px;border:1px solid #cdd7da;border-radius:5px;color:var(--blue);font-weight:650}
        .hero{position:relative;overflow:hidden;background:var(--blue);color:white}.hero:after{position:absolute;content:"";width:490px;height:490px;right:-120px;top:-250px;border:1px solid #ffffff17;border-radius:50%;box-shadow:0 0 0 45px #ffffff08,0 0 0 95px #ffffff06}.hero .shell{position:relative;z-index:1;display:grid;grid-template-columns:1.13fr .87fr;gap:64px;align-items:center;min-height:390px;padding-top:60px;padding-bottom:62px}.eyebrow{margin:0 0 18px;color:#e6b38e;font-size:11px;font-weight:750;letter-spacing:.17em;text-transform:uppercase}.hero h1{max-width:620px;margin:0;font-family:Georgia,"Times New Roman",serif;font-size:clamp(42px,5.1vw,64px);font-weight:500;line-height:1.04;letter-spacing:-.035em}.hero-copy{max-width:540px;margin:22px 0 0;color:#d9e5e8;font-size:16px;line-height:1.75}.hero-actions{display:flex;align-items:center;gap:21px;margin-top:29px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:45px;padding:0 18px;border-radius:4px;background:#fff;color:var(--blue-dark);font-size:13px;font-weight:700;text-decoration:none;transition:transform .15s,background .15s}.button:hover{transform:translateY(-1px);background:#f1f3ef}.text-link{color:#fff;font-size:13px;font-weight:600;text-underline-offset:4px}.hero-note{margin:20px 0 0;color:#b8ced5;font-size:12px}.hero-visual{position:relative;display:flex;justify-content:center;align-items:center;min-height:250px}.route-card{position:relative;width:min(100%,350px);padding:26px 25px;background:#f6f5f1;color:var(--ink);border-radius:5px;box-shadow:0 18px 60px #08253640;transform:rotate(1.5deg)}.route-top{display:flex;align-items:center;justify-content:space-between;padding-bottom:16px;border-bottom:1px solid var(--line);color:#73818a;font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase}.parcel-icon{display:grid;place-items:center;width:45px;height:45px;margin-top:19px;border-radius:4px;background:#e6eef0;color:var(--blue)}.route-card h2{margin:14px 0 5px;font-family:Georgia,"Times New Roman",serif;font-size:22px;font-weight:500}.route-card p{margin:0;color:#71808a;font-size:12px}.progress{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin-top:23px}.progress span{height:3px;background:#dce3e1}.progress span:first-child,.progress span:nth-child(2){background:#689985}.route-status{display:flex;align-items:center;gap:7px;margin-top:15px;color:var(--green);font-size:11px;font-weight:700}
        .assurance{background:#fff;border-bottom:1px solid #e5e8e5}.assurance .shell{display:grid;grid-template-columns:repeat(3,1fr);padding:18px 0}.assurance-item{display:flex;align-items:center;gap:11px;padding:0 24px;border-right:1px solid #e5e8e5}.assurance-item:first-child{padding-left:0}.assurance-item:last-child{border:0}.assurance-icon{display:grid;place-items:center;width:31px;height:31px;border-radius:50%;background:#edf3f1;color:var(--green);font-size:14px}.assurance-item strong{display:block;font-size:12px}.assurance-item small{display:block;margin-top:3px;color:#7a878e;font-size:10px}
        .section{padding:76px 0 84px}.section-heading{display:flex;justify-content:space-between;align-items:end;gap:24px;margin-bottom:29px}.section-heading .eyebrow{margin-bottom:9px;color:#a65e36}.section-heading h2{margin:0;font-family:Georgia,"Times New Roman",serif;font-size:32px;font-weight:500;letter-spacing:-.02em}.section-heading>p{max-width:380px;margin:0;color:var(--muted);font-size:13px;line-height:1.65}.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.step{min-height:162px;padding:22px 23px;background:#fff;border:1px solid #e2e6e3;border-radius:5px}.step-num{color:#bd7147;font-family:Georgia,serif;font-size:14px}.step h3{margin:16px 0 7px;font-size:14px}.step p{max-width:290px;margin:0;color:#687780;font-size:12px;line-height:1.7}
        .security{display:flex;justify-content:space-between;align-items:center;gap:24px;padding:24px 28px;background:#eaf0ed;border:1px solid #d9e4de;border-radius:5px}.security-copy{display:flex;align-items:center;gap:15px}.security-badge{display:grid;place-items:center;width:42px;height:42px;flex:0 0 auto;border-radius:50%;background:#d8e8df;color:#356d5a}.security h2{margin:0 0 4px;font-size:13px}.security p{max-width:650px;margin:0;color:#5c7068;font-size:11px;line-height:1.6}.security a{flex:0 0 auto;color:#315f50;font-size:12px;font-weight:700;text-underline-offset:3px}
        .hero-visual:before{position:absolute;inset:7px 8px;content:"";border-radius:5px;background:linear-gradient(180deg,#102b3b12 20%,#102b3b77),url('https://images.unsplash.com/photo-1769355104335-acef3aa4c9b6?auto=format&fit=crop&w=1200&q=85') center 52%/cover no-repeat;box-shadow:0 18px 55px #08253640}.route-card{z-index:1;max-width:320px;transform:none;box-shadow:0 14px 42px #08253633}.brandmark i{font-size:17px}.parcel-icon i{font-size:21px}.assurance-icon i{font-size:13px}.security-badge i{font-size:18px}.hero-actions .fa-arrow-right{margin-left:10px}.security a i{margin-left:4px}.photo-credit{position:absolute;z-index:2;right:15px;bottom:12px;color:#fff;font-size:9px;text-shadow:0 1px 3px #000a}
        footer{background:#fff;border-top:1px solid #e2e6e3}.footer{display:flex;justify-content:space-between;gap:20px;padding:21px 0;color:#75828a;font-size:11px}.footer p{margin:0}.footer a{color:#536b76;text-decoration:none}.footer a:hover{text-decoration:underline}
        @media(max-width:760px){.shell{width:min(100% - 36px,560px)}.topline .shell{justify-content:center}.topline p:last-child{display:none}.navright{gap:15px}.navright>a:not(.staff){display:none}.hero .shell{grid-template-columns:1fr;gap:25px;padding-top:48px;padding-bottom:42px}.hero h1{font-size:48px}.hero-copy{font-size:14px}.hero-visual{min-height:205px}.route-card{max-width:340px}.assurance .shell{grid-template-columns:1fr;gap:13px;padding:18px 0}.assurance-item,.assurance-item:first-child{padding:0;border:0}.section{padding:55px 0}.section-heading{display:block}.section-heading>p{margin-top:10px}.steps{grid-template-columns:1fr}.step{min-height:auto}.security{align-items:flex-start;flex-direction:column;padding:20px}.footer{flex-direction:column}.section-heading h2{font-size:29px}}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*:before,*:after{transition:none!important}}
    </style>
</head>
<body>
    <div class="topline"><div class="shell"><p class="availability"><span class="dot"></span> Claims support available</p><p>Secure card verification powered by {{ $gatewayName }}</p></div></div>
    <header>
        <nav class="shell nav" aria-label="Main navigation">
            <a class="brand" href="{{ url('/') }}" aria-label="Parcel Claims home">
                <span class="brandmark" aria-hidden="true"><i class="fa-solid fa-boxes-stacked"></i></span>
                <span class="brandname">Parcel Claims<small>Customer support</small></span>
            </a>
            <div class="navright"><a href="#process">How it works</a><a href="#security">Card security</a><a class="staff" href="{{ route('admin.login') }}">Staff sign in</a></div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="shell">
                <div>
                    <p class="eyebrow">Claims &amp; parcel support</p>
                    <h1>We’re here to help resolve your claim.</h1>
                    <p class="hero-copy">Follow the secure verification link sent by our claims team to confirm your card and keep your claim moving. It takes just a few minutes.</p>
                    <div class="hero-actions"><a class="button" href="#process">See how verification works <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><a class="text-link" href="{{ route('admin.login') }}">Claims team sign in</a></div>
                    <p class="hero-note">Have a link already? Open it directly from the message you received.</p>
                </div>
                <div class="hero-visual" aria-label="Claim status illustration">
                    <div class="route-card">
                        <div class="route-top"><span>Customer claim</span><span>Secure process</span></div>
                        <div class="parcel-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></div>
                        <h2>Verification in progress</h2><p>Your claim team will continue once your card is verified.</p>
                        <div class="progress" aria-hidden="true"><span></span><span></span><span></span></div>
                        <div class="route-status"><span class="dot" style="background:#5b997b;box-shadow:none"></span> Secure card verification</div>
                    </div>
                    <span class="photo-credit">Photo: Guilherme Mendes / Unsplash</span>
                </div>
            </div>
        </section>

        <section class="assurance" aria-label="Service assurances"><div class="shell">
            <div class="assurance-item"><span class="assurance-icon"><i class="fa-solid fa-link" aria-hidden="true"></i></span><span><strong>Private verification links</strong><small>Unique to your claim</small></span></div>
            <div class="assurance-item"><span class="assurance-icon"><i class="fa-regular fa-credit-card" aria-hidden="true"></i></span><span><strong>Secure {{ $gatewayName }} checkout</strong><small>Card details handled by the payment provider</small></span></div>
            <div class="assurance-item"><span class="assurance-icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span><span><strong>Claim team follow-up</strong><small>We continue after verification</small></span></div>
        </div></section>

        <section class="section" id="process"><div class="shell">
            <div class="section-heading"><div><p class="eyebrow">A straightforward process</p><h2>What to expect</h2></div><p>We’ll guide you through the verification securely. You won’t need to create an account or share your card details with our team.</p></div>
            <div class="steps">
                <article class="step"><span class="step-num">01 / Link</span><h3>Open your claim link</h3><p>Use the private link sent to your email or phone by the claims team.</p></article>
                <article class="step"><span class="step-num">02 / Verify</span><h3>Confirm your card securely</h3><p>Complete the small verification payment with {{ $gatewayName }}. Your bank may ask you to approve it with a one-time code.</p></article>
                <article class="step"><span class="step-num">03 / Continue</span><h3>We pick up your claim</h3><p>Once the payment is confirmed, the claims team can continue processing your request.</p></article>
            </div>
            <aside class="security" id="security"><div class="security-copy"><span class="security-badge" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span><span><h2>Your card details stay protected</h2><p>Card number and security code are entered directly in the payment provider’s secure checkout. This site stores only the payment authorization token and limited card details needed to manage your claim.</p></span></div><span>{{ $gatewayName }} secure checkout</span></aside>
        </div></section>
    </main>

    <footer><div class="shell footer"><p>© {{ date('Y') }} Parcel Claims. All rights reserved.</p><p>Questions about your claim? Reply to the message from your claims representative.</p></div></footer>
</body>
</html>
