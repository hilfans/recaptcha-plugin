<?php
/*
Plugin Name: reCaptcha
Plugin URI: http://your-own-domain-here.com/articles/hey-test-my-sample-plugin/
Description: Implement reCaptcha on public submission page
Version: 1.0
Author: Simon Briggs
Author URI: 
*/

function spb_recaptcha_check_Captcha($value, $url, $keyword, $title)
{
	// If the user is logged in then we don't need to process the captcha
	if (yourls_is_valid_user()===true)
	{
		return false;
	}
	$recaptcha=$_POST['g-recaptcha-response'];
	if(!empty($recaptcha))
	{
	    $privkey = yourls_get_option( 'spb_recaptcha_priv_key', "" );

		$google_url="https://www.google.com/recaptcha/api/siteverify";
		$secret=$privkey;
		$ip=$_SERVER['REMOTE_ADDR'];
		$url=$google_url."?secret=".$secret."&response=".$recaptcha."&remoteip=".$ip;
		$res=spb_recaptcha_getCurlData($url);
		$res= json_decode($res, true);
		//reCaptcha success check 
		if($res['success'])
		{
			return false;
		}
		else
		{
			$response['message'] ="Sorry, but you didn't pass the reCaptcha test";
			return $response;
		}
	}
	else
	{
		$response['message'] ="Sorry, but you didn't pass the reCaptcha test";
		return $response;
	}
}

function spb_recaptcha_getCurlData($url)
{
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
$curlData = curl_exec($curl);
curl_close($curl);
return $curlData;
}

function spb_recaptcha_add_Captcha_Script()
{
	$pubkey = yourls_get_option( 'spb_recaptcha_pub_key', "" );
	if (!(yourls_is_valid_user()===true))
	{
		echo ("<script src='https://www.google.com/recaptcha/api.js'></script>");
		echo("<div class='g-recaptcha' data-sitekey='" . $pubkey . "'></div>");
	}
}

function spb_recaptcha_plugin_init() {
    yourls_register_plugin_page( 'spb_recaptcha', 'reCaptcha settings', 'spb_recaptcha_configpage_display' );
}

// The function that will draw the admin page
function spb_recaptcha_configpage_display() {
	 if( isset( $_POST['spb_recaptcha_public_key'] ) ) {
	        yourls_verify_nonce( 'spb_recaptcha_nonce' );
	        spb_recaptcha_save_admin();
	    }
    
    $nonce = yourls_create_nonce( 'spb_recaptcha_nonce' );
    $pubkey = yourls_get_option( 'spb_recaptcha_pub_key', "" );
    $privkey = yourls_get_option( 'spb_recaptcha_priv_key', "" );

    echo '<h2>reCaptcha plugin settings</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="nonce" value="' . $nonce . '" />';
    echo '<p><label for="spb_recaptcha_public_key">reCaptcha site key: </label>';
    echo '<input type="text" id="spb_recaptcha_public_key" name="spb_recaptcha_public_key" value="' . $pubkey . '"></p>';  
    echo '<p><label for="spb_recaptcha_private_key">reCaptcha secret key: </label>';
    echo '<input type="text" id="spb_recaptcha_private_key" name="spb_recaptcha_private_key" value="' . $privkey . '"></p>';
    echo '<input type="submit"/>';
    echo '</form>';
}

function spb_recaptcha_save_admin()
{
	$pubkey = $_POST['spb_recaptcha_public_key'];
	$privkey = $_POST['spb_recaptcha_private_key'];
	 if ( yourls_get_option( 'spb_recaptcha_pub_key' ) !== false ) {
        yourls_update_option( 'spb_recaptcha_pub_key', $pubkey );
    } else {
        yourls_add_option( 'spb_recaptcha_pub_key', $pubkey );
    }
	 if ( yourls_get_option( 'spb_recaptcha_priv_key' ) !== false ) {
        yourls_update_option( 'spb_recaptcha_priv_key', $privkey );
    } else {
        yourls_add_option( 'spb_recaptcha_priv_key', $privkey );
    }
    echo "Saved";
}

yourls_add_action( 'plugins_loaded', 'spb_recaptcha_plugin_init' );
yourls_add_filter( 'shunt_add_new_link', 'spb_recaptcha_check_Captcha' );
//yourls_add_action( 'html_head',"spb_recaptcha_add_Captcha_Script");

?>
