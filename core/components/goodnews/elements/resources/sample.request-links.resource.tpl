[[!GoodNewsRequestLinks?
    &unsubscribeResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`Unsubscribe`]]`
    &profileResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`Subscription Update`]]`
    &requestLinksEmailTpl=`sample.GoodNewsRequestLinksEmailTpl`
    &requestLinksEmailSubject=`Your requested links to update or cancel your subscription at [[++site_name]]`
    &validate=`
        email:email:required,
        nospam:blank`
]]
<!--
    Samples of other available configuration parameters:
    (Please read the documentation for a full list of parameters)
    
    &submittedResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`Request Links Success`]]`
-->

<div class="container">
    <div class="header">
        <h1>[[++site_name]]</h1>
    </div>
    <div class="main">
        <h2>Update or cancel your subscription</h2>
        <p>
            Please enter the email address you used for subscription and click the <strong>Request Secure Links</strong> button. 
            We will send an email to the submitted address which contains quick-links to update or cancel your subscription.
        </p>
        [[!+error.message:notempty=`
            <p class="errorMsg">[[!+error.message]]</p>
        `]]
        [[!+success.message:notempty=`
            <p class="successMsg">[[!+success.message]]</p>
        `]]
        <form id="profileform" class="gon-form" action="[[~[[*id]]]]" method="post">
            <input type="hidden" name="nospam" value="[[!+nospam]]">
            <fieldset>
                <p class="fieldbg[[!+error.email:notempty=` fielderror`]]">
                    <label for="email">
                        E-Mail Address
                        [[!+error.email]]
                    </label>
                    <input type="email" name="email" id="email" value="[[!+email]]" placeholder="Please enter your e-mail address" required>
                </p>
            </fieldset>
            <p>
                <button type="submit" role="button" name="goodnews-requestlinks-btn" value="Request" class="button green">Request Secure Links</button>
            </p>
        </form>
    </div>
    <div class="footer">
        <p>&copy; Copyright [[++site_name]]</p>
    </div>
</div>
        