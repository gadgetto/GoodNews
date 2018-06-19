<!DOCTYPE html>
<html lang="[[++cultureKey]]">
<head>
    <meta charset="[[++modx_charset]]">
    <title>[[++site_name]] | [[*pagetitle]]</title>
    <base href="[[++site_url]]">
    <style type="text/css">
        html {
            box-sizing: border-box;
        }
        *,
        *::before,
        *::after {
            box-sizing: inherit;
        }
        body {
            font-family: sans-serif;
        }
        .container {
            max-width: 40rem;
            margin: 0 auto;
        }
        h1,
        h2 {
            font-weight: 400;
        }
        p {
            margin: 0 0 1.25rem 0;
        }
        form {
            padding: 0;
            margin-right: 0;
            margin-left: 0;
        }
        fieldset {
            min-width: 0;
            padding: 0;
            margin: 0 0 1rem 0;
            border: 0;
        }
        legend {
            display: block;
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin-bottom: .5rem;
            font-size: 1.5rem;
            line-height: inherit;
            color: inherit;
            white-space: normal;
        }
        label {
            display: block;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: .75rem;
        }
        label strong {
            font-weight: 600;
        }
        .gongrpfieldset label,
        .goncatfieldset label {
            margin-bottom: 0;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            display: block;
            width: 100%;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #b1b9c1;
            border-radius: .25rem;
        }
        input.readonly,
        textarea.readonly {
            background-color: #f7f8fb;
            cursor: not-allowed;
        }
        .formerror {
            display: block;
            margin-bottom: 1rem;
            padding: .75rem 1rem;
            color: #d1293a;
            background-color: #fbd3d7;
            border-radius: .25rem;
        }
        .formsuccess {
            display: block;
            margin-bottom: 1rem;
            padding: .75rem 1rem;
            color: #27a946;
            background-color: #cfe7d5;
            border-radius: .25rem;
        }
        .fielderror,
        .gongrpfieldserror,
        .goncatfieldserror {
            color: #d1293a;
        }
        .fielderror input {
            border-color: #d1293a;
        }
        span.error {
            display: block;
            margin-bottom: .5rem;
            padding: .375rem .75rem;
            color: #d1293a;
            background-color: #fbd3d7;
            border-radius: .25rem;
            font-size: smaller;
            line-height: 1rem;
        }
        button,
        input[type="button"] {
            color: #fff;
            background-color: #9ec41a;
            display: inline-block;
            font-weight: 400;
            vertical-align: middle;
            border: 1px solid #9ec41a;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
        }
        button:hover,
        input[type="button"]:hover {
            background-color: #8da534;
            border-color: #8da534;
        }
        .hidden {
            visibility: hidden;
        }
    </style>
</head>
<body>
    [[*content]]
</body>
</html>