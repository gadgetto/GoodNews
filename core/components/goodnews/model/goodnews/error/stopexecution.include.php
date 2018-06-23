<?php
header($_SERVER['SERVER_PROTOCOL'].' '.$statuscode);
?>
<html>
<head>
<title><?php echo $statuscode; ?></title>
<style type="text/css">
* {
    margin: 0;
    padding: 0;
}
body {
    padding: 50px;
    background: #eee;
}
.message {
    padding: 20px;
    border: 1px solid #f22;
    background: #f99;
    font-family: sans-serif;
    text-align: center;
}
h1 {
    font-size: 2rem;
    margin-bottom: 20px;
}
p {
    font-size: 1.2rem;
}
</style>
</head>
<body>
<div class="message">
    <?php echo '<h1>'.$statuscode.'</h1><p>'.$description.'</p>'; ?>
</div>
</body>
<?php
@session_write_close();
exit();
?>
