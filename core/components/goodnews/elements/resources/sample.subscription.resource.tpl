[[!GoodNewsSubscription?
    &submittedResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Mail Sent`]]`
    &activationResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Confirm`]]`
    &activationEmailTpl=`sample.GoodNewsActivationEmailChunk`
    &activationEmailSubject=`Thank you for joining our newsletter service at [[++site_name]]`
    &sendSubscriptionEmail=`1`
    &unsubscribeResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Unsubscribe`]]`
    &profileResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Update`]]`
    &subscriptionEmailSubject=`Your subscription to our newsletter service at [[++site_name]] was successful!`
    &reSubscriptionEmailSubject=`Existing user profile or newsletter subscription found!`
    &reSubscriptionEmailTpl=`sample.GoodNewsReSubscriptionEmailChunk`
    &validate=`
        email:email:required,
        gongroups:required,
        gdprcheck:required`
    &groupsOnly=`1`
    &gongroups.vTextRequired=`Please choose at least one mailing group.`
    &goncategories.vTextRequired=`Please choose at least one category of your interest.`
    &gdprcheck.vTextRequired=`You need to agree to our Terms and Conditions and Privacy Policy.`
]]

[[-
    Samples of other available configuration parameters:
    (Please read the documentation for a full list of parameters)
    
    &defaultGroups=`1`
    &includeGroups=`4,6`
    &defaultCategories=`3,36,40,48`

    PLEASE NOTE: If you use the &defaultCategories param, you don't need the &defaultGroups param!
                 (all groups will be selected automatically based on their corresponding categories)

    If you'd like to use groups AND categories, you need to use this &validate params:
    
    &validate=`
        email:email:required,
        gonctegories:required,
        gdprcheck:required`

    and set:
    
    &groupsOnly=`0`
]]

<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <h2>Subscribe to our newsletter</h2>
        <p>
            Sign up for our occasional newsletter and get news and updates delivered to your inbox. And don't worry, you can unsubscribe instantly or change your preferences at any time.
        </p>
        <form action="[[~[[*id]]]]" method="post">
            [[!+error.message:notempty=`
                <div class="formerror">
                    [[!+error.message]]
                </div>
            `]]
            <fieldset>
                <legend>Personal Data</legend>
                <label[[!+error.email:notempty=` class="fielderror"`]]>
                    E-Mail Address
                    [[!+error.email]]
                    <input type="email" name="email" value="[[!+email]]" required="required">
                </label>
                <label[[!+error.fullname:notempty=` class="fielderror"`]]>
                    First and Last Name (optional)
                    [[!+error.fullname]]
                    <input type="text" name="fullname" value="[[!+fullname]]" aria-describedby="fullnameHelp">
                    <small id="fullnameHelp">If you provide your full name, it will be used for personalizing your newsletters.</small>
                </label>
            </fieldset>
            [[!+fields_hidden:is=`1`:then=`
                [[!+grpcatfieldsets]]
            `:else=`
                <fieldset>
                    <legend>Newsletter Topics</legend>
                    <div class="label[[!+error.gongroups:notempty=` gongrpfieldserror`]][[!+error.goncategories:notempty=` goncatfieldserror`]]">
                        <p>Please choose the newsletter topics you are interested in.</p>
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
                <button type="submit" name="goodnews-subscription-btn" value="Subscribe">Subscribe now</button>
            </fieldset>
        </form>
    </main>
    <aside>
        <p><em>Please note: We respect your privacy and will never give your data to third parties, nor would we ever spam you.</em></p>
    </aside>
    <footer>
        <p>&copy; Copyright [[++site_name]] | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a></p>
    </footer>
</div>
        