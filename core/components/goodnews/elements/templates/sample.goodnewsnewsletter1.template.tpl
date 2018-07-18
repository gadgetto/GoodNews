<!DOCTYPE html>
<html lang="[[++cultureKey]]">
    <head>
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="text/html; charset=[[++modx_charset]]">
        <title>[[++site_name]] | [[*pagetitle]]</title>
        <style type="text/css">
        /**
         * Template: Single column
         * Requirements: Styles need to be inlined before sending
         *
         * Email template based on:
         * https://github.com/leemunroe/responsive-html-email-template
         *
         * License: The MIT License (MIT)
         * https://github.com/leemunroe/responsive-html-email-template/blob/master/license.txt
         */

        /* GLOBAL RESETS */
        
        img {
            border: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
        }
        body {
            background-color: #f6f6f6;
            font-family: sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }
        table td {
            font-family: sans-serif;
            font-size: 16px;
            vertical-align: top;
        }

        /* BODY & CONTAINER */
        
        .body {
            background-color: #f6f6f6;
            width: 100%;
        }
        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display: block;
            Margin: 0 auto !important;
            /* makes it centered */
            max-width: 580px;
            padding: 10px;
            width: 580px;
        }
        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            box-sizing: border-box;
            display: block;
            Margin: 0 auto;
            max-width: 580px;
            padding: 10px;
        }

        /* HEADER, FOOTER, MAIN */
        
        .main {
            background: #ffffff;
            border-radius: 3px;
            width: 100%;
        }
        .wrapper {
            box-sizing: border-box;
            padding: 20px;
        }
        .content-block-title {
            padding-bottom: 10px;            
            padding-top: 10px;
        }
        .content-block {
            padding-bottom: 10px;
            padding-top: 10px;
        }
        .footer {
            clear: both;
            margin-top: 10px;
            text-align: center;
            width: 100%;
        }
        .footer td,
        .footer p,
        .footer span,
        .footer a {
            color: #999999;
            font-size: 13px;
            text-align: center;
        }

        /* TYPOGRAPHY */
        
        h1,
        h2,
        h3,
        h4 {
            color: #000000;
            font-family: sans-serif;
            font-weight: 300;
            line-height: 1.5;
            margin: 0;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 28px;
            text-align: center;
            text-transform: capitalize;
        }
        h2 {
            font-size: 24px;
        }
        .content-block-title h2,
        .content-block-title h3,
        .content-block-title h4 {
            margin-bottom: 0px;
        }
        p,
        ul,
        ol {
            font-family: sans-serif;
            font-size: 16px;
            font-weight: normal;
            margin: 0;
            margin-bottom: 15px;
        }
        p li,
        ul li,
        ol li {
            list-style-position: inside;
            margin-left: 5px;
        }
        a {
            color: #9ec41a;
            text-decoration: underline;
        }
        a:hover {
            color: #8da534;
            text-decoration: underline;
        }
        .hello {
            font-size: 20px;
            font-weight: 300;
        }
        
        /* BUTTONS */
        
        .btn {
            box-sizing: border-box;
            width: 100%;
        }
        .btn > tbody > tr > td {
            padding-bottom: 15px;
        }
        .btn table {
            width: auto;
        }
        .btn table td {
            background-color: #ffffff;
            border-radius: 5px;
            text-align: center;
        }
        .btn a {
            background-color: #ffffff;
            border: solid 1px #9ec41a;
            border-radius: 5px;
            box-sizing: border-box;
            color: #9ec41a;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            padding: 12px 25px;
            text-decoration: none;
            text-transform: capitalize;
        }
        .btn-primary table td {
            background-color: #9ec41a;
        }
        .btn-primary a {
            background-color: #9ec41a;
            border-color: #9ec41a;
            color: #ffffff;
        }
        .btn-primary table td {
            background-color: #9ec41a;
        }
        .btn-primary a {
            background-color: #9ec41a;
            border-color: #9ec41a;
            color: #ffffff;
        }
        .btn-secondary table td {
            background-color: #ffffff;
        }
        .btn-secondary a {
            background-color: #ffffff;
            border-color: #9ec41a;
            color: #9ec41a;
        }

        /* OTHER STYLES THAT MIGHT BE USEFUL */
        
        .last {
            margin-bottom: 0;
        }
        .first {
            margin-top: 0;
        }
        .align-center {
            text-align: center;
        }
        .align-right {
            text-align: right;
        }
        .align-left {
            text-align: left;
        }
        .clear {
            clear: both;
        }
        .mt0 {
            margin-top: 0;
        }
        .mb0 {
            margin-bottom: 0;
        }
        .preheader {
            color: transparent;
            display: none;
            height: 0;
            max-height: 0;
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            mso-hide: all;
            visibility: hidden;
            width: 0;
        }
        .powered-by a {
            text-decoration: none;
        }
        hr {
            border: 0;
            border-bottom: 1px solid #dedede;
            margin: 20px 0;
        }

        /* RESPONSIVE AND MOBILE FRIENDLY STYLES */
        
        @media only screen and (max-width: 620px) {
            table[class=body] h1 {
                font-size: 24px !important;
                margin-bottom: 10px !important;
            }
            table[class=body] h2 {
                font-size: 20px !important;
                margin-bottom: 10px !important;
            }
            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 14px !important;
            }
            table[class=body] .wrapper,
            table[class=body] .article {
                padding: 10px !important;
            }
            table[class=body] .content {
                padding: 0 !important;
            }
            table[class=body] .container {
                padding: 0 !important;
                width: 100% !important;
            }
            table[class=body] .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }
            table[class=body] .footer td,
            table[class=body] .footer p,
            table[class=body] .footer span,
            table[class=body] .footer a {
                font-size: 12px;
            }
            table[class=body] .btn table {
                width: 100% !important;
            }
            table[class=body] .btn a {
                width: 100% !important;
            }
            table[class=body] .img-responsive {
                height: auto !important;
                max-width: 100% !important;
                width: auto !important;
            }
        }

        /* PRESERVE THESE STYLES IN THE HEAD */
        
        @media all {
            .ExternalClass {
                width: 100%;
            }
            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%;
            }
            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: underline !important;
            }
            .btn-primary table td:hover {
                background-color: #8da534 !important;
            }
            .btn-primary a:hover {
                background-color: #8da534 !important;
                border-color: #8da534 !important;
            }
            .btn-secondary table td:hover {
                background-color: #eff3db !important;
            }
            .btn-secondary a:hover {
                background-color: #eff3db !important;
                border-color: #8da534 !important;
            }
        }
        </style>
    </head>
    <body>
        <table border="0" cellpadding="0" cellspacing="0" class="body">
            <tr>
                <td>&nbsp;</td>
                <td class="container">
                    <div class="content">
                    <!-- START CENTERED WHITE CONTAINER -->
                        
                        <!-- Hidden preheader (some clients will show this text as a preview) -->
                        [[*introtext:notempty=`<span class="preheader">[[*introtext]]</span>`]]
                        
                        <table class="main">
                            <!-- START MAIN CONTENT AREA -->
                            <tr>
                                <td class="wrapper">
                                    <table border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <p><strong class="hello">Hello[[+fullname:notempty=` [[+fullname]]`]],</strong></p>
                                                [[*content]]
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- END MAIN CONTENT AREA -->
                        </table>
        
                        <!-- START FOOTER -->
                        <div class="footer">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block">
                                        <span class="apple-link">
                                            Your are receiving this e-mail because you signed up for our newsletter on <a href="[[++site_url]]">our website</a>.<br>
                                            Don't like these e-mails? <a href="[[~[[+unsubscribeResource]]]]?sid=[[+sid]]">Unsubscribe</a> or <a href="[[~[[+profileResource]]]]?sid=[[+sid]]">Edit Mailing Profile</a>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        <span class="apple-link">
                                            If you have any questions, please feel free to <a href="mailto:[[++emailsender]]">contact us</a>!<br>
                                            Powered by <a href="[[++site_url]]">[[++site_name]]</a>.
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!-- END FOOTER -->

                    <!-- END CENTERED WHITE CONTAINER -->
                    </div>
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </body>
</html>
