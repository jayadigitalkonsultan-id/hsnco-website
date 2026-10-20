<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light"><title>@yield('title') | HSNCO</title></head>
<body style="margin:0;padding:0;background-color:#f2f1ec;color:#243832;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
<div style="display:none;font-size:1px;color:#f2f1ec;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">@yield('preheader')</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f2f1ec;"><tr><td align="center" style="padding:32px 12px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background-color:#ffffff;border:1px solid #dedfd7;">
<tr><td style="padding:30px 32px;background-color:#183e34;border-bottom:4px solid #b99b62;">
<p style="margin:0;color:#ffffff;font-family:Georgia,serif;font-size:30px;letter-spacing:3px;">HS &amp; CO</p>
<p style="margin:8px 0 0;color:#e4d8bb;font-size:10px;letter-spacing:2px;">COUNSELLORS AT LAW</p>
</td></tr>
<tr><td style="padding:34px 28px;">
<p style="margin:0 0 14px;color:#78643e;font-size:11px;font-weight:bold;letter-spacing:2px;">PORTAL ADMINISTRASI HSNCO</p>
<h1 style="margin:0 0 22px;font-family:Georgia,serif;font-size:29px;line-height:1.3;font-weight:normal;color:#183e34;">@yield('title')</h1>
@yield('content')
</td></tr>
<tr><td style="padding:22px 28px;background-color:#faf9f5;border-top:1px solid #e7e7df;">
<p style="margin:0;font-size:12px;line-height:1.8;color:#65716b;">Email otomatis dari portal HSNCO. Jangan membagikan tautan aktivasi, kata sandi, atau kode verifikasi kepada siapa pun.</p>
<p style="margin:12px 0 0;font-size:11px;line-height:1.6;color:#65716b;">&copy; {{ date('Y') }} HS &amp; CO Counsellors at Law</p>
</td></tr>
</table>
</td></tr></table>
</body></html>