<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Platform</title>
</head>
<body>
    <h1>Welcome, {{ $user['first_name'] }} , {{$user['last_name']}}!</h1>
    <p>Thank you for registering with us. We are excited to have you onboard!</p>
    <p>Your account has been successfully created on LOF. You can now log in with the following credentials:</p>
    <ul>
        <li>Email: {{ $user['email'] }}</li>
        <li>Password: {{ $password }}</li>

    </ul>
    <p>For Login You need to verify your email, please click the link below:</p>
        <a href="{{ $verificationUrl }}">Verify Email</a>
    <p>If you have any questions, feel free to reach out to us.</p>
    <p>Best regards,</p>
    <p>Your Company Team</p>
</body>
</html>
