[[!GoodNewsRequestLinks?
    &unsubscribeResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Unsubscribe`]]`
    &profileResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Update`]]`
    &requestLinksEmailTpl=`sample.GoodNewsRequestLinksEmailTpl`
    &requestLinksEmailSubject=`Your requested links to update or cancel your subscription at [[++site_name]]`
    &validate=`
        email:email:required,
        gdprcheck:required`
]]

[[-
    Please read the documentation for a full list of configuration parameters.
]]

<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <h2>Update or cancel your subscription</h2>
        <p>
            Please enter the email address you used for subscription and click the <strong>Request Secure Links</strong> button. 
            We will send an email to the submitted address which contains quick-links to update or cancel your subscription.
        </p>
        [[!+success.message:notempty=`
            <div class="formsuccess">
                [[!+success.message]]
            </div>
        `]]
        [[!+error.message:notempty=`
            <div class="formerror">
                [[!+error.message]]
            </div>
        `]]
        <form action="[[~[[*id]]]]" method="post">
            <fieldset>
                <label[[!+error.email:notempty=` class="fielderror"`]]>
                    E-Mail Address
                    [[!+error.email]]
                    <input type="email" name="email" value="[[!+email]]" required="required">
                </label>
            </fieldset>
            <fieldset>
                <label[[!+error.gdprcheck:notempty=` class="fielderror"`]]>
                    [[!+error.gdprcheck]]
                    <input type="checkbox" name="gdprcheck" value="agreed" required="required">
                    I have read and agree to the <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> and <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a>
                </label>
                <button type="submit" name="goodnews-requestlinks-btn" value="Request">Request Secure Links</button>
            </fieldset>
        </form>
    </main>
    <footer>
        <p>&copy; Copyright [[++site_name]] | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a></p>
    </footer>
</div>
        