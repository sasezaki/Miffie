<?php
/**
 * Extra package.xml settings such as dependencies.
 * More information: http://pear.php.net/manual/en/pyrus.commands.make.php#pyrus.commands.make.packagexmlsetup
 */

$package->channel = $compatible->channel
    = 'pear.diggin.musicrider.com';
$package->rawlead = $compatible->rawlead
    = array(
        'name' => 'sasezaki',
        'user'=>'sasezaki',
        'email'=>'sasezaki@gmail.com',
        'active'=>'yes'
    );
$package->license = $compatible->license
    = 'LGPL';
$package->dependencies['required']->php = $compatible->dependencies['required']->php
    = '5.3.2';

$package->description = $compatible->description
    = "Scraping toolkit";

$package->dependencies['required']->extension['mbstring']->save();
$compatible->dependencies['required']->extension['mbstring']->save();
$package->dependencies['required']->extension['tidy']->save();
$compatible->dependencies['required']->extension['tidy']->save();



/**
 * for example:
$package->dependencies['required']->package['pear2.php.net/PEAR2_Autoload']->save();
$package->dependencies['required']->package['pear2.php.net/PEAR2_Exception']->save();
$package->dependencies['required']->package['pear2.php.net/PEAR2_MultiErrors']->save();
$package->dependencies['required']->package['pear2.php.net/PEAR2_HTTP_Request']->save();

$compatible->dependencies['required']->package['pear2.php.net/PEAR2_Autoload']->save();
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_Exception']->save();
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_MultiErrors']->save();
$compatible->dependencies['required']->package['pear2.php.net/PEAR2_HTTP_Request']->save();

// ignore files
unset($package->files['www/config.inc.php']);
unset($package->files['www/.htaccess']);
*/
?>
