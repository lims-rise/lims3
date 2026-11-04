<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #14213d; line-height: 1.5;">
    <h2 style="margin-bottom: 0;">RISE | LIMS3.0</h2>
    <p>Hello {{ $name }},</p>
    <p>
        We received a request to reset the password of your account. Click the button below to choose a new password.
        This link is valid for {{ $minutes }} minutes and can be used only once.
    </p>
    <p>
        <a href="{{ $url }}" style="background: #2d5fd0; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none; display: inline-block;">
            Reset password
        </a>
    </p>
    <p>If the button does not work, copy this address into your browser:<br>
       <span style="word-break: break-all;">{{ $url }}</span></p>
    <p>If you did not request this, you can ignore this email. Your password will stay the same.</p>
</body>
</html>