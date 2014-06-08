<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 * (Loosely) based on code from PHPMailer-BMH (Bounce Mail Handler)
 * Copyright 2002-2009 by Andy Prevost <andy.prevost@worxteam.com>
 * Modified by bitego - 04/2014
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * GoodNews GoodNewsBounceMailHandler standard DSN rules set
 * (standard Delivery Status Notifications)
 *
 * @package goodnews
 */

/**
 * Parse for standard Delivery Status Notification by using php IMAP methods
 *
 * @param string $dsnMsg Human-readable explanation
 * @param string $dsnReport The delivery-status report
 * @return array $result
 */
function bmhDSNRules($dsnMsg, $dsnReport) {

    /** @var array $result The result array */
    $result = array(
         'rule_type'   => 'DSN'
        ,'email'       => ''
        ,'user_id'     => '0'
        ,'mailing_id'  => '0'
        ,'status_code' => ''
        ,'diag_code'   => ''
        ,'rule_no'     => '0000'
        ,'bounce_type' => false
    );

    /** @var array $statusCodes The status codes array */
    $statusCodes = array(
         '5.0.0' => array('bounce_type' => 'hard')  //Address does not exist
        ,'5.1.0' => array('bounce_type' => 'hard')  //Other address status
        ,'5.1.1' =>	array('bounce_type' => 'hard')  //Bad destination mailbox address
        ,'5.1.2' =>	array('bounce_type' => 'hard')  //Bad destination system address
        ,'5.1.3' =>	array('bounce_type' => 'hard')  //Bad destination mailbox address syntax
        ,'5.1.4' =>	array('bounce_type' => 'hard')  //Destination mailbox address ambiguous
        ,'5.1.5' =>	array('bounce_type' => 'hard')  //Destination mailbox address valid
        ,'5.1.6' =>	array('bounce_type' => 'hard')  //Mailbox has moved
        ,'5.1.7' =>	array('bounce_type' => 'hard')  //Bad sender’s mailbox address syntax
        ,'5.1.8' =>	array('bounce_type' => 'hard')  //Bad sender’s system address
        ,'5.2.0' =>	array('bounce_type' => 'soft')  //Other or undefined mailbox status
        ,'5.2.1' =>	array('bounce_type' => 'soft')  //Mailbox disabled, not accepting messages
        ,'5.2.2' =>	array('bounce_type' => 'soft')  //Mailbox full
        ,'5.2.3' =>	array('bounce_type' => 'hard')  //Message length exceeds administrative limit
        ,'5.2.4' =>	array('bounce_type' => 'hard')  //Mailing list expansion problem
        ,'5.3.0' =>	array('bounce_type' => 'hard')  //Other or undefined mail system status
        ,'5.3.1' =>	array('bounce_type' => 'soft')  //Mail system full
        ,'5.3.2' =>	array('bounce_type' => 'hard')  //System not accepting network messages
        ,'5.3.3' =>	array('bounce_type' => 'hard')  //System not capable of selected features
        ,'5.3.4' =>	array('bounce_type' => 'hard')  //Message too big for system
        ,'5.4.0' =>	array('bounce_type' => 'hard')  //Other or undefined network or routing status
        ,'5.4.1' =>	array('bounce_type' => 'hard')  //No answer from host
        ,'5.4.2' =>	array('bounce_type' => 'hard')  //Bad connection
        ,'5.4.3' =>	array('bounce_type' => 'hard')  //Routing server failure
        ,'5.4.4' =>	array('bounce_type' => 'hard')  //Unable to route
        ,'5.4.5' =>	array('bounce_type' => 'soft')  //Network congestion
        ,'5.4.6' =>	array('bounce_type' => 'hard')  //Routing loop detected
        ,'5.4.7' =>	array('bounce_type' => 'hard')  //Delivery time expired
        ,'5.5.0' =>	array('bounce_type' => 'hard')  //Other or undefined protocol status
        ,'5.5.1' =>	array('bounce_type' => 'hard')  //Invalid command
        ,'5.5.2' =>	array('bounce_type' => 'hard')  //Syntax error
        ,'5.5.3' =>	array('bounce_type' => 'soft')  //Too many recipients
        ,'5.5.4' =>	array('bounce_type' => 'hard')  //Invalid command arguments
        ,'5.5.5' =>	array('bounce_type' => 'hard')  //Wrong protocol version
        ,'5.6.0' =>	array('bounce_type' => 'hard')  //Other or undefined media error
        ,'5.6.1' =>	array('bounce_type' => 'hard')  //Media not supported
        ,'5.6.2' =>	array('bounce_type' => 'hard')  //Conversion required and prohibited
        ,'5.6.3' =>	array('bounce_type' => 'hard')  //Conversion required but not supported
        ,'5.6.4' =>	array('bounce_type' => 'hard')  //Conversion with loss performed
        ,'5.6.5' =>	array('bounce_type' => 'hard')  //Conversion failed
        ,'5.7.0' =>	array('bounce_type' => 'hard')  //Other or undefined security status
        ,'5.7.1' =>	array('bounce_type' => 'hard')  //Delivery not authorized, message refused
        ,'5.7.2' =>	array('bounce_type' => 'hard')  //Mailing list expansion prohibited
        ,'5.7.3' =>	array('bounce_type' => 'hard')  //Security conversion required but not possible
        ,'5.7.4' =>	array('bounce_type' => 'hard')  //Security features not supported
        ,'5.7.5' =>	array('bounce_type' => 'hard')  //Cryptographic failure
        ,'5.7.6' =>	array('bounce_type' => 'hard')  //Cryptographic algorithm not supported
        ,'5.7.7' =>	array('bounce_type' => 'hard')  //Message integrity failure
        ,'9.1.1' =>	array('bounce_type' => 'hard')  //Hard bounce with no bounce code found
    );

    $diagCode = '';

    // Extract the recipient email
    if (preg_match('/Original-Recipient: rfc822;(.*)/i', $dsnReport, $match)) {
        $email_arr = imap_rfc822_parse_adrlist($match[1], 'default.domain.name');
        if (isset($email_arr[0]->host) && $email_arr[0]->host != '.SYNTAX-ERROR.' && $email_arr[0]->host != 'default.domain.name' ) {
            $result['email'] = $email_arr[0]->mailbox.'@'.$email_arr[0]->host;
        }
    } elseif (preg_match('/Final-Recipient: rfc822;(.*)/i', $dsnReport, $match)) {
        $email_arr = imap_rfc822_parse_adrlist($match[1], 'default.domain.name');
        if (isset($email_arr[0]->host) && $email_arr[0]->host != '.SYNTAX-ERROR.' && $email_arr[0]->host != 'default.domain.name' ) {
            $result['email'] = $email_arr[0]->mailbox.'@'.$email_arr[0]->host;
        }
    }
    
    // If email still counldn't be extracted try to get from dsn message
    if (empty($result['email'])) {
        if (preg_match('<(\S+@\S+\w)>/is', $dsnMsg, $match)) {
            $result['email'] = $match[1];
        }
    }

    // Extract status code (e.g. 5.1.1)
    if (preg_match ('/Status: ([0-9\.]+)/i', $dsnReport, $match)) {
        $result['status_code'] = $match[1];
    }
    
    // Extract diganostic code (could be multi-line, if the new line is beginning with SPACE or HTAB)
    if (preg_match ('/Diagnostic-Code:((?:[^\n]|\n[\t ])+)(?:\n[^\t ]|$)/is', $dsnReport, $match)) {
        $result['diag_code'] = $match[1];
        $diagCode = $result['diag_code'];
    }
    
    // If valid status code is detected -> assign bounce_type
    if ($result['status_code']) {
        $result['bounce_type'] = $statusCodes[$result['status_code']]['bounce_type'];
        // If we already have a bounce type at this time, no further action is required -> so return result!
        if ($result['bounce_type']) {
            return $result;
        }
    }
    
    
    /**
     * If we still have no bounce_type, try to parse the diagnostic code
     * based on custom rules.
     */
        
    /*
    sample 1:
    Diagnostic-Code: X-Postfix; me.domain.com platform: said: 552 5.2.2 Over quota (in reply to RCPT TO command)
    sample 2:
    Diagnostic-Code: SMTP; 552 Requested mailbox exceeds quota.
    */
    if (preg_match ('(?:over|exceeds).*quota/is', $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0105';
    }
    
    /*
    sample 1:
    Diagnostic-Code: smtp;552 5.2.2 This message is larger than the current system limit or the recipient's mailbox is full. Create a shorter message body or remove attachments and try sending it again.
    sample 2:
    Diagnostic-Code: X-Postfix; host mta5.us4.domain.com.int[111.111.111.111] said:
      552 recipient storage full, try again later (in reply to RCPT TO command)
    sample 3:
    Diagnostic-Code: X-HERMES; host 127.0.0.1[127.0.0.1] said: 551 bounce as<the
      destination mailbox <xxxxx@yourdomain.com> is full> queue as
      100.1.ZmxEL.720k.1140313037.xxxxx@yourdomain.com (in reply to end of
      DATA command)
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*full/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0145';
    }

    /*
    sample:
    Diagnostic-Code: SMTP; 452 Insufficient system storage
    */
    elseif (preg_match ("/Insufficient system storage/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0134';
    }

    /*
    sample 1:
    Diagnostic-Code: X-Postfix; cannot append message to destination file^M
      /var/mail/dale.me89g: error writing message: File too large^M
    sample 2:
    Diagnostic-Code: X-Postfix; cannot access mailbox /var/spool/mail/b8843022 for^M
      user xxxxx. error writing message: File too large
    */
    elseif (preg_match ("/File too large/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0192';
    }
    
    /*
    sample:
    Diagnostic-Code: smtp;552 5.2.2 This message is larger than the current system limit or the recipient's mailbox is full. Create a shorter message body or remove attachments and try sending it again.
    */
    elseif (preg_match ("/larger than.*limit/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0146';
    }
    
    /*
    sample:
    Diagnostic-Code: X-Notes; User xxxxx (xxxxx@yourdomain.com) not listed in public Name & Address Book
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user)(.*)not(.*)list/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0103';
    }
    
    /*
    sample:
    Diagnostic-Code: smtp; 450 user path no exist
    */
    elseif (preg_match ("/user path no exist/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0106';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 Relaying denied.
    sample 2:
    Diagnostic-Code: SMTP; 554 <xxxxx@yourdomain.com>: Relay access denied
    sample 3:
    Diagnostic-Code: SMTP; 550 relaying to <xxxxx@yourdomain.com> prohibited by administrator
    */
    elseif (preg_match ("/Relay.*(?:denied|prohibited)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0108';
    }

    /*
    sample:
    Diagnostic-Code: SMTP; 554 qq Sorry, no valid recipients (#5.1.3)
    */
    elseif (preg_match ("/no.*valid.*(?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0185';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 «Dªk¦a§} - invalid address (#5.5.0)
    sample 2:
    Diagnostic-Code: SMTP; 550 Invalid recipient: <xxxxx@yourdomain.com>
    sample 3:
    Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: Invalid User
    */
    elseif (preg_match ("/Invalid.*(?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0111';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 delivery error: dd Sorry your message to xxxxx@yourdomain.com cannot be delivered. This account has been disabled or discontinued [#102]. - mta173.mail.tpe.domain.com
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*(?:disabled|discontinued)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0114';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 delivery error: dd This user doesn't have a domain.com account (www.xxxxx@yourdomain.com) [0] - mta134.mail.tpe.domain.com
    */
    elseif (preg_match ("/user doesn't have.*account/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0127';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 5.1.1 unknown or illegal alias: xxxxx@yourdomain.com
    */
    elseif (preg_match ("/(?:unknown|illegal).*(?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0128';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 450 mailbox unavailable.
    sample 2:
    Diagnostic-Code: SMTP; 550 5.7.1 Requested action not taken: mailbox not available
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*(?:un|not\s+)available/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0122';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 sorry, no mailbox here by that name (#5.7.1)
    */
    elseif (preg_match ("/no (?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0123';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 User (xxxxx@yourdomain.com) unknown.
    sample 2:
    Diagnostic-Code: SMTP; 553 5.3.0 <xxxxx@yourdomain.com>... Addressee unknown, relay=[111.111.111.000]
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*unknown/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0125';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 user disabled
    sample 2:
    Diagnostic-Code: SMTP; 452 4.2.1 mailbox temporarily disabled: xxxxx@yourdomain.com
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*disabled/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0133';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: Recipient address rejected: No such user (xxxxx@yourdomain.com)
    */
    elseif (preg_match ("/No such (?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0143';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 MAILBOX NOT FOUND
    sample 2:
    Diagnostic-Code: SMTP; 550 Mailbox ( xxxxx@yourdomain.com ) not found or inactivated
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*NOT FOUND/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0136';
    }
    
    /*
    sample:
    Diagnostic-Code: X-Postfix; host m2w-in1.domain.com[111.111.111.000] said: 551
    <xxxxx@yourdomain.com> is a deactivated mailbox (in reply to RCPT TO
    command)
    */
    elseif (preg_match ("/deactivated (?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0138';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com> recipient rejected
    ...
    <<< 550 <xxxxx@yourdomain.com> recipient rejected
    550 5.1.1 xxxxx@yourdomain.com... User unknown
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*reject/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0148';
    }
    
    /*
    sample:
    Diagnostic-Code: smtp; 5.x.0 - Message bounced by administrator  (delivery attempts: 0)
    */
    elseif (preg_match ("/bounce.*administrator/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0151';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 <maxqin> is now disabled with MTA service.
    */
    elseif (preg_match ("/<.*>.*disabled/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0152';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 551 not our customer
    */
    elseif (preg_match ("/not our customer/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0154';
    }
    
    /*
    sample:
    Diagnostic-Code: smtp; 5.1.0 - Unknown address error 540-'Error: Wrong recipients' (delivery attempts: 0)
    */
    elseif (preg_match ("/Wrong (?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0159';
    }
    
    /*
    sample:
    Diagnostic-Code: smtp; 5.1.0 - Unknown address error 540-'Error: Wrong recipients' (delivery attempts: 0)
    sample 2:
    Diagnostic-Code: SMTP; 501 #5.1.1 bad address xxxxx@yourdomain.com
    */
    elseif (preg_match ("/(?:unknown|bad).*(?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0160';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Command RCPT User <xxxxx@yourdomain.com> not OK
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*not OK/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0186';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 5.7.1 Access-Denied-XM.SSR-001
    */
    elseif (preg_match ("/Access.*Denied/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0189';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 5.1.1 <xxxxx@yourdomain.com>... email address lookup in domain map failed^M
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*lookup.*fail/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0195';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 User not a member of domain: <xxxxx@yourdomain.com>^M
    */
    elseif (preg_match ("/(?:recipient|address|email|mailbox|user).*not.*member of domain/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0198';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550-"The recipient cannot be verified.  Please check all recipients of this^M
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*cannot be verified/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0202';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Unable to relay for xxxxx@yourdomain.com
    */
    elseif (preg_match ("/Unable to relay/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0203';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 xxxxx@yourdomain.com:user not exist
    sample 2:
    Diagnostic-Code: SMTP; 550 sorry, that recipient doesn't exist (#5.7.1)
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*(?:n't|not) exist/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0205';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550-I'm sorry but xxxxx@yourdomain.com does not have an account here. I will not
    */
    elseif (preg_match ("/not have an account/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0207';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 This account is not allowed...xxxxx@yourdomain.com
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*is not allowed/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0220';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: inactive user
    */
    elseif (preg_match ("/inactive.*(?:alias|account|recipient|address|email|mailbox|user)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0135';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 xxxxx@yourdomain.com Account Inactive
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*Inactive/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0155';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: Recipient address rejected: Account closed due to inactivity. No forwarding information is available.
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user) closed due to inactivity/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0170';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>... User account not activated
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user) not activated/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0177';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 User suspended
    sample 2:
    Diagnostic-Code: SMTP; 550 account expired
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*(?:suspend|expire)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0183';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 5.3.0 <xxxxx@yourdomain.com>... Recipient address no longer exists
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*no longer exist/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0184';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 VS10-RT Possible forgery or deactivated due to abuse (#5.1.1) 111.111.111.211^M
    */
    elseif (preg_match ("/(?:forgery|abuse)/is",$diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0196';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 mailbox xxxxx@yourdomain.com is restricted
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*restrict/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0209';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 <xxxxx@yourdomain.com>: User status is locked.
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*locked/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0228';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 User refused to receive this mail.
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user) refused/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0156';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 501 xxxxx@yourdomain.com Sender email is not in my domain
    */
    elseif (preg_match ("/sender.*not/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0206';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 Message refused
    */
    elseif (preg_match ("/Message refused/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0175';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 5.0.0 <xxxxx@yourdomain.com>... No permit
    */
    elseif (preg_match ("/No permit/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0190';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 sorry, that domain isn't in my list of allowed rcpthosts (#5.5.3 - chkuser)
    */
    elseif (preg_match ("/domain isn't in.*allowed rcpthost/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0191';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 AUTH FAILED - xxxxx@yourdomain.com^M
    */
    elseif (preg_match ("/AUTH FAILED/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0197';
    }
    
    /*
    sample 1:
    Diagnostic-Code: SMTP; 550 relay not permitted^M
    sample 2:
    Diagnostic-Code: SMTP; 530 5.7.1 Relaying not allowed: xxxxx@yourdomain.com
    */
    elseif (preg_match ("/relay.*not.*(?:permit|allow)/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0201';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 not local host domain.com, not a gateway
    */
    elseif (preg_match ("/not local host/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0204';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 500 Unauthorized relay msg rejected
    */
    elseif (preg_match ("/Unauthorized relay/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0215';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 Transaction failed
    */
    elseif (preg_match ("/Transaction.*fail/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0221';
    }
    
    /* 
    sample:
    Diagnostic-Code: smtp;554 5.5.2 Invalid data in message
    */
    elseif (preg_match ("/Invalid data/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0223';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Local user only or Authentication mechanism
    */
    elseif (preg_match ("/Local user only/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0224';
    }

    /*
    sample:
    Diagnostic-Code: SMTP; 550-ds176.domain.com [111.111.111.211] is currently not permitted to
    relay through this server. Perhaps you have not logged into the pop/imap
    server in the last 30 minutes or do not have SMTP Authentication turned on
    in your email client.
    */
    elseif (preg_match ("/not.*permit.*to/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0225';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Content reject. FAAAANsG60M9BmDT.1
    */
    elseif (preg_match ("/Content reject/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0165';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 552 MessageWall: MIME/REJECT: Invalid structure
    */
    elseif (preg_match ("/MIME\/REJECT/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0212';
    }
    
    /*
    sample:
    Diagnostic-Code: smtp; 554 5.6.0 Message with invalid header rejected, id=13462-01 - MIME error: error: UnexpectedBound: part didn't end with expected boundary [in multipart message]; EOSToken: EOF; EOSType: EOF
    */
    elseif (preg_match ("/MIME error/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0217';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 Mail data refused by AISP, rule [169648].
    */
    elseif (preg_match ("/Mail data refused.*AISP/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0218';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Host unknown
    */
    elseif (preg_match ("/Host unknown/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0130';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 Specified domain is not allowed.
    */
    elseif (preg_match ("/Specified domain.*not.*allow/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0180';
    }
    
    /*
    sample:
    Diagnostic-Code: X-Postfix; delivery temporarily suspended: connect to
    111.111.11.112[111.111.11.112]: No route to host
    */
    elseif (preg_match ("/No route to host/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0188';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 unrouteable address
    */
    elseif (preg_match ("/unrouteable address/is", $diagCode)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0208';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 451 System(u) busy, try again later.
    */
    elseif (preg_match ("/System.*busy/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0112';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 451 mta172.mail.tpe.domain.com Resources temporarily unavailable. Please try again later.  [#4.16.4:70].
    */
    elseif (preg_match ("/Resources temporarily unavailable/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0116';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 sender is rejected: 0,mx20,wKjR5bDrnoM2yNtEZVAkBg==.32467S2
    */
    elseif (preg_match ("/sender is rejected/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0101';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 <unknown[111.111.111.000]>: Client host rejected: Access denied
    */
    elseif (preg_match ("/Client host rejected/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0102';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 Connection refused(mx). MAIL FROM [xxxxx@yourdomain.com] mismatches client IP [111.111.111.000].
    */
    elseif (preg_match ("/MAIL FROM(.*)mismatches client IP/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0104';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 Please visit http:// antispam.domain.com/denyip.php?IP=111.111.111.000 (#5.7.1)
    */
    elseif (preg_match ("/denyip/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0144';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 Service unavailable; Client host [111.111.111.211] blocked using dynablock.domain.com; Your message could not be delivered due to complaints we received regarding the IP address you're using or your ISP. See http:// blackholes.domain.com/ Error: WS-02^M
    */
    elseif (preg_match ("/client host.*blocked/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0201';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Requested action not taken: mail IsCNAPF76kMDARUY.56621S2 is rejected,mx3,BM
    */
    elseif (preg_match ("/mail.*reject/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0147';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 552 sorry, the spam message is detected (#5.6.0)
    */
    elseif (preg_match ("/spam.*detect/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0162';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 5.7.1 Rejected as Spam see: http:// rejected.domain.com/help/spam/rejected.html
    */
    elseif (preg_match ("/reject.*spam/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0216';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 5.7.1 <xxxxx@yourdomain.com>... SpamTrap=reject mode, dsn=5.7.1, Message blocked by BOX Solutions (www.domain.com) SpamTrap Technology, please contact the domain.com site manager for help: (ctlusr8012).^M
    */
    elseif (preg_match ("/SpamTrap/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0200';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Verify mailfrom failed,blocked
    */
    elseif (preg_match ("/Verify mailfrom failed/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0210';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 Error: MAIL FROM is mismatched with message header from address!
    */
    elseif (preg_match ("/MAIL.*FROM.*mismatch/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0226';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 5.7.1 Message scored too high on spam scale.  For help, please quote incident ID 22492290.
    */
    elseif (preg_match ("/spam scale/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0211';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 5.7.1 reject: Client host bypassing service provider's mail relay: ds176.domain.com
    */
    elseif (preg_match ("/Client host bypass/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0229';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 550 sorry, it seems as a junk mail
    */
    elseif (preg_match ("/junk mail/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0230';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553-Message filtered. Please see the FAQs section on spam
    */
    elseif (preg_match ("/message filtered/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0227';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 554 5.7.1 The message from (<xxxxx@yourdomain.com>) with the subject of ( *(ca2639) 7|-{%2E* : {2"(%EJ;y} (SBI$#$@<K*:7s1!=l~) matches a profile the Internet community may consider spam. Please revise your message before resending.
    */
    elseif (preg_match ("/subject.*consider.*spam/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0222';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 451 Temporary local problem - please try later
    */
    elseif (preg_match ("/Temporary local problem/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0142';
    }
    
    /*
    sample:
    Diagnostic-Code: SMTP; 553 5.3.5 system config error
    */
    elseif (preg_match ("/system config error/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0153';
    }
    
    /*
    sample:
    Diagnostic-Code: X-Postfix; delivery temporarily suspended: conversation with^M
    111.111.111.11[111.111.111.11] timed out while sending end of data -- message may be^M
    sent more than once
    */
    elseif (preg_match ("/delivery.*suspend/is", $diagCode)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0213';
    }

    /*
    sample:
    ----- The following addresses had permanent fatal errors -----
    <xxxxx@yourdomain.com>
    ----- Transcript of session follows -----
    ... while talking to mta1.domain.com.:
    >>> DATA
    <<< 503 All recipients are invalid
    554 5.0.0 Service unavailable
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user)(.*)invalid/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0107';
    }
    
    /*
    sample:
    ----- Transcript of session follows -----
    xxxxx@yourdomain.com... Deferred: No such file or directory
    */
    elseif (preg_match ("/Deferred.*No such.*(?:file|directory)/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0141';
    }
    
    /*
    sample:
    Failed to deliver to '<xxxxx@yourdomain.com>'^M
    LOCAL module(account xxxx) reports:^M
    mail receiving disabled^M
    */
    elseif (preg_match ("/mail receiving disabled/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0194';
    }
    
    /*
    sample:
    - These recipients of your message have been processed by the mail server:^M
    xxxxx@yourdomain.com; Failed; 5.1.1 (bad destination mailbox address)
    */
    elseif (preg_match ("/bad.*(?:alias|account|recipient|address|email|mailbox|user)/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '227';
    }
    
    /*
    sample 1:
    This Message was undeliverable due to the following reason:
    The user(s) account is temporarily over quota.
    <xxxxx@yourdomain.com>
    sample 2:
     Recipient address: xxxxx@yourdomain.com
     Reason: Over quota
    */
    elseif (preg_match ("/over.*quota/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0131';
    }
    
    /*
    sample:
    Sorry the recipient quota limit is exceeded.
    This message is returned as an error.
    */
    elseif (preg_match ("/quota.*exceeded/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0150';
    }
    
    /*
    sample:
    The user to whom this message was addressed has exceeded the allowed mailbox
    quota. Please resend the message at a later time.
    */
    elseif (preg_match ("/exceed.*\n?.*quota/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0187';
    }
    
    /*
    sample 1:
    Failed to deliver to '<xxxxx@yourdomain.com>'
    LOCAL module(account xxxxxx) reports:
    account is full (quota exceeded)
    sample 2:
    Error in fabiomod_sql_glob_init: no data source specified - database access disabled
    [Fri Feb 17 23:29:38 PST 2006] full error for caltsmy:
    that member's mailbox is full
    550 5.0.0 <xxxxx@yourdomain.com>... Can't create output
    */
    elseif (preg_match ("/(?:alias|account|recipient|address|email|mailbox|user).*full/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0132';
    }
    
    /*
    sample:
    gaosong "(0), ErrMsg=Mailbox space not enough (space limit is 10240KB)
    */
    elseif (preg_match ("/space.*not.*enough/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0219';
    }
    
    /*
    sample 1:
    ----- Transcript of session follows -----
    xxxxx@yourdomain.com... Deferred: Connection refused by nomail.tpe.domain.com.
    Message could not be delivered for 5 days
    Message will be deleted from queue
    sample 2:
    451 4.4.1 reply: read error from www.domain.com.
    xxxxx@yourdomain.com... Deferred: Connection reset by www.domain.com.
    */
    elseif (preg_match ("/Deferred.*Connection (?:refused|reset)/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0115';
    }
    
    /*
    sample:
    ----- The following addresses had permanent fatal errors -----
    Tan XXXX SSSS <xxxxx@yourdomain..com>
    ----- Transcript of session follows -----
    553 5.1.2 XXXX SSSS <xxxxx@yourdomain..com>... Invalid host name
    */
    elseif (preg_match ("/Invalid host name/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0109';
    }
    
    /*
    sample:
    ----- Transcript of session follows -----
    xxxxx@yourdomain.com... Deferred: mail.domain.com.: No route to host
    */
    elseif (preg_match ("/Deferred.*No route to host/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0109';
    }
    
    /*
    sample:
    ----- Transcript of session follows -----
    550 5.1.2 xxxxx@yourdomain.com... Host unknown (Name server: .: no data known)
    */
    elseif (preg_match ("/Host unknown/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0140';
    }
    
    /*
    sample:
    : Host or domain name not found. Name
    service error for name=domain.com type=MX: Host not found, try
    again
    */
    elseif (preg_match ("/Host not found/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9005';
    }
    
    /*
    (Exchange Server 2007 message)
    sample:
    albert.huber@agrana.at
    #550 5.1.1 RESOLVER.ADR.RecipNotFound; not found ##
    */
    elseif (preg_match ("/not found/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9006';
    }

    /*
    sample:
    ----- Transcript of session follows -----
    451 HOTMAIL.com.tw: Name server timeout
    Message could not be delivered for 5 days
    Message will be deleted from queue
    */
    elseif (preg_match ("/Name server timeout/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0118';
    }
    
    /*
    sample:
    ----- Transcript of session follows -----
    xxxxx@yourdomain.com... Deferred: Connection timed out with hkfight.com.
    Message could not be delivered for 5 days
    Message will be deleted from queue
    */
    elseif (preg_match ("/Deferred.*Connection.*tim(?:e|ed).*out/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0119';
    }
    
    /*
    sample:
    ----- Transcript of session follows -----
    xxxxx@yourdomain.com... Deferred: Name server: domain.com.: host name lookup failure
    */
    elseif (preg_match ("/Deferred.*host name lookup failure/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0121';
    }
    
    /*
    rule: dns_loop
    sample:
    ----- Transcript of session follows -----^M
    554 5.0.0 MX list for znet.ws. points back to mail01.domain.com^M
    554 5.3.5 Local configuration error^M
    */
    elseif (preg_match ("/MX list.*point.*back/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0199';
    }
    
    /***********************************
    rule: dns_loop
    sample:
    : mail for domain.com loops back to myself
    */
    elseif (preg_match ("/loop.*back/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9007';
    }
    
    /***********************************
    rule: syntax_error
    sample:
    : host domain.com[195.186.19.143] said: 501
    5.5.2 RCPT TO syntax error (in reply to RCPT TO command)
    */
    elseif (preg_match ("/syntax.*error/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9008';
    }
    
    /*
    rule: internal_error
    sample:
    ----- Transcript of session follows -----
    451 4.0.0 I/O error
    */
    elseif (preg_match ("/I\/O error/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0120';
    }
    
    /*
    rule: internal_error
    sample:
    Failed to deliver to 'xxxxx@yourdomain.com'^M
    SMTP module(domain domain.com) reports:^M
    connection with mx1.mail.domain.com is broken^M
    */
    elseif (preg_match ("/connection.*broken/i", $dsnMsg)) {
        $result['bounce_type'] = 'soft';
        $result['rule_no']     = '0231';
    }
    
    /*
    rule: other
    sample:
    Delivery to the following recipients failed.
    xxxxx@yourdomain.com
    */
    elseif (preg_match ("/Delivery to the following recipients failed.*\n.*\n.*".$result['email']."/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0176';
    }
    
    // Followings are wind-up rule: must be the last one
    //   many other rules msg end up with "550 5.1.1 ... User unknown"
    //   many other rules msg end up with "554 5.0.0 Service unavailable"

    /*
    sample 1:
    ----- The following addresses had permanent fatal errors -----^M
    <xxxxx@yourdomain.com>^M
    (reason: User unknown)^M
    
    sample 2:
    550 5.1.1 xxxxx@yourdomain.com... User unknown^M
    */
    elseif (preg_match ("/User unknown/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0193';
    }
    
    /*
    sample:
    554 5.0.0 Service unavailable
    */
    elseif (preg_match ("/Service unavailable/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '0214';
    }
    
    /*
    sample:
    Name or service not known
    */
    elseif (preg_match ("/Name or service not\s+known/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9003';
    }
    
    /*
    rule: loop
    sample:
    554 5.4.6 Too many hops
    */
    elseif (preg_match ("/Too many hops/i", $dsnMsg)) {
        $result['bounce_type'] = 'hard';
        $result['rule_no']     = '9004';
    }
            
    return $result;
}
