<?php

declare(strict_types=1);
/*******************************************************************************
 *                      PHP Paypal IPN Integration Class
 *******************************************************************************
 *      Author:     Micah Carrick
 *      Email:      email@micahcarrick.com
 *      Website:    http://www.micahcarrick.com
 *
 *      File:       paypal.class.php
 *      Version:    1.3.0
 *      Copyright:  (c) 2005 - Micah Carrick
 *                  You are free to use, distribute, and modify this software
 *                  under the terms of the GNU General Public License.  See the
 *                  included license.txt file.
 *
 *******************************************************************************
 *  VERION HISTORY:
 *      v1.3.0 [10.10.2005] - Fixed it so that single quotes are handled the
 *                            right way rather than simple stripping them.  This
 *                            was needed because the user could still put in
 *                            quotes.
 *
 *      v1.2.1 [06.05.2005] - Fixed typo from previous fix :)
 *
 *      v1.2.0 [05.31.2005] - Added the optional ability to remove all quotes
 *                            from the paypal posts.  The IPN will come back
 *                            invalid sometimes when quotes are used in certian
 *                            fields.
 *
 *      v1.1.0 [05.15.2005] - Revised the form output in the submit_paypal_post
 *                            method to allow non-javascript capable browsers
 *                            to provide a means of manual form submission.
 *
 *      v1.0.0 [04.16.2005] - Initial Version
 *
 *******************************************************************************
 *  DESCRIPTION:
 *
 *      NOTE: See www.micahcarrick.com for the most recent version of this class
 *            along with any applicable sample files and other documentaion.
 *
 *      This file provides a neat and simple method to interface with paypal and
 *      The paypal Instant Payment Notification (IPN) interface.  This file is
 *      NOT intended to make the paypal integration "plug 'n' play". It still
 *      requires the developer (that should be you) to understand the paypal
 *      process and know the variables you want/need to pass to paypal to
 *      achieve what you want.
 *
 *      This class handles the submission of an order to paypal aswell as the
 *      processing an Instant Payment Notification.
 *
 *      This code is based on that of the php-toolkit from paypal.  I've taken
 *      the basic principals and put it in to a class so that it is a little
 *      easier--at least for me--to use.  The php-toolkit can be downloaded from
 *      http://sourceforge.net/projects/paypal.
 *
 *      To submit an order to paypal, have your order form POST to a file with:
 *
 *          $p = new paypal_class;
 *          $p->add_field('business', 'somebody@domain.com');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_paypal_post();
 *
 *      To process an IPN, have your IPN processing file contain:
 *
 *          $p = new paypal_class;
 *          if ($p->validate_ipn()) {
 *          ... (IPN is verified.  Details are in the ipn_data() array)
 *          }
 *
 *
 *      In case you are new to paypal, here is some information to help you:
 *
 *      1. Download and read the Merchant User Manual and Integration Guide from
 *         http://www.paypal.com/en_US/pdf/integration_guide.pdf.  This gives
 *         you all the information you need including the fields you can pass to
 *         paypal (using add_field() with this class) aswell as all the fields
 *         that are returned in an IPN post (stored in the ipn_data() array in
 *         this class).  It also diagrams the entire transaction process.
 *
 *      2. Create a "sandbox" account for a buyer and a seller.  This is just
 *         a test account(s) that allow you to test your site from both the
 *         seller and buyer perspective.  The instructions for this is available
 *         at https://developer.paypal.com/ as well as a great forum where you
 *         can ask all your paypal integration questions.  Make sure you follow
 *         all the directions in setting up a sandbox test environment, including
 *         the addition of fake bank accounts and credit cards.
 *
 *******************************************************************************
 */

namespace Empathy\ELib\ThirdParty;

use Empathy\MVC\Config;

class PaypalClass
{
    public mixed $last_error = '';                 // holds the last error encountered
    public mixed $ipn_log = true;                    // bool: log IPN results to text file?
    public mixed $ipn_log_file;               // filename of the IPN log
    public mixed $ipn_response = '';               // holds the IPN response from paypal
    public mixed $ipn_data = [];         // array contains the POST values for IPN
    public mixed $fields = [];           // array holds the fields to submit to paypal
    // initialization constructor.  Called when class is created.
    public mixed $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';


    public function __construct()
    {

        $this->ipn_log_file = Config::get('DOC_ROOT') . '/logs/.ipn_results.log';

        // populate $fields array with a few default values.  See the paypal
        // documentation for a list of fields and their data types. These defaul
        // values can be overwritten by the calling script.

        $this->add_field('rm', '2');           // Return method = POST
        $this->add_field('cmd', '_xclick');

    }

