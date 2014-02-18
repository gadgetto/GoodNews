<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html lang="[[++cultureKey]]">
<head>
    
    <!--
    This template is inspired by the superb "Antwort" email boilerplate
    (http://internations.github.io/antwort/index.html)
    -->
    
    <meta http-equiv="Content-Type" content="text/html; charset=[[++modx_charset]]" />
    <meta name="viewport" content="initial-scale=1.0" />    <!-- Mobile webkit will display zoomed in -->
    <meta name="format-detection" content="telephone=no" /> <!-- Disable auto phone number linking in iOS -->
    
    <title>[[++site_name]] | [[*pagetitle]]</title>
    <base href="[[++site_url]]" />
    <style type="text/css">
    
        /* Force Hotmail to display emails at full width */
        .ReadMsgBody { width: 100%; background-color: #515151; }
        .ExternalClass { width: 100%; background-color: #515151; }

        /* Forces Hotmail to display normal line spacing. */
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }

        /* Prevents Webkit and Windows Mobile platforms from changing default font sizes. */
        body { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; }
        
        /* Resets all body margins and padding to "0" for good measure. */
        body { margin: 0; padding: 0; }
        
        /* Resolves webkit padding issue. */
        table { border-spacing: 0; }
        
        /* Resolves the Outlook 2007, 2010, and Gmail td padding issue. */
        table td { border-collapse: collapse; }
        
        /* http://www.symphonious.net/2010/09/02/the-email-and-p-myth/ */
        p { margin-top: 0; margin-bottom: 1em; }
        
        /* Image display hack for HotMail */
        img { display:block; }
        
        /* Weird Yahoo links and border bottom */
        .yshortcuts a { border-bottom: none !important; }
        
        /* E-mail width for small screens */
        @media screen and (max-width: 600px) {
            body {
                padding: 0 !important;
            }
            table[class="header"] {
                width: 100% !important;
            }
            table[class="container"] {
                width: 100% !important;
            }
            table[class="footer"] {
                width: 100% !important;
            }
        }
        
        /* Give content more room on mobile */
        @media screen and (max-width: 480px) {
            td[class="header-padding"],
            td[class="container-padding"],
            td[class="footer-padding"] {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }
    
    </style>
</head>
<body style="margin: 0; padding: 20px 0;" bgcolor="#515151" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
    
    <!-- 100% wrapper (grey background) -->
    <table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#515151">
        <tr>
            <td align="center" valign="top" bgcolor="#515151" style="background-color: #515151;">
    
                <!-- 600px header (grey background) -->
                <table border="0" width="600" cellpadding="0" cellspacing="0" class="header" bgcolor="#ebebeb">
                    <tr>
                        <td class="header-padding" bgcolor="#9ec41a" align="center" style="background-color: #9ec41a; text-align: center; padding-left: 30px; padding-right: 30px; font-weight: bold; font-size: 24px; line-height: 28px; font-family: Helvetica, sans-serif; color: #ffffff;">
                            <br>
                            [[++site_name]]<br>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td class="header-padding" bgcolor="#ebebeb" align="center" style="background-color: #ebebeb; text-align: center; padding-left: 30px; padding-right: 30px; font-size: 14px; line-height: 20px; font-family: Helvetica, sans-serif; color: #333;">
                            <br>
                            [[*introtext:notempty=`<p>[[*introtext]]</p>`]]
                        	<p>Trouble viewing this email? <a href="[[~[[*id]]]]" target="_blank">Read it online</a></p>
                        </td>
                    </tr>
                </table>
                <!-- /600px header -->
                
                <!-- 600px container (white background) -->
                <table border="0" width="600" cellpadding="0" cellspacing="0" class="container" bgcolor="#ffffff">
                    <tr>
                        <td class="container-padding" bgcolor="#ffffff" style="background-color: #ffffff; padding-left: 30px; padding-right: 30px; font-size: 20px; line-height: 24px; font-family: Helvetica, sans-serif; color: #333;">
                            <br>
                            Hello, [[+FULLNAME]]<br>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td class="container-padding" bgcolor="#ffffff" style="background-color: #ffffff; padding-left: 30px; padding-right: 30px; font-size: 16px; line-height: 22px; font-family: Helvetica, sans-serif; color: #333;">
                            [[*content]]
                        </td>
                    </tr>
                </table>
                <!--/600px container -->
    
                <!-- 600px footer (grey background) -->
                <table border="0" width="600" cellpadding="0" cellspacing="0" class="footer" bgcolor="#ebebeb">
                    <tr>
                        <td class="footer-padding" bgcolor="#ebebeb" align="center" style="background-color: #ebebeb; text-align: center; padding-left: 30px; padding-right: 30px; font-size: 14px; line-height: 20px; font-family: Helvetica, sans-serif; color: #333;">
                            <br>
                            Your are receiving this e-mail because you signed up for this newsletter at: [[++site_url]]<br>
                            <br>
                            You registered with e-mail address: [[+EMAIL]]<br>
                            <br>
                            <a href="[[~[[+unsubscribeResource]]]]?sid=[[+SID]]">Unsubscribe</a> | <a href="[[~[[+profileResource]]]]?sid=[[+SID]]">Edit Mailing Profile</a><br>
                            <br>
                            Please feel free to contact us: [[++emailsender]]<br>
                            <em>Copyright &copy; [[++site_name]], All rights reserved.</em><br>
                            <br>
                        </td>
                    </tr>
                    <tr><td height="10" bgcolor="#9ec41a" style="background-color: #9ec41a;">&nbsp;</td></tr>
                </table>
                <!-- /600px footer -->

            </td>
        </tr>
    </table>
    <!--/100% wrapper-->

</body>
</html>
