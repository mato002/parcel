<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7f8">
    <title>Secure checkout | {{ $brandSettings["brand_name"] }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        :root{color-scheme:light;--ink:#172c38;--muted:#637782;--line:#dfe7e9;--blue:{{ $brandSettings["brand_primary_color"] }};--blue-dark:{{ $brandSettings["brand_primary_color"] }};--teal:{{ $brandSettings["brand_accent_color"] }};--canvas:#f3f6f7;--white:#fff}
        *{box-sizing:border-box}body{margin:0;background:var(--canvas);color:var(--ink);font-family:Inter,"Segoe UI",Arial,sans-serif;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;flex-direction:column}.topbar{height:76px;display:flex;align-items:center;justify-content:space-between;padding:0 max(28px,calc((100vw - 1160px)/2));border-bottom:1px solid #e4eaec;background:#fff}
        .brand{display:flex;align-items:center;gap:11px;color:var(--ink);text-decoration:none}.brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:7px;background:var(--blue);color:#fff;font-size:15px}.brand-name{font-size:12px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.brand-name small{display:block;margin-top:3px;color:#71858e;font-size:9px;font-weight:650;letter-spacing:.15em}
        .secure-label{display:flex;align-items:center;gap:8px;color:#58716f;font-size:11px;font-weight:650}.secure-label i{color:var(--teal)}
        .main{width:min(100% - 48px,1000px);margin:48px auto;flex:1;display:grid;grid-template-columns:minmax(0,1fr) minmax(390px,.9fr);align-items:center;gap:64px}
        .intro{max-width:450px}.eyebrow{display:flex;align-items:center;gap:9px;margin:0 0 16px;color:var(--teal);font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}.eyebrow:before{content:"";width:22px;height:2px;background:#62a18c}
        h1{max-width:500px;margin:0;color:#173141;font-size:clamp(34px,4vw,48px);line-height:1.08;letter-spacing:-.045em}.intro-copy{max-width:440px;margin:15px 0 24px;color:#61747e;font-size:14px;line-height:1.65}
        .price-card{display:flex;align-items:center;justify-content:space-between;max-width:420px;padding:17px 18px;border:1px solid #dce6e7;border-radius:8px;background:#fff;box-shadow:0 8px 25px #15394909}.price-label{color:#5b7079;font-size:11px;font-weight:650}.price-value{margin-top:4px;color:#173849;font-size:27px;font-weight:780;letter-spacing:-.04em}.price-value small{margin-left:5px;color:#617781;font-size:11px;letter-spacing:.02em}.payment-tag{display:flex;align-items:center;gap:6px;padding:7px 9px;border-radius:4px;background:#eef6f2;color:#35745b;font-size:10px;font-weight:700}.payment-tag i{font-size:10px}.charge-note{max-width:420px;margin:12px 0 0;color:#71818a;font-size:11px;line-height:1.5}
        .form-card{padding:30px;border:1px solid #e1e8ea;border-radius:9px;background:#fff;box-shadow:0 18px 50px #16384912}.form-heading{margin-bottom:20px}.form-heading h2{margin:0;color:#1c3542;font-size:20px;letter-spacing:-.025em}.claim-chip{display:flex;align-items:center;gap:10px;margin:0 0 20px;padding:12px;border:1px solid #e7edef;border-radius:6px;background:#f8fafb}.claim-chip i{color:#548492}.claim-chip small{display:block;margin-bottom:4px;color:#7c8c92;font-size:8px;font-weight:750;letter-spacing:.12em;text-transform:uppercase}.claim-chip strong{font-size:12px;letter-spacing:.03em}
        .field{margin-top:15px}.field label{display:block;margin-bottom:7px;color:#344e59;font-size:11px;font-weight:700}.field input{width:100%;height:46px;padding:0 13px;border:1px solid #d7e0e3;border-radius:5px;background:#fff;color:#1b3441;font:inherit;font-size:14px;outline:0;transition:border .15s,box-shadow .15s}.field input::placeholder{color:#a2afb4}.field input:focus{border-color:#52879a;box-shadow:0 0 0 3px #14536b13}
        .submit{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;min-height:49px;margin-top:20px;border:0;border-radius:6px;background:var(--blue);color:#fff;font-size:12px;font-weight:750;cursor:pointer;transition:background .15s}.submit:hover{background:var(--blue-dark)}.submit:disabled{opacity:.7;cursor:wait}
        .error{margin:0 0 16px;padding:11px 12px;border:1px solid #f0d3d0;border-radius:5px;background:#fff5f4;color:#a33e37;font-size:11px;line-height:1.5}.message{min-height:17px;margin:11px 0 0;color:#536e79;font-size:10px;line-height:1.5}.footer{display:flex;justify-content:space-between;gap:18px;width:min(1160px,calc(100% - 48px));margin:0 auto;padding:19px 0;border-top:1px solid #e1e8e9;color:#85939a;font-size:9px}.footer a{color:#55717c;text-decoration:none}
        @media(max-width:900px){.main{grid-template-columns:minmax(0,1fr) minmax(350px,.9fr);gap:36px}.form-card{padding:26px}}
        @media(max-width:760px){.topbar{height:64px;padding:0 18px}.secure-label{font-size:0}.secure-label i{font-size:14px}.main{width:min(100% - 32px,480px);margin:24px auto 30px;grid-template-columns:1fr;gap:20px;align-content:start}.intro{max-width:none}.eyebrow{margin-bottom:11px;font-size:9px}.intro h1{font-size:clamp(30px,8vw,38px);line-height:1.08}.intro-copy{margin:10px 0 15px;font-size:13px;line-height:1.5}.price-card{max-width:none;padding:12px 14px}.price-value{font-size:23px}.charge-note{margin-top:8px;font-size:10px}.form-card{padding:21px 18px;border-radius:9px}.form-heading{margin-bottom:15px}.field{margin-top:14px}.field input{height:48px;font-size:16px}.submit{min-height:49px;flex-wrap:wrap;gap:7px}.footer{width:calc(100% - 32px);flex-direction:column;gap:5px;margin:0 auto;font-size:10px}}
        @media(prefers-reduced-motion:reduce){*,*:before,*:after{scroll-behavior:auto!important;transition:none!important}}
    </style>
</head>
<body>
<div class="page">
    <header class="topbar"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">@if($brandSettings["brand_logo_url"])<img src="{{ $brandSettings["brand_logo_url"] }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="fa-solid fa-credit-card"></i>@endif</span><span class="brand-name">{{ $brandSettings["brand_name"] }}<small>{{ $brandSettings["brand_tagline"] }}</small></span></a><span class="secure-label"><i class="fa-solid fa-shield-halved"></i> Secure checkout</span></header>
    <main class="main">
        <section class="intro" aria-labelledby="page-title">
            <p class="eyebrow">Secure payment</p>
            <h1 id="page-title">{{ $paymentSettings['checkout_title'] }}</h1>
            <p class="intro-copy">{{ $paymentSettings['checkout_message'] }}</p>
            <div class="price-card"><div><div class="price-label">{{ $paymentSettings['refund_verification'] ? 'Temporary authorization' : $paymentSettings['payment_label'] }}</div><div class="price-value">{{ number_format($paymentSettings['verification_amount'] / 100, 2) }} <small>{{ $paymentSettings['verification_currency'] }}</small></div></div><span class="payment-tag"><i class="fa-solid fa-lock"></i> Secure checkout</span></div>
            @if($paymentSettings['refund_verification'])<p class="charge-note">This amount will be refunded after the payment is confirmed.</p>@endif
        </section>
        <section class="form-card" aria-labelledby="form-title">
            <div class="form-heading"><h2 id="form-title">Your details</h2></div>
            <div class="claim-chip"><i class="fa-solid fa-receipt"></i><span><small>{{ $paymentSettings['payment_reference_label'] }}</small><strong>{{ $claim->claim_reference }}</strong></span></div>
            @if(isset($error))<div class="error"><i class="fa-solid fa-circle-exclamation"></i> {{ $error }}</div>@endif
            @if(session('error'))<div class="error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>@endif
            <form id="verify-form">
                <div class="field"><label for="name">Full name</label><input id="name" required maxlength="120" autocomplete="name" value="{{ old('name', $claim->customer_name === 'Customer' ? '' : $claim->customer_name) }}" placeholder="Full name"></div>
                <div class="field"><label for="email">Email address</label><input id="email" type="email" required autocomplete="email" value="{{ old('email', str_ends_with($claim->customer_email, '@verification.invalid') ? '' : $claim->customer_email) }}" placeholder="name@example.com"></div>
                <button id="submit" class="submit" type="submit"><i class="fa-solid fa-lock"></i><span>Continue</span><b>{{ number_format($paymentSettings['verification_amount'] / 100, 2) }} {{ $paymentSettings['verification_currency'] }}</b></button>
                <p id="message" class="message" role="status" aria-live="polite"></p>
            </form>
        </section>
    </main>
    <footer class="footer"><span>© {{ date('Y') }} {{ $brandSettings["brand_name"] }}. {{ $brandSettings["brand_footer_text"] }}</span><span>Need help with this payment? @if($paymentSettings['support_email'])Contact <a href="mailto:{{ $paymentSettings['support_email'] }}">{{ $paymentSettings['support_email'] }}</a>.@elseif($paymentSettings['support_phone'])Contact {{ $paymentSettings['support_phone'] }}.@elseContact support for assistance.@endif</span></footer>
</div>
@if($paymentSettings['gateway'] === 'paystack')<script src="https://js.paystack.co/v2/inline.js"></script>@endif
<script>
const form=document.getElementById('verify-form'),button=document.getElementById('submit'),message=document.getElementById('message');
const resetButton=()=>{button.disabled=false;button.querySelector('span').textContent='Continue';};
form.addEventListener('submit',async event=>{event.preventDefault();button.disabled=true;button.querySelector('span').textContent='Connecting…';message.textContent='';
 try{const response=await fetch(@json(route('verify.initialize',$claim)),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({name:document.getElementById('name').value,email:document.getElementById('email').value})});const data=await response.json();if(!response.ok)throw new Error(data.message||'Could not start checkout. Please check your details and try again.');
 @if($paymentSettings['gateway'] === 'paystack')
 if(data.access_code&&window.PaystackPop){const popup=new PaystackPop();popup.resumeTransaction(data.access_code);const statusUrl=@json(route('verify.status',['token'=>$claim->public_token,'reference'=>'REFERENCE'])).replace('REFERENCE',encodeURIComponent(data.reference));const completeUrl=@json(route('verify.complete',['token'=>$claim->public_token,'gateway'=>$paymentSettings['gateway']]));let checks=0;const poll=setInterval(async()=>{checks++;try{const statusResponse=await fetch(statusUrl,{headers:{'Accept':'application/json'}}),payment=await statusResponse.json();if(payment.status==='success'){clearInterval(poll);window.location.assign(completeUrl+'?reference='+encodeURIComponent(data.reference));return;}}catch(error){}if(checks>=24){clearInterval(poll);message.textContent='If you completed payment, contact support with your payment reference.';resetButton();}},2500);return;}
 @endif
 if(data.redirect_url){window.location.assign(data.redirect_url);return;}throw new Error('Secure checkout could not be opened. Please try again.');
 }catch(error){message.textContent=error.message;resetButton();}
});
</script>
</body>
</html>


