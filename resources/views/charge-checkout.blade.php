<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f6f7">
    <link rel="icon" href="{{ $brandSettings['brand_logo_url'] ?: asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <title>Secure payment | {{ $brandSettings['brand_name'] }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#f4f6f7;color:#1b2d3a;font:15px/1.55 Inter,"Segoe UI",Arial,sans-serif}.card{width:min(100%,520px);padding:32px;background:white;border:1px solid #e3e8eb;border-radius:12px;box-shadow:0 16px 44px #233c4b12}.brand{margin:0 0 22px;color:{{ $brandSettings['brand_primary_color'] }};font-size:13px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}h1{margin:0;font-size:25px;letter-spacing:-.03em}.sub{margin:8px 0 23px;color:#72818b}.summary{padding:16px;border:1px solid #e3e8eb;border-radius:8px;background:#f9fbfb}.summary small{display:block;color:#72818b}.summary strong{display:block;margin-top:4px;font-size:24px}.description{margin:12px 0 0;color:#526670}.button{width:100%;margin-top:22px;padding:13px 16px;border:0;border-radius:6px;background:{{ $brandSettings['brand_primary_color'] }};color:#fff;font-size:15px;font-weight:700;cursor:pointer}.button:disabled{opacity:.65;cursor:wait}.message{min-height:24px;margin:12px 0 0;color:#a3443d}.fine{margin:14px 0 0;color:#72818b;font-size:12px}.ok{padding:14px;border-radius:7px;background:#eaf4ef;color:#28634d}.err{padding:14px;border-radius:7px;background:#fff1ef;color:#913d37}a{color:{{ $brandSettings['brand_primary_color'] }}}
    </style>
</head>
<body>
    <main class="card">
        <p class="brand">{{ $brandSettings['brand_name'] }}</p>
        @if($completed)
            <div class="ok"><h1>Payment confirmed</h1><p>Your payment was received successfully. You can close this page.</p></div>
        @else
            <h1>Complete your payment</h1>
            <p class="sub">Hi {{ $parent->claim?->customer_name ?? 'there' }}, continue through secure Paystack checkout. Your bank may request an OTP or other confirmation.</p>
            @if(!empty($error))<div class="err">{{ $error }}</div>@endif
            <div class="summary"><small>Amount due</small><strong>{{ number_format($parent->amount / 100, 2) }} {{ $parent->currency }}</strong><p class="description">{{ $parent->description }}</p></div>
            <button id="pay" class="button" type="button"><i class="fa-solid fa-lock"></i> Continue to secure checkout</button>
            <p id="message" class="message" role="status" aria-live="polite"></p>
            <p class="fine">This is a new checkout attempt. A declined attempt will not be charged again.</p>
        @endif
        <p class="fine">{{ $paymentSettings['support_email'] ? 'Need help? '.$paymentSettings['support_email'] : 'Contact support if you need help.' }}</p>
    </main>
    @unless($completed)<script src="https://js.paystack.co/v2/inline.js"></script><script>
        const button=document.getElementById('pay'),message=document.getElementById('message');
        button.addEventListener('click',async()=>{
            button.disabled=true;message.textContent='Starting secure checkout…';
            try{
                const response=await fetch(@json(route('charge-checkout.initialize',$checkoutToken)),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())}});
                const data=await response.json();if(!response.ok)throw new Error(data.message||'Checkout could not be started.');
                if(data.status==='success'){window.location.href=@json(route('charge-checkout.show',$checkoutToken))+'?success=1';return;}
                if(data.access_code&&window.PaystackPop){const popup=new PaystackPop();popup.resumeTransaction(data.access_code);const statusUrl=@json(route('charge-checkout.status',['token'=>$checkoutToken,'reference'=>'REFERENCE']));let checks=0;const poll=setInterval(async()=>{checks++;try{const res=await fetch(statusUrl.replace('REFERENCE',encodeURIComponent(data.reference)),{headers:{'Accept':'application/json'}});const result=await res.json();if(result.status==='success'){clearInterval(poll);window.location.href=@json(route('charge-checkout.show',$checkoutToken))+'?success=1';return;}if(['failed','abandoned'].includes(String(result.status).toLowerCase())){clearInterval(poll);message.textContent='The bank did not approve this payment. Please contact your bank or use another card.';button.disabled=false;return;}}catch(error){}if(checks>=36){clearInterval(poll);message.textContent='We have not received confirmation yet. Please check with support before trying again.';button.disabled=false;}},2000);return;}
                if(data.redirect_url){window.location.href=data.redirect_url;return;}throw new Error('Secure checkout could not be opened.');
            }catch(error){message.textContent=error.message;button.disabled=false;}
        });
    </script>@endunless
</body>
</html>
