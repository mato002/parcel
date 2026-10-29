<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7f8">
    <title>Secure card verification | Parcel Claims</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        :root{color-scheme:light;--ink:#172c38;--muted:#637782;--line:#dfe7e9;--blue:#14536b;--blue-dark:#103e52;--teal:#2c826d;--canvas:#f3f6f7;--white:#fff}
        *{box-sizing:border-box}body{margin:0;background:var(--canvas);color:var(--ink);font-family:Inter,"Segoe UI",Arial,sans-serif;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;flex-direction:column}.topbar{height:76px;display:flex;align-items:center;justify-content:space-between;padding:0 max(28px,calc((100vw - 1160px)/2));border-bottom:1px solid #e4eaec;background:#fff}
        .brand{display:flex;align-items:center;gap:11px;color:var(--ink);text-decoration:none}.brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:7px;background:var(--blue);color:#fff;font-size:15px}.brand-name{font-size:12px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.brand-name small{display:block;margin-top:3px;color:#71858e;font-size:9px;font-weight:650;letter-spacing:.15em}
        .secure-label{display:flex;align-items:center;gap:8px;color:#58716f;font-size:11px;font-weight:650}.secure-label i{color:var(--teal)}
        .main{width:min(1160px,calc(100% - 48px));margin:60px auto;flex:1;display:grid;grid-template-columns:minmax(0,1fr) minmax(390px,.82fr);align-items:center;gap:80px}
        .intro{max-width:510px}.eyebrow{display:flex;align-items:center;gap:9px;margin:0 0 18px;color:var(--teal);font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}.eyebrow:before{content:"";width:22px;height:2px;background:#62a18c}
        h1{max-width:500px;margin:0;color:#173141;font-size:44px;line-height:1.09;letter-spacing:-.045em}.intro-copy{max-width:440px;margin:18px 0 29px;color:#61747e;font-size:15px;line-height:1.75}
        .price-card{display:flex;align-items:center;justify-content:space-between;max-width:450px;padding:18px 20px;border:1px solid #dce6e7;border-radius:8px;background:#fff;box-shadow:0 8px 25px #15394909}.price-label{color:#5b7079;font-size:11px;font-weight:650}.price-value{margin-top:4px;color:#173849;font-size:27px;font-weight:780;letter-spacing:-.04em}.price-value small{margin-left:5px;color:#617781;font-size:11px;letter-spacing:.02em}.payment-tag{display:flex;align-items:center;gap:6px;padding:7px 9px;border-radius:4px;background:#eef6f2;color:#35745b;font-size:10px;font-weight:700}.payment-tag i{font-size:10px}
        .explain{max-width:450px;margin-top:17px;padding:14px 16px;border-left:3px solid #76a995;background:#eaf2ef;color:#536c64;font-size:11px;line-height:1.6}.explain strong{color:#2c5f4d}.steps{display:flex;gap:0;margin-top:29px;max-width:470px}.step{position:relative;flex:1;padding-right:14px;color:#71818a;font-size:10px;line-height:1.45}.step:not(:last-child):after{position:absolute;top:11px;left:24px;width:calc(100% - 38px);height:1px;background:#d5dfdf;content:""}.step-number{position:relative;z-index:1;display:grid;place-items:center;width:23px;height:23px;margin-bottom:9px;border:1px solid #d3dfdf;border-radius:50%;background:var(--canvas);color:#315e68;font-size:10px;font-weight:750}.step strong{display:block;margin-bottom:3px;color:#344e59;font-size:10px}
        .form-card{padding:31px 32px;border:1px solid #e1e8ea;border-radius:9px;background:#fff;box-shadow:0 18px 50px #16384912}.form-heading{margin-bottom:22px}.form-heading h2{margin:0;color:#1c3542;font-size:20px;letter-spacing:-.025em}.form-heading p{margin:7px 0 0;color:#75868e;font-size:11px;line-height:1.55}.claim-chip{display:flex;align-items:center;gap:9px;margin:18px 0 21px;padding:11px 12px;border:1px solid #e7edef;border-radius:5px;background:#f8fafb}.claim-chip i{color:#548492}.claim-chip small{display:block;margin-bottom:3px;color:#7c8c92;font-size:8px;font-weight:750;letter-spacing:.12em;text-transform:uppercase}.claim-chip strong{font-size:11px;letter-spacing:.03em}
        .field{margin-top:16px}.field label{display:block;margin-bottom:7px;color:#344e59;font-size:10px;font-weight:700}.field input{width:100%;height:44px;padding:0 12px;border:1px solid #d7e0e3;border-radius:5px;background:#fff;color:#1b3441;font:inherit;font-size:12px;outline:0;transition:border .15s,box-shadow .15s}.field input::placeholder{color:#a2afb4}.field input:focus{border-color:#52879a;box-shadow:0 0 0 3px #14536b13}
        .submit{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;min-height:47px;margin-top:21px;border:0;border-radius:5px;background:var(--blue);color:#fff;font-size:12px;font-weight:750;cursor:pointer;transition:background .15s}.submit:hover{background:var(--blue-dark)}.submit:disabled{opacity:.7;cursor:wait}
        .secure-note{display:flex;gap:10px;margin-top:19px;padding-top:16px;border-top:1px solid #edf0f1;color:#74838a;font-size:10px;line-height:1.6}.secure-note i{margin-top:2px;color:var(--teal)}.secure-note strong{color:#4b656e}
        .error{margin:0 0 16px;padding:11px 12px;border:1px solid #f0d3d0;border-radius:5px;background:#fff5f4;color:#a33e37;font-size:11px;line-height:1.5}.message{min-height:17px;margin:11px 0 0;color:#536e79;font-size:10px;line-height:1.5}.footer{display:flex;justify-content:space-between;gap:18px;width:min(1160px,calc(100% - 48px));margin:0 auto;padding:19px 0;border-top:1px solid #e1e8e9;color:#85939a;font-size:9px}.footer a{color:#55717c;text-decoration:none}
        @media(max-width:900px){.main{grid-template-columns:1fr minmax(350px,.9fr);gap:36px}.intro h1{font-size:37px}.form-card{padding:26px}}
        @media(max-width:700px){.topbar{height:58px;padding:0 14px}.secure-label{font-size:0}.secure-label i{font-size:14px}.main{width:min(500px,calc(100% - 28px));margin:21px auto;grid-template-columns:1fr;gap:16px}.intro{max-width:none}.intro h1{font-size:29px;line-height:1.12}.intro-copy{margin:9px 0 13px;font-size:13px;line-height:1.5}.price-card{max-width:none;padding:12px 14px}.price-value{font-size:22px}.explain{margin-top:9px;padding:10px 12px;font-size:10px;line-height:1.45}.steps{display:none}.form-card{padding:19px 16px;border-radius:8px}.form-heading{margin-bottom:13px}.field{margin-bottom:13px}.field input{height:46px;font-size:16px}.submit{min-height:48px;flex-wrap:wrap;gap:7px}.secure-note{margin-top:17px;padding-top:13px;font-size:10px;line-height:1.45}.footer{width:calc(100% - 28px);flex-direction:column;gap:5px;margin:22px auto;font-size:10px}}
        @media(prefers-reduced-motion:reduce){*,*:before,*:after{scroll-behavior:auto!important;transition:none!important}}
    </style>
</head>
<body>
<div class="page">
    <header class="topbar"><a class="brand" href="{{ route('home') }}"><span class="brand-mark"><i class="fa-solid fa-box"></i></span><span class="brand-name">Parcel Claims<small>Customer verification</small></span></a><span class="secure-label"><i class="fa-solid fa-shield-halved"></i> Secure payment verification</span></header>
    <main class="main">
        <section class="intro" aria-labelledby="page-title">
            <p class="eyebrow">Card verification</p>
            <h1 id="page-title">Verify your card to continue your claim.</h1>
            <p class="intro-copy">Confirm the details for your parcel claim and complete a one-time payment through {{ $gatewayName }}. Secure checkout opens over this page, and your bank may ask you to approve the payment with a one-time code.</p>
            <div class="price-card"><div><div class="price-label">One-time verification payment</div><div class="price-value">{{ number_format($paymentSettings['verification_amount'] / 100, 2) }} <small>{{ $paymentSettings['verification_currency'] }}</small></div></div><span class="payment-tag"><i class="fa-solid fa-lock"></i> Secure checkout</span></div>
            <div class="explain"><strong>What happens to this payment?</strong><br>@if($paymentSettings['refund_verification'])After {{ $gatewayName }} confirms your card, we automatically request a refund of this verification payment. The time it takes to appear depends on your bank.@elseThis is a real payment to verify your card. It is not automatically refunded.@endif</div>
            <div class="steps" aria-label="Verification steps"><div class="step"><span class="step-number">1</span><strong>Your details</strong>Confirm your name and email</div><div class="step"><span class="step-number">2</span><strong>Secure payment</strong>{{ $gatewayName }} checkout and bank approval</div><div class="step"><span class="step-number">3</span><strong>Claim continues</strong>Our team can proceed</div></div>
        </section>
        <section class="form-card" aria-labelledby="form-title">
            <div class="form-heading"><h2 id="form-title">Your details</h2><p>Check that your contact information is correct before continuing to payment.</p></div>
            <div class="claim-chip"><i class="fa-solid fa-box-open"></i><span><small>Parcel / claim reference</small><strong>{{ $claim->claim_reference }}</strong></span></div>
            @if(session('error'))<div class="error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>@endif
            <form id="verify-form">
                <div class="field"><label for="name">Full name</label><input id="name" required maxlength="120" autocomplete="name" value="{{ old('name', $claim->customer_name === 'Customer' ? '' : $claim->customer_name) }}" placeholder="Enter your full name"></div>
                <div class="field"><label for="email">Email address</label><input id="email" type="email" required autocomplete="email" value="{{ old('email', str_ends_with($claim->customer_email, '@verification.invalid') ? '' : $claim->customer_email) }}" placeholder="you@example.com"></div>
                <button id="submit" class="submit" type="submit"><i class="fa-solid fa-lock"></i><span>Continue to secure payment</span><b>{{ number_format($paymentSettings['verification_amount'] / 100, 2) }} {{ $paymentSettings['verification_currency'] }}</b></button>
                <p id="message" class="message" role="status" aria-live="polite"></p>
            </form>
            <div class="secure-note"><i class="fa-solid fa-shield-halved"></i><span><strong>Card details stay with {{ $gatewayName }}.</strong> Your full card number and security code are entered in the provider’s secure checkout. We never see or store them.</span></div>
        </section>
    </main>
    <footer class="footer"><span>© {{ date('Y') }} Parcel Claims</span><span>Questions about this claim? @if($paymentSettings['support_email'])Contact <a href="mailto:{{ $paymentSettings['support_email'] }}">{{ $paymentSettings['support_email'] }}</a>.@elseContact our claims team.@endif</span></footer>
</div>
@if($paymentSettings['gateway'] === 'paystack')<script src="https://js.paystack.co/v2/inline.js"></script>@endif
<script>
const form=document.getElementById('verify-form'),button=document.getElementById('submit'),message=document.getElementById('message');
const resetButton=()=>{button.disabled=false;button.querySelector('span').textContent='Continue to secure payment';};
form.addEventListener('submit',async event=>{event.preventDefault();button.disabled=true;button.querySelector('span').textContent='Connecting securely…';message.textContent='Preparing your secure checkout…';
 try{const response=await fetch(@json(route('verify.initialize',$claim)),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({name:document.getElementById('name').value,email:document.getElementById('email').value})});const data=await response.json();if(!response.ok)throw new Error(data.message||'Could not start verification. Please check your details and try again.');
 @if($paymentSettings['gateway'] === 'paystack')
 if(data.access_code&&window.PaystackPop){const popup=new PaystackPop();popup.resumeTransaction(data.access_code);message.textContent='Complete your payment in the secure Paystack window.';const statusUrl=@json(route('verify.status',['token'=>$claim->public_token,'reference'=>'REFERENCE'])).replace('REFERENCE',encodeURIComponent(data.reference));const completeUrl=@json(route('verify.complete',['token'=>$claim->public_token,'gateway'=>'paystack']));let checks=0;const poll=setInterval(async()=>{checks++;try{const statusResponse=await fetch(statusUrl,{headers:{'Accept':'application/json'}}),payment=await statusResponse.json();if(payment.status==='success'){clearInterval(poll);window.location.assign(completeUrl+'?reference='+encodeURIComponent(data.reference));return;}}catch(error){}if(checks>=24){clearInterval(poll);message.textContent='Checkout closed or is taking longer than expected. If you completed payment, contact support with your claim reference.';resetButton();}},2500);return;}
 @endif
 if(data.redirect_url){window.location.assign(data.redirect_url);return;}throw new Error('Secure checkout could not be opened. Please try again.');
 }catch(error){message.textContent=error.message;resetButton();}
});
</script>
</body>
</html>


