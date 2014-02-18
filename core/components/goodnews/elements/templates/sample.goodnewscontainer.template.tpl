<!DOCTYPE html>
<html lang="[[++cultureKey]]">
<head>
    <meta charset="[[++modx_charset]]">
    <title>[[++site_name]] | [[*pagetitle]]</title>
    <base href="[[++site_url]]">
    <style type="text/css">
        * { margin: 0; padding: 0; }
        button,
        body {
            background-color: #515151;
            font-family: sans-serif;
            font-size: 17px;
            color: #666;
            line-height: 1.4;
            padding: 0 15px;
        }
        p { margin-bottom: 20px; }
        p:last-child { margin: 0; }
        p a { color: #000; text-decoration: none; }
        strong { font-weight: normal; color: #9ec41a; }
        .container {
            max-width: 640px;
            margin: 30px auto;
            background-color: #f6f6f6;
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background-color: #9ec41a;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: normal;
            color: #fff;
            text-align: center;
            padding: 30px;
        }
        .main {
            margin: 30px;
        }
        .main h2 {
            font-size: 20px;
            font-weight: normal;
            margin-bottom: 15px;
        }
        .errorMsg {
            font-size: 18px;
            text-align: center;
            padding: 8px;
            margin-bottom: 20px;
            color: #f2430e;
            background-color: #f9dfd7;
            border-radius: 5px;
        }
        .newsletterrow {
            margin-bottom: 15px;
        }
        .newslettertitle {
            font-size: 18px;
            font-weight: normal;
            margin-bottom: 0;
        }
        .newslettertitle a {
            text-decoration: none;
            color: #9ec41a;
        }
        .newslettertitle a:hover {
            text-decoration: underline;
            color: #9ec41a;
        }
        .newsletterintro {
            font-size: 15px;
            color: #999;
        }
        .aside { margin: 30px; }
        .aside a {
            display: block;
            margin-top: 15px;
            text-align: center;
            color: #000;
            text-decoration: none;
        }
        .footer {
            margin: 0 -30px;
            padding: 15px 30px;
            font-size: 14px;
            background-color: #e2e2e2;
        }
        .footer p {
            text-align: center;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>[[++site_name]]</h1>
        </div>
        <div class="main">
            <h2>Our previously sent newsletters</h2> 
            <p>From here you have access to all of our previously sent newsletters. We currently have [[+total]] mailings in our archive:</p>
            [[*content]]
        </div>
        <div class="footer">
            <p>&copy; Copyright [[++site_name]]</p>
        </div>
    </div>
</body>
</html>