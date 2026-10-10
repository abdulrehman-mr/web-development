<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Redirecting to {{ $gateway }}…</title>
    <style>body{font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;color:#0f172a}button{margin-top:1rem;padding:.75rem 1.25rem;border:0;border-radius:.75rem;background:#097045;color:#fff;font-weight:600;cursor:pointer}</style>
</head>
<body>
    <form id="redirect" method="post" action="{{ $url }}" style="text-align:center">
        @foreach ($fields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <p>Taking you to {{ $gateway }} to complete your payment securely…</p>
        <noscript><button>Continue to {{ $gateway }}</button></noscript>
    </form>
    <script>document.getElementById('redirect').submit();</script>
</body>
</html>
