<!DOCTYPE html>
<html lang="[[++cultureKey]]">
    <head>
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="text/html; charset=[[++modx_charset]]">
        <title>Registration & Subscription Renewal</title>
        <style>
        /**
         * Email template based on:
         * https://github.com/leemunroe/responsive-html-email-template
         *
         * License: The MIT License (MIT)
         * https://github.com/leemunroe/responsive-html-email-template/blob/master/license.txt
         */
        @media only screen and (max-width: 620px) {
            table[class=body] h1 {
                font-size: 28px !important;
                margin-bottom: 10px !important;
            }
            table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
                font-size: 16px !important;
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
                text-decoration: none !important;
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

    <body class="" style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
        <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
            <tr>
                <td style="font-family: sans-serif; font-size: 16px; vertical-align: top;">&nbsp;</td>
                <td class="container" style="font-family: sans-serif; font-size: 16px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
                    <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">
        
                        <!-- START CENTERED WHITE CONTAINER -->
                        <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">
                            We found an existing user profile for the submitted email address
                        </span>
                        
                        <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">
                            <!-- START MAIN CONTENT AREA -->
                            <tr>
                                <td class="wrapper" style="font-family: sans-serif; font-size: 16px; vertical-align: top; box-sizing: border-box; padding: 20px;">
                                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                                        <tr>
                                            <td style="font-family: sans-serif; font-size: 16px; vertical-align: top;">
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px;">
                                                    Hello, [[+fullname]]
                                                </p>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px;">
                                                    we found an existing user profile for the submitted email address! The registered username for this profile is:
                                                </p>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: bold; margin: 0; margin-bottom: 15px;">
                                                    [[+username]]
                                                </p>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px;">
                                                    If you have forgotten your password, you can create  new one by visiting our login area at:
                                                </p>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; color: #9ec41a; margin-bottom: 15px;">
                                                    <span class="apple-link" style="color: #9ec41a; font-size: 16px; text-align: center;">
                                                        <a href="[[++site_url]]" target="_blank" style="text-decoration: underline; color: #9ec41a; font-size: 13px; text-align: center;">[[++site_url]]</a>
                                                    </span>
                                                </p>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px;">
                                                    For security reasons we didn't change any newletter subscription settings! To renew/edit your newsletter subscriptions please click the following link:
                                                </p>
                                                <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                                                    <tr>
                                                        <td style="font-family: sans-serif; font-size: 16px; vertical-align: top;">
                                                            <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td align="left" style="font-family: sans-serif; font-size: 16px; vertical-align: top; padding-bottom: 15px;">
                                                                            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td style="font-family: sans-serif; font-size: 16px; vertical-align: top; background-color: #9ec41a; border-radius: 5px; text-align: center;">
                                                                                            <a href="[[+updateProfileUrl]]" target="_blank" style="display: inline-block; color: #ffffff; background-color: #9ec41a; border: solid 1px #9ec41a; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 16px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #9ec41a;">
                                                                                                Edit your profile
                                                                                            </a>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                        <td style="font-family: sans-serif; font-size: 16px; vertical-align: top;">&nbsp;</td>
                                                        <td style="font-family: sans-serif; font-size: 16px; vertical-align: top;">
                                                            <table border="0" cellpadding="0" cellspacing="0" class="btn btn-secondary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td align="left" style="font-family: sans-serif; font-size: 16px; vertical-align: top; padding-bottom: 15px;">
                                                                            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td style="font-family: sans-serif; font-size: 16px; vertical-align: top; background-color: #ffffff; border-radius: 5px; text-align: center;">
                                                                                            <a href="[[+unsubscribeUrl]]" target="_blank" style="display: inline-block; color: #9ec41a; background-color: #ffffff; border: solid 1px #9ec41a; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 16px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #9ec41a;">
                                                                                                Unsubscribe
                                                                                            </a>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px;">
                                                    If you did not request this message, please ignore/delete it!
                                                </p>
                                                <p style="font-family: sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 15px;">
                                                    Thank you,<br>
                                                    Your [[++site_name]] Team
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- END MAIN CONTENT AREA -->
                        </table>
        
                        <!-- START FOOTER -->
                        <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
                            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                                <tr>
                                    <td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 13px; color: #999999; text-align: center;">
                                        <span class="apple-link" style="color: #999999; font-size: 13px; text-align: center;">
                                            Your are receiving this mail because your e-mail address<br>
                                            was submitted via our subscription form at<br>
                                            <a href="[[++site_url]]" style="text-decoration: underline; color: #999999; font-size: 13px; text-align: center;">[[++site_url]]</a>.
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 13px; color: #999999; text-align: center;">
                                        <span class="apple-link" style="color: #999999; font-size: 13px; text-align: center;">
                                            Please feel free to contact us: <a href="mailto:[[++emailsender]]" style="text-decoration: underline; color: #999999; font-size: 13px; text-align: center;">[[++emailsender]]</a>.
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!-- END FOOTER -->
        
                    <!-- END CENTERED WHITE CONTAINER -->
                    </div>
                </td>
                <td style="font-family: sans-serif; font-size: 16px; vertical-align: top;">&nbsp;</td>
            </tr>
        </table>
    </body>
</html>
