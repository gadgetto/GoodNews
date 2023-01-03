<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitego\GoodNews\BounceMailHandler\Rules;

/**
 * GoodNews BounceMailHandler body rules set
 * (none standard Delivery Status Notifications)
 *
 * @package goodnews
 * @subpackage bouncemailhandler
 */

/**
 * Parse message body for Delivery Status Notifications
 *
 * @param string $body The body of the email
 * @return array $result
 */
function bodyRules($body)
{
    /** @var array $result The result array */
    $result = [
        'rule_type'   => 'BODY'
        ,'email'       => ''
        ,'user_id'     => '0'
        ,'mailing_id'  => '0'
        ,'status_code' => ''
        ,'diag_code'   => ''
        ,'rule_no'     => '0000'
        ,'time'        => ''
        ,'bounce_type' => false
    ];

    /**
     * Try to parse the message body
     * based on custom rules.
     */

    /*
    sample:
    xxxxx@yourdomain.com
    no such address here
    */
    if (preg_match("/(\S+@\S+\w).*\n?.*no such address here/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0237';
        $result['email']       = $match[1];
    }

    /*
    <xxxxx@yourdomain.com>:
    111.111.111.111 does not like recipient.
    Remote host said: 550 User unknown
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*\n?.*user unknown/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0236';
        $result['email']       = $match[1];
    }

    /*********************************
    <xxxxx@yourdomain.com>:
    111.111.111.111 does not like recipient.
    Remote host said: 550 User not found
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*\n?.*user not found/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9001';
        $result['email']       = $match[1];
    }

    /*
    sample:
    <xxxxx@yourdomain.com>:
    Sorry, no mailbox here by that name. vpopmail (#5.1.1)
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*no mailbox/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0157';
        $result['email']       = $match[1];
    }

    /*
    sample:
    xxxxx@yourdomain.com<br>
    local: Sorry, can't find user's mailbox. (#5.1.1)<br>
    */
    elseif (preg_match("/(\S+@\S+\w)<br>.*\n?.*\n?.*can't find.*mailbox/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0164';
        $result['email']       = $match[1];
    }

    /*
    sample:
    ##########################################################
    #  This is an automated response from a mail delivery    #
    #  program.  Your message could not be delivered to      #
    #  the following address:                                #
    #                                                        #
    #      "|/usr/local/bin/mailfilt -u #dkms"               #
    #        (reason: Can't create output)                   #
    #        (expanded from: <xxxxx@yourdomain.com>)         #
    #                                                        #
    */
    elseif (preg_match("/Can't create output.*\n?.*<(\S+@\S+\w)>/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0169';
        $result['email']       = $match[1];
    }

    /*
    sample:
    ????????????????:
    xxxxx@yourdomain.com : ????, ?????.
    */
    elseif (preg_match("/(\S+@\S+\w).*=D5=CA=BA=C5=B2=BB=B4=E6=D4=DA/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0174';
        $result['email']       = $match[1];
    }

    /*
    sample:
    xxxxx@yourdomain.com
    Unrouteable address
    */
    elseif (preg_match("/(\S+@\S+\w).*\n?.*Unrouteable address/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0179';
        $result['email']       = $match[1];
    }

    /*
    sample:
    Delivery to the following recipients failed.
    xxxxx@yourdomain.com
    */
    elseif (preg_match("/delivery[^\n\r]+failed\S*\s+(\S+@\S+\w)\s/is", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0013';
        $result['email']       = $match[1];
    }

    /*
    sample:
    A message that you sent could not be delivered to one or more of its^M
    recipients. This is a permanent error. The following address(es) failed:^M
    ^M
    xxxxx@yourdomain.com^M
    unknown local-part "xxxxx" in domain "yourdomain.com"^M
    */
    elseif (preg_match("/(\S+@\S+\w).*\n?.*unknown local-part/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0232';
        $result['email']       = $match[1];
    }

    /*
    sample:
    <xxxxx@yourdomain.com>:^M
    111.111.111.11 does not like recipient.^M
    Remote host said: 550 Invalid recipient: <xxxxx@yourdomain.com>^M
    */
    elseif (preg_match("/Invalid.*(?:alias|account|recipient|address|email|mailbox|user).*<(\S+@\S+\w)>/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0233';
        $result['email']       = $match[1];
    }

    /*
    sample:
    Sent >>> RCPT TO: <xxxxx@yourdomain.com>^M
    Received <<< 550 xxxxx@yourdomain.com... No such user^M
    ^M
    Could not deliver mail to this user.^M
    xxxxx@yourdomain.com^M
    *****************     End of message     ***************^M
    */
    elseif (preg_match("/\s(\S+@\S+\w).*No such.*(?:alias|account|recipient|address|email|mailbox|user)>/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0234';
        $result['email']       = $match[1];
    }

    /*
    sample:
    <xxxxx@yourdomain.com>:^M
    This address no longer accepts mail.
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*(?:alias|account|recipient|address|email|mailbox|user).*no.*accept.*mail>/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0235';
        $result['email']       = $match[1];
    }

    /*
    sample 1:
    <xxxxx@yourdomain.com>:
    This account is over quota and unable to receive mail.
    sample 2:
    <xxxxx@yourdomain.com>:
    Warning: undefined mail delivery mode: normal (ignored).
    The users mailfolder is over the allowed quota (size). (#5.2.2)
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*\n?.*over.*quota/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0182';
        $result['email']       = $match[1];
    }

    /*
    sample:
    ----- Transcript of session follows -----
    mail.local: /var/mail/2b/10/kellen.lee: Disc quota exceeded
    554 <xxxxx@yourdomain.com>... Service unavailable
    */
    elseif (preg_match("/quota exceeded.*\n?.*<(\S+@\S+\w)>/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0126';
        $result['email']       = $match[1];
    }

    /*
    sample:
    Hi. This is the qmail-send program at 263.domain.com.
    <xxxxx@yourdomain.com>:
    - User disk quota exceeded. (#4.3.0)
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*quota exceeded/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0158';
        $result['email']       = $match[1];
    }

    /*
    sample:
    xxxxx@yourdomain.com
    mailbox is full (MTA-imposed quota exceeded while writing to file
    /mbx201/mbx011/A100/09/35/A1000935772/mail/.inbox):
    */
    elseif (preg_match("/\s(\S+@\S+\w)\s.*\n?.*mailbox.*full/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0166';
        $result['email']       = $match[1];
    }

    /*
    sample:
    The message to xxxxx@yourdomain.com is bounced because : Quota exceed the hard limit
    */
    elseif (preg_match("/The message to (\S+@\S+\w)\s.*bounce.*Quota exceed/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0168';
        $result['email']       = $match[1];
    }

    /*
    sample:
    xxxxx@yourdomain.com<br>
    553 user is inactive (eyou mta)
    */
    elseif (preg_match("/(\S+@\S+\w)<br>.*\n?.*\n?.*user is inactive/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0171';
        $result['email']       = $match[1];
    }

    /*
    sample:
    xxxxx@yourdomain.com [Inactive account]
    */
    elseif (preg_match("/(\S+@\S+\w).*inactive account/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0181';
        $result['email']       = $match[1];
    }

    /*
    sample:
    <xxxxx@yourdomain.com>:
    Unable to switch to /var/vpopmail/domains/domain.com: input/output error. (#4.3.0)
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*input\/output error/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0172';
        $result['email']       = $match[1];
    }

    /*
    sample:
    <xxxxx@yourdomain.com>:
    can not open new email file errno=13
    file=/home/vpopmail/domains/fromc.com/0/domain/Maildir/tmp/1155254417.28358.mx05,S=212350
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*can not open new email file/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0173';
        $result['email']       = $match[1];
    }

    /*
    sample:
    <xxxxx@yourdomain.com>:
    111.111.111.111 failed after I sent the message.
    Remote host said: 451 mta283.mail.scd.yahoo.com Resources temporarily unavailable.
    Please try again later [#4.16.5].
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*\n?.*Resources temporarily unavailable/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0163';
        $result['email']       = $match[1];
    }

    /*
     * sample:
     * AutoReply message from xxxxx@yourdomain.com
     */
    elseif (preg_match("/^AutoReply message from (\S+@\S+\w)/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0167';
        $result['email']       = $match[1];
    }

    /*
    sample:
    <xxxxx@yourdomain.com>:
    The user does not accept email in non-Western (non-Latin) character sets.
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*\n?.*does not accept[^\r\n]*non-Western/i", $body, $match)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0043';
        $result['email']       = $match[1];
    }

    /*
    sample:
    This user doesn't have a yahoo.com account
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*554.*delivery error.*this user.*doesn't have.*account/is", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0044';
        $result['email']       = $match[1];
    }

    /*
    550 hotmail.com
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*550.*Requested.*action.*not.*taken:.*mailbox.*unavailable/is", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0045';
        $result['email']       = $match[1];
    }

    /*
    550 5.1.1 aim.com
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*550 5\.1\.1.*Recipient address rejected/is", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0046';
        $result['email']       = $match[1];
    }

    /*
    550 5.1.0
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*550.*5\.1\.0.*Address rejected/is", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9002';
        $result['email']       = $match[1];
    }

    /*
    550 .* (in reply to end of DATA command)
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*550.*in reply to end of DATA command/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0047';
        $result['email']       = $match[1];
    }

    /*
    550 .* (in reply to RCPT TO command)
    */
    elseif (preg_match("/<(\S+@\S+\w)>.*550.*in reply to RCPT TO command/i", $body, $match)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0048';
        $result['email']       = $match[1];
    }

    /*
    You are using an old domainname for the intended recipient.
    name@old-domain.com

    Please change your contact detail to the new domain.
    @new-domain.com
    */
    elseif (preg_match('/old +domainname.*\n?.*(\S+@\S+\w)/i', $body, $match)) {   // not working!
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9010';
        $result['email']       = $match[1];
    }

    return $result;
}
