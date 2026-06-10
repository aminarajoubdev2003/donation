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
            <h1>🎉 حملة جديدة</h1>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:30px; text-align:right;">

            <h2 style="color:#333;"><?php echo e($campaign->name); ?></h2>

            <p style="color:#666; line-height:1.8;">
                <?php echo e($campaign->description); ?>

            </p>

            <!-- Button -->
            <div style="text-align:center; margin:30px 0;">
                <a href="<?php echo e(url('/campaigns/'.$campaign->id)); ?>"
                   style="background:#014a5b; color:#fff; padding:15px 25px; text-decoration:none; border-radius:5px; font-size:16px;border-radius:99px;">
                 تبرع الآن
                </a>
            </div>

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="background:#f1f1f1; padding:15px; text-align:center; font-size:12px; color:#999;">
            جميع الحقوق محفوظة © <?php echo e(date('Y')); ?>

        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
<?php /**PATH C:\laragon\www\donation\resources\views/emails/new_campaign.blade.php ENDPATH**/ ?>