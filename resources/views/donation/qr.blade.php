<!DOCTYPE html>
<html>
<head>
    <title>الدفع عبر شام كاش</title>
</head>
<body style="font-family: Arial; background:#f5f5f5; padding:30px;">

<div style="max-width:500px; margin:auto; background:white; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">

    <h2 style="text-align:center;">الدفع عبر شام كاش</h2>

    {{-- رقم الحساب --}}
    <div style="margin-top:20px;">
        <label><strong>رقم الحساب</strong></label>
        <div style="display:flex; gap:10px; margin-top:5px;">
            <input
                type="text"
                value="{{ $account }}"
                id="account"
                readonly
                style="flex:1; padding:10px;"
            >
            <button onclick="copyAccount()" style="padding:10px;">
                نسخ
            </button>
        </div>
    </div>

    {{-- المبلغ --}}
    <div style="margin-top:20px;">
        <label><strong>المبلغ المطلوب</strong></label>
        <input
            type="text"
            value="{{ $donation->contribution_amount }}"
            readonly
            style="width:100%; padding:10px; margin-top:5px;"
        >
    </div>

    {{-- التعليمات --}}
    <div style="margin-top:20px; color:#555; line-height:1.6;">
        <strong>طريقة الدفع:</strong><br>
        1. افتح تطبيق ShamCash<br>
        2. اختر "تحويل"<br>
        3. الصق رقم الحساب<br>
        4. أدخل نفس المبلغ<br>
        5. أكمل عملية الدفع<br>
        6. انسخ رقم العملية وضعه بالأسفل
    </div>

    {{-- الأخطاء --}}
    @if(isset($errors) && $errors->any())
        <div style="color:red; margin-top:15px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- النجاح --}}
    @if (session('success'))
        <div style="color:green; margin-top:15px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- إدخال رقم العملية --}}
    <form method="POST" action="{{ route('donation.verify') }}" style="margin-top:20px;">
        @csrf

        <input type="hidden" name="donation_id" value="{{ $donation->id }}">

        <input
            type="text"
            name="transaction_id"
            placeholder="أدخل رقم العملية هنا"
            required
            style="width:100%; padding:10px;"
        >

        <button type="submit" style="margin-top:15px; width:100%; padding:12px; background:#28a745; color:white; border:none;">
            تأكيد الدفع
        </button>
    </form>

</div>

<script>
function copyAccount() {
    let acc = document.getElementById("account");
    acc.select();
    acc.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(acc.value);
    alert("تم نسخ رقم الحساب");
}
</script>

</body>
</html>
