<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Test Page - It Works!</h1>
    <p>Customer ID: {{ $customer['id'] ?? 'No ID' }}</p>
    <p>Customer Name: {{ $customer['name'] ?? 'No Name' }}</p>
</body>
</html>