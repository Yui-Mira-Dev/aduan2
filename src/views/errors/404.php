<?php
$MainLink = "https://yui.my.id/";

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>404 Not Found</title>
    <link rel="stylesheet" href="<?php echo $MainLink ?>assets/css/forbiden.css">
    <link rel="icon" href="logopusri">
</head>

<body>
    <div class="page">
        <div class="main">
            <h1>Page Not Found</h1>
            <div class="error-code">404</div>
            <h2>Not Found</h2>
            <p class="lead">The page you are looking for might have been moved or deleted.</p>
            <hr />
            <p>What you can do:</p>
            <div class="help-actions">
                <a href="javascript:location.reload();">Reload Page</a>
                <a href="javascript:history.back();">Back to Previous Page</a>
                <a href="<?php echo $MainLink; ?>">Return to Home Page</a>
            </div>
        </div>
    </div>
</body>

</html>