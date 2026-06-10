<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>حملة جديدة</title>
</head>
<body style="margin:0; padding:0; font-family:Arial; background:#f4f4f4; direction:rtl;">

<table width="100%" bgcolor="#f4f4f4" cellpadding="0" cellspacing="0">
<tr>
<td align="center">

<table width="600" bgcolor="#ffffff" style="margin:20px; border-radius:10px; overflow:hidden;">

    <!-- Header -->
    <tr>
        <td style="background:#014a5b; color:#fff; padding:20px; text-align:center;">
            <h1>رمز التحقق الخاص بك</h1>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:30px; text-align:right;">

            <h2 style="color:#333;"> رمز OTP هو:</h2>

            <p style="color:#666; line-height:1.8;">
                <strong>{{ $otp }}</strong>
            </p>
            <p>
             صلاحية الرمز 10 دقائق.
            </p>

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#999;">
            جميع الحقوق محفوظة © {{ date('Y') }}
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
