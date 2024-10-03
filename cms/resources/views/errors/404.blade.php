<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .container {
            background-color: white;
            padding: 2rem 3rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-50px);
            animation: slideDown 0.5s ease forwards;
        }

        h1 {
            font-size: 4rem;
            margin: 0;
            color: #ff6b6b;
        }

        p {
            font-size: 1.2rem;
            margin-top: 1rem;
            color: #666;
        }

        .btn {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.8rem 1.5rem;
            background-color: #3498db;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        @keyframes slideDown {
            0% {
                opacity: 0;
                transform: translateY(-100px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .container p {
            animation: fadeIn 1s ease forwards;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>404</h1>
        <p>Sorry, the page you are looking for could not be found.</p>
        <a href="{{ url('/') }}" class="btn">Go to Homepage</a>
    </div>
</body>

</html>