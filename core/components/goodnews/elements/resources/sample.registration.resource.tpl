[[!GoodNewsSubscription?
    &submittedResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Registration Mail Sent`]]`
    &activationResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Registration Confirm`]]`
    &activationEmailTpl=`sample.GoodNewsActivationRegEmailChunk`
    &activationEmailSubject=`Thank you for registering at [[++site_name]]`
    &sendSubscriptionEmail=`1`
    &unsubscribeResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Unsubscribe`]]`
    &profileResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Update`]]`
    &subscriptionEmailSubject=`Your subscription to our newsletter service at [[++site_name]] was successful!`
    &reSubscriptionEmailSubject=`Existing user profile or newsletter subscription found!`
    &reSubscriptionEmailTpl=`sample.GoodNewsReRegistrationEmailChunk`
    &usergroups=`10`
    &usernameField=`email`
    &validate=`
        email:email:required,
        password:required:minLength=^8^,
        password_confirm:password_confirm=^password^,
        fullname:required,
        address:required,
        city:required,
        zip:required,
        gdprcheck:required`
    &groupsOnly=`1`
    &gdprcheck.vTextRequired=`You need to agree to our Terms and Conditions and Privacy Policy.`
]]

[[-
    Samples of other available configuration parameters:
    (Please read the documentation for a full list of parameters)
    
    &defaultGroups=`1`
    &includeGroups=`4,6`
    &defaultCategories=`3,36,40,48`
    &gongroups.vTextRequired=`Please choose at least one mailing group.`
    &goncategories.vTextRequired=`Please choose at least one category of your interest.`

    PLEASE NOTE: If you use the &defaultCategories param, you don't need the &defaultGroups param!
                 (all groups will be selected automatically based on their corresponding categories)

    If you'd like to use groups AND categories, you need to set:
        
    &groupsOnly=`0`
]]

<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <h2>Register for our website</h2>
        <form action="[[~[[*id]]]]" method="post">
            [[!+error.message:notempty=`
                <div class="formerror">
                    [[!+error.message]]
                </div>
            `]]
            <fieldset>
                <legend>Account Data</legend>
                <label[[!+error.email:notempty=` class="fielderror"`]]>
                    E-Mail Address
                    [[!+error.email]]
                    <input type="email" name="email" value="[[!+email]]" required="required">
                </label>
                <label[[!+error.password:notempty=` class="fielderror"`]]>
                    Password
                    [[!+error.password]]
                    <input type="password" name="password" value="[[!+password]]" aria-describedby="passwordHelp" required="required">
                    <small id="passwordHelp">The password needs to be at least 8 characters long.</small>
               </label>
                <label[[!+error.password_confirm:notempty=` class="fielderror"`]]>
                    Retype Password
                    [[!+error.password_confirm]]
                    <input type="password" name="password_confirm" value="[[!+password_confirm]]" placeholder="Please retype your password" required="required">
                </label>
            </fieldset>
            <fieldset>
                <legend>Personal Data</legend>
                <label[[!+error.fullname:notempty=` class="fielderror"`]]>
                    First and Last Name
                    [[!+error.fullname]]
                    <input type="text" name="fullname" value="[[!+fullname]]" placeholder="Please enter your first and last name" required="required">
                </label>
                <label[[!+error.address:notempty=` class="fielderror"`]]>
                    Address
                    [[!+error.address]]
                    <input type="text" name="address" value="[[!+address]]" required="required">
                </label>
                <label[[!+error.city:notempty=` class="fielderror"`]]>
                    City
                    [[!+error.city]]
                    <input type="text" name="city" value="[[!+city]]" required="required">
                </label>
                <label[[!+error.zip:notempty=` class="fielderror"`]]>
                    Zip
                    [[!+error.zip]]
                    <input type="text" name="zip" value="[[!+zip]]" required="required">
                </label>
            </fieldset>
            [[!+fields_hidden:is=`1`:then=`
                [[!+grpcatfieldsets]]
            `:else=`
                <fieldset>
                    <legend>Newsletter Service</legend>
                    <p>
                        Do you want to sign up for our occasional newsletter and get news and updates 
                        delivered to your inbox? And don't worry, you can unsubscribe instantly 
                        or change your preferences at any time.
                    </p>
                    <div class="label[[!+error.gongroups:notempty=` gongrpfieldserror`]][[!+error.goncategories:notempty=` goncatfieldserror`]]">
                        <p>Please choose the newsletter topics you are interested in (optional)</p>
                        [[!+error.gongroups]]
                        [[!+error.goncategories]]
                    </div>
                    <input type="hidden" name="gongroups[]" value="">
                    <input type="hidden" name="goncategories[]" value="">
                    [[!+grpcatfieldsets]]
                </fieldset>
            `]]
            [[!+config_error:is=`1`:then=`
                <div class="formerror">
                    Snippet configuration error: Please check your GoodNewsSubscription Snippet configuration!
                </div>
            `]]
            <fieldset>
                <label[[!+error.gdprcheck:notempty=` class="fielderror"`]]>
                    [[!+error.gdprcheck]]
                    <input type="checkbox" name="gdprcheck" value="agreed" required="required">
                    I have read and agree to the <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> and <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a>
                </label>
                <button type="submit" name="goodnews-subscription-btn" value="Subscribe">Register now</button>
            </fieldset>
        </form>
    </main>
    <aside>
        <p><em>Please note: We respect your privacy and will never give your data to third 
        parties, nor would we ever spam you.</em></p>
    </aside>
    <footer>
        <p>&copy; Copyright [[++site_name]] | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a></p>
    </footer>
</div>
        