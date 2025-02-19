<?php
$MainLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/";
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>403 Forbidden</title>
    <link rel="stylesheet" href="<?php echo $MainLink ?>src/pages/dist/css/forbiden.css">
    <link rel="icon" href="logopusri">
</head>

<body>
    <div class="page">
        <div class="main">
            <h1>Server Error</h1>
            <div class="error-code">403</div>
            <h2>Forbidden</h2>
            <p class="lead">You do not have permission to access this document.</p>
            <hr />
            <p>That's what you can do</p>
            <div class="help-actions">
                <a href="javascript:location.reload();">Reload Page</a>
                <a href="javascript:history.back();">Back to Previous Page</a>
                <a href="<?php echo $MainLink ?>">Home Page</a>
            </div>
        </div>
    </div>
</body>

</html>