    public function add_field(mixed $field, mixed $value): void
    {

        // adds a key=>value pair to the fields array, which is what will be
        // sent to paypal as POST variables.  If the value is already in the
        // array, it will be overwritten.

        $this->fields["$field"] = $value;
    }

    public function submit_paypal_post(): void
    {

        // this function actually generates an entire HTML page consisting of
        // a form with hidden elements which is submitted to paypal via the
        // BODY element's onLoad attribute.  We do this so that you can validate
        // any POST vars from you custom form before submitting to paypal.  So
        // basically, you'll have your own form which is submitted to your script
        // to validate the data, which in turn calls this function to create
        // another hidden form and submit to paypal.

        // The user will briefly see a message on the screen that reads:
        // "Please wait, your order is being processed..." and then immediately
        // is redirected to paypal.

        echo "<html>\n";
        echo "<head><title>Processing Payment...</title></head>\n";
        echo "<body onLoad=\"document.forms['paypal_form'].submit();\">\n";
        echo '<center><h2>Please wait, your order is being processed and you';
        echo " will be redirected to the paypal website.</h2></center>\n";
        echo '<form method="post" name="paypal_form" ';
        echo 'action="' . $this->paypal_url . "\">\n";

        foreach ($this->fields as $name => $value) {
            echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
        }
        echo '<center><br/><br/>If you are not automatically redirected to ';
        echo "paypal within 5 seconds...<br/><br/>\n";
        echo "<input type=\"submit\" value=\"Click Here\"></center>\n";

        echo "</form>\n";
        echo "</body></html>\n";

    }

    public function validate_ipn(): mixed
    {
        $raw_post_data = file_get_contents('php://input');
        if (!is_string($raw_post_data)) {
            $this->last_error = 'Empty IPN body';
            $this->log_ipn_results(false);

            return false;
        }
        $this->ipn_response = '';

        // Keep parsed values for your app/logging
        parse_str($raw_post_data, $this->ipn_data);

        $post_data = 'cmd=_notify-validate&' . $raw_post_data;

        $ch = curl_init($this->paypal_url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Connection: close',
        ]);

        $curlBody = curl_exec($ch);

        if ($curlBody === false || !is_string($curlBody)) {
            $this->last_error = 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            $this->log_ipn_results(false);
            return false;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->ipn_response = trim($curlBody);

        if ($http_code !== 200) {
            $this->last_error = 'PayPal responded with HTTP ' . $http_code;
            $this->log_ipn_results(false);
            return false;
        }

        if ($this->ipn_response === 'VERIFIED') {
            $this->log_ipn_results(true);
            return true;
        }

        $this->last_error = 'IPN Validation Failed. Response: ' . $this->ipn_response;
        $this->log_ipn_results(false);
        return false;
    }

    public function log_ipn_results(mixed $success): void
    {

        if (!$this->ipn_log) {
            return;
        }  // is logging turned off?

        // Timestamp
        $text = '[' . date('m/d/Y g:i A') . '] - ';

        // Success or failure being logged?
        if ($success) {
            $text .= "SUCCESS!\n";
        } else {
            $text .= 'FAIL: ' . $this->last_error . "\n";
        }

        // Log the POST variables
        $text .= "IPN POST Vars from Paypal:\n";
        foreach ($this->ipn_data as $key => $value) {
            $text .= "$key=$value, ";
        }

        // Log the response from the paypal server
        $text .= "\nIPN Response from Paypal Server:\n " . $this->ipn_response;

        // Write to log
        $fp = fopen($this->ipn_log_file, 'a');
        if ($fp === false) {
            return;
        }
        fwrite($fp, $text . "\n\n");

        fclose($fp);  // close file
    }

    public function dump_fields(): void
    {

        // Used for debugging, this function will output all the field/value pairs
        // that are currently defined in the instance of the class using the
        // add_field() function.

        echo '<h3>paypal_class->dump_fields() Output:</h3>';
        echo '<table width="95%" border="1" cellpadding="2" cellspacing="0">
            <tr>
               <td bgcolor="black"><b><font color="white">Field Name</font></b></td>
               <td bgcolor="black"><b><font color="white">Value</font></b></td>
            </tr>';

        ksort($this->fields);
        foreach ($this->fields as $key => $value) {
            echo "<tr><td>$key</td><td>" . urldecode((string) $value) . '&nbsp;</td></tr>';
        }

        echo '</table><br>';
    }
}
