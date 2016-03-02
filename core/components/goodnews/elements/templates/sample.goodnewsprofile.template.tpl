<!DOCTYPE html>
<html lang="[[++cultureKey]]">
<head>
    <meta charset="[[++modx_charset]]">
    <title>[[++site_name]] | [[*pagetitle]]</title>
    <base href="[[++site_url]]">
    <style type="text/css">
        * { margin: 0; padding: 0; }
        button,
        input {
            font-size: 100%;
            margin: 0;
            vertical-align: baseline;
            *vertical-align: middle;
        }
        input[type="checkbox"] {
            box-sizing: border-box;
            padding: 0;
            *height: 13px;
            *width: 13px;
        }
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
            color: #333;
            margin-bottom: 15px;
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
        .errorMsg {
            display: block;
            overflow: hidden;
            font-size: 18px;
            text-align: center;
            padding: 8px;
            margin-bottom: 20px !important;
            color: #f2430e;
            background-color: #f9dfd7;
            border-radius: 5px;
        }
        .successMsg {
            display: block;
            overflow: hidden;
            font-size: 18px;
            text-align: center;
            padding: 8px;
            margin-bottom: 20px !important;
            color: #9ec41a;
            background-color: #e4e8d0;
            border-radius: 5px;
        }
        
        /* form styles */
        .gon-form { overflow: hidden; margin: 20px 0; }
        .gon-form fieldset { border: none; margin-bottom: 30px; }
        .gon-form fieldset fieldset { margin: 10px; }
        .gon-form legend { font-size: 24px; font-weight: normal; color: #9ec41a; margin: 0; padding: 0 0 10px 0; }
        .gon-form p { margin: 0; }
        .gon-form p.intro { margin-bottom: 20px; }
        .gon-form p.fieldbg {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #e8e8e8;                    
            border-radius: 5px;
        }
        .gon-form p.grpfield { }
        .gon-form p.grpfield label { font-weight: bold; }
        .gon-form p.grpfield span { font-weight: normal; }
        .gon-form p.catfield {
            margin-left: 30px;
        }
        .gon-form label {
            font-size: 15px;
            display: block;
            margin-bottom: 5px;
            line-height: 24px;
            letter-spacing: 1px;
            overflow: hidden;
        }
        .gon-form label.cblabel {
            display: inline;
            margin-bottom: 5px;
            line-height: 24px;
            letter-spacing: 1px;
            overflow: hidden;
        }
        .gon-form label.cblabel .desc {
            color: #adadad;
            font-size: 14px;
        }
        .gon-form label.singlelabel {
            margin-bottom: 0;
        }
        .gon-form .error {
            display: block;
            width: auto;
            margin-top: 5px;
            padding: 3px 10px;
            color: #f2430e;
            background-color: #f9dfd7;                    
            border-radius: 5px;
        }
        .gon-form input[type="text"],
        .gon-form input[type="email"],
        .gon-form input[type="password"],
        .gon-form textarea {
            width: 100%;
            font-size: 17px;
            color: #333;
            margin: 0;
            padding: 5px 0;
            background: transparent;
            border: none;
            border-top: 1px solid transparent;
            border-bottom: 1px solid transparent;
            -webkit-appearance: none;
        }
        .gon-form input:focus,
        .gon-form textarea:focus {
            outline: none;
            border-top: 1px dotted #ccc;
            border-bottom: 1px dotted #ccc;
            /* todo: define my own input, textarea outline */
        } 
        .gon-form button:focus { outline: none; /* todo: define my own button outline */ } 
        /* Remove box shadow firefox, chrome and opera put around required fields */  
        .gon-form input:required,
        .gon-form textarea:required {  
            -webkit-box-shadow: none;  
               -moz-box-shadow: none;  
                 -o-box-shadow: none;  
                    box-shadow: none;  
        } 
        /* chrome, safari */  
        .gon-form ::-webkit-input-placeholder { color: #ccc; font-style: italic; }
        /* mozilla */  
        .gon-form input:-moz-placeholder,
        .gon-form textarea:-moz-placeholder { color: #ccc; font-style: italic; }
        /* ie (faux placeholder) */  
        .gon-form input.placeholder-text, 
        .gon-form textarea.placeholder-text  { color: #ccc; font-style: italic; }

        /* button styles */
        .button,
        .button:link,
        .button:visited {
            margin: 0 auto;
            display: inline-block;
            font-size: 17px;
            width: auto;
            height: auto;
            zoom: 1;
            padding: .5em 1.5em;
            cursor: pointer;
            text-decoration: none;
            border: none;
            /* todo: define outline */
            border-radius: 5px;
        }
        
        .button.green,
        .button.green:link,
        .button.green:visited {
            background-color: #9ec41a;
            color: white;
        }
        .button.green:hover {
            background-color: #89ab17;
        }
    </style>
</head>
<body>
[[*content]]
</body>
</html>