[[!GoodNewsUpdateProfile?
    &validate=`
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
    
    PLEASE NOTE: in subscription-update resource you shouldn't set:

    &validate=`
        gongroups:required,
        goncategories:required`
        
    - otherwise the subscriber won't be able to unselect ALL groups/categories!
]]

<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <h2>Edit your user and mailing profile</h2>
        <p>
            You can edit your user and mailing profile here.
        </p>
        <form action="[[~[[*id]]]]" method="post">
            [[!+update_success:is=`1`:then=`
                <div class="formsuccess">
                    Your user and mailing profile was updated successfully!
                </div>
            `]]
            [[!+error.message:notempty=`
                <div class="formerror">
                    [[!+error.message]]
                </div>
            `]]
            <fieldset>
                <legend>Account Data</legend>
                <label>
                    E-Mail Address
                    <input type="email" name="email" value="[[!+email]]" class="readonly" aria-describedby="emailHelp" readonly="readonly">
                     <small id="emailHelp">The email address you registered with can't be changed.</small>
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
                    <legend>Newsletter Topics</legend>
                    <div class="label[[!+error.gongroups:notempty=` gongrpfieldserror`]][[!+error.goncategories:notempty=` goncatfieldserror`]]">
                        <p>Please choose the newsletter topics you are interested in</p>
                        [[!+error.gongroups]]
                        [[!+error.goncategories]]
                    </div>
                    <input type="hidden" name="gongroups[]" value="">
                    <input type="hidden" name="goncategories[]" value="">
                    [[!+grpcatfieldsets]]
                    <small>(Unselect all topics if you don't want to receive newsletters any longer)</small>
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
                <button type="submit" name="goodnews-updateprofile-btn" value="Update">Update profile</button>
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
