<div id="sms_box" class="page-width" style="text-align:center;background-color:#fff3cd;color:#8d6604;padding:1.5rem;font-size:1.5em;">
	<div id='flp_box_link'>
		<p style="color:#9b6927;margin-bottom:0;"><img src="https://www.fraudlabspro.com/images/icons/ico-warning.png" width="18"style="vertical-align:-2px;"> {$sms_msg_sms_required}</p>
		<input class="btn" type="button" style="margin-bottom:0;margin-right:5px;margin-top:10px;font-size:15px;" name="verify_flp_sms_inline" id="verify_flp_sms_inline" value="Verify your phone number" data-fancybox data-src="#flp-sms-verification" href="javascript:;">
	</div>
</div>
<div id="flp_box_success" class="page-width" style="text-align:center;background-color:#d4edda;color:#2e7769;padding:1.5rem;font-size:1.5em;display:none;"><p style="margin-bottom:0;"><img src="https://www.fraudlabspro.com/images/icons/ico-tick.png" width="18" style="vertical-align:-2px;">Thank you. You have successfully completed the SMS verification.</p></div>
<div id="flp-sms-verification" style="display:none;max-width:50%;">
	<div id="flp_sms" class="page-width" style="font-size:14px;text-align:center;">
		<img src="https://www.fraudlabspro.com/images/icons/icon-send-otp.png" width="110" style="margin-top:25px;margin-bottom:15px;">
		<h2 id="verifysms" style="margin-bottom:7px;">Verify Phone Number <span id="sms_span" style="font-size:.8rem">(via SMS Verification)</span></h2>
		<div id="sms_err" style="background-color:#f8d7da;color:#7d5880;padding:10px;margin-bottom:20px;font-size:1em;display:none;"></div>
		<p id="sms_info_display" style="font-size:16px;margin-bottom:40px;">{$sms_instruction}</p>
		<label for="phone_number" id="enter_phone_number">
			<input type="text" class="field__input" style="width:100%;text-align:center;border:1px solid silver;" name="phone_number" id="phone_number" value="">
		</label>
		<span id="sms_phone_info" style="display:none;font-size:16px;margin-bottom:15px;"></span>
		<div id="sms_otp" style="margin-top:5px; display:none;"><input type="text" name="sms_otp1" id="sms_otp1" value="" style="width:110px; background-color:white; border: none; box-shadow:none !important; font-family:Courier New, Courier, monospace !important; font-size:20px; display:inline-block;">&nbsp;&nbsp;-&nbsp;&nbsp;<input type="text" name="sms_otp2" id="sms_otp2" value="" placeholder="Enter OTP numbers" style="padding:9px 10px; font-size:17px; width:200px; display:inline-block;"></div>
		<p id="resend_otp_text" style="display:none;">Didn't receive the OTP code? <a href="javascript:;" name="resend_otp" id="resend_otp" value="Resend OTP" style="margin-right:5px;display:none;" >Resend</a></p>
		<div id="sms_section" style="margin-bottom:20px;"></div>
		<input class="btn" type="button" name="submit_otp" id="submit_otp" value="Submit OTP" style="margin-right:5px;margin-bottom:10px;display:none;">
		<input class="btn" type="button" name="get_otp" id="get_otp" value="Get OTP" style="margin-right:5px;margin-bottom:10px;"> 
		<input class="btn btn--secondary" type="button" name="reset_pn" id="reset_pn" value="Reset Phone" style="margin-right:5px;margin-bottom:10px;display:none;">
		<input type="hidden" name="sms_phone_cc" id="sms_phone_cc" value="{$sms_cc}">
		<input type="hidden" name="sms_verified" id="sms_verified" value="">
		<input type="hidden" name="sms_tran_id" id="sms_tran_id" value="">
		<input type="hidden" name="sms_order_id" id="sms_order_id" value="{$sms_order_id}">
	</div>
	<div id="sms_success_status" style="text-align:center;background-color:#fff;color:#2e7769;padding:10px 0;font-size:1.25em;display:none;">Thank you. You have successfully completed the SMS verification.</div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.5/js/intlTelInput.min.js"></script>
<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.5/css/intlTelInput.min.css">
<script src="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css">
<script>
	jQuery(document).ready(function() {
		var phoneNum;
		var order_id = jQuery("#sms_order_id").val();
		var msg_otp_success = {$sms_msg_otp_success};
		msg_otp_success = msg_otp_success.split("[phone]");
		var msg_otp_fail = {$sms_msg_otp_fail};
		msg_otp_fail = msg_otp_fail.split("[phone]");
		var msg_invalid_phone = {$sms_msg_invalid_phone};
		var msg_invalid_otp = {$sms_msg_invalid_otp};

		if (jQuery("#sms_phone_cc").length) {
			defaultCc = jQuery("#sms_phone_cc").val();
		} else {
			defaultCc = 'US';
		}
		jQuery(document).ready(function(){
			phoneNum = window.intlTelInput(document.querySelector("#phone_number"), {
				utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.5/js/utils.min.js",
				separateDialCode: true,
				initialCountry: defaultCc
			});
		});

		jQuery("#sms_otp2").bind("keypress", function(e) {
			var code = e.keyCode || e.which;
			if (code == 13){
				e.preventDefault();
			}
		});

		jQuery("#get_otp").click(function(e) {
			if (jQuery("#phone_number").val() == "") {
				jQuery("#sms_err").html(msg_invalid_phone);
				jQuery("#sms_err").show();
				jQuery("#phone_number").focus();
			}else if (!confirm("Send OTP to " + phoneNum.getNumber() + "?")) {
				e.preventDefault();
			} else {
				doOTP();
			}
		});

		jQuery("#resend_otp").click(function(e) {
			if (typeof(Storage) !== "undefined") {
				if (sessionStorage.resent_count){
					sessionStorage.resent_count = Number(sessionStorage.resent_count)+1;
				} else {
					sessionStorage.resent_count = 1;
				}

				if (sessionStorage.resent_count == 3) {
					jQuery("#sms_err").html("Error: Maximum number of retries to send verification SMS exceeded. Please wait for your OTP code.");
					jQuery("#sms_err").show();
					jQuery("#get_otp").hide();
					jQuery("#resend_otp_text").hide();
					jQuery("#resend_otp").hide();
					jQuery("#reset_pn").hide();
				} else {
					if (!confirm("Send OTP to " + phoneNum.getNumber() + "?")) {
						e.preventDefault();
					} else {
						doOTP();
					}
				}
			}
		});

		if (sessionStorage.resent_count >= 3) {
			jQuery("#sms_err").html("Error: Maximum number of retries to send verification SMS exceeded. Please wait for your OTP code.");
			jQuery("#sms_err").show();
			jQuery("#get_otp").hide();
			jQuery("#resend_otp_text").hide();
			jQuery("#resend_otp").hide();
			jQuery("#reset_pn").hide();
		}

		jQuery("#submit_otp").click(function() {
			checkOTP();
		});

		jQuery("#reset_pn").click(function() {
			self.parent.location.reload();
		});

		function doOTP() {
			var data ={
				"tel": phoneNum.getNumber(),
				"tel_cc": phoneNum.getSelectedCountryData().iso2.toUpperCase(),
				"do_otp": true,
			};
			jQuery.ajax({
				type: "POST",
				data: data,
				success: sms_doOTP_success,
				error: sms_doOTP_error,
				dataType: "text"
			});
		}

		function sms_doOTP_success(data) {
			if(data.indexOf("ERROR") == 0) {
				jQuery("#sms_err").html(msg_otp_fail[0] + phoneNum.getNumber() + msg_otp_fail[1]);
				jQuery("#sms_err").show();
			}
			else if(data.indexOf("OK") == 0) {
				jQuery("#sms_tran_id").val(data.substr(2,20));
				jQuery("#get_otp").hide();
				jQuery("#resend_otp_text").css('display', 'inline-block');
				jQuery("#resend_otp").css('display', 'inline-block');
				jQuery("#submit_otp").css('display', 'inline-block');
				jQuery("#sms_otp").show();
				jQuery("#reset_pn").css('display', 'inline-block');
				jQuery("#sms_phone_info").html(msg_otp_success[0] + phoneNum.getNumber() + msg_otp_success[1]);
				jQuery("#sms_phone_info").show();
				jQuery("#sms_info_display").hide();
				jQuery("#enter_phone_number").hide();
				jQuery("#sms_err").hide();
				jQuery("#sms_otp1").val(data.substr(22,6));
				jQuery("#sms_otp1").prop("disabled", true);
			}
		}

		function sms_doOTP_error() {
			jQuery("#sms_err").html(msg_otp_fail[0] + phoneNum.getNumber() + msg_otp_fail[1]);
			jQuery("#sms_err").show();
		}

		function checkOTP() {
			var data ={
				"otp": jQuery("#sms_otp1").val() + "-" + jQuery("#sms_otp2").val(),
				"tran_id": jQuery("#sms_tran_id").val(),
				"tel": phoneNum.getNumber(),
				"check_otp": true,
			};
			jQuery.ajax({
				type: "POST",
				data: data,
				success: sms_checkOTP_success,
				error: sms_checkOTP_error,
				dataType: "text"
			});
		}

		function sms_checkOTP_success(data) {
			if (data.indexOf("ERROR 601") == 0) {
					jQuery("#sms_err").html(msg_invalid_otp);
					jQuery("#sms_err").show();
			}
			else if (data.indexOf("ERROR 600") == 0) {
					jQuery("#sms_err").html("Error: Error while performing verification.");
					jQuery("#sms_err").show();
			}
			else if (data.indexOf("OK") == 0){
				jQuery("#sms_verified").val("YES");
				if(typeof(Storage) !== "undefined") {
					sessionStorage.sms_vrf = "YES";
					sessionStorage.resent_count = 0;
					sessionStorage.order_id = order_id;
				}
				jQuery("#sms_otp").hide();
				jQuery("#submit_otp").hide();
				jQuery("#get_otp").hide();
				jQuery("#resend_otp_text").hide();
				jQuery("#resend_otp").hide();
				jQuery("#reset_pn").hide();
				jQuery("#sms_phone_info").hide();
				jQuery("#sms_box").hide();
				jQuery("#flp_sms").hide();
				jQuery("#sms_success_status").show();
				jQuery("#flp_box_link").hide();
				jQuery("#flp_box_success").show();
			}
		}

		if(sessionStorage.sms_vrf == "YES" && sessionStorage.order_id == order_id) {
			jQuery("#sms_verified").val("YES");
			jQuery("#sms_otp").hide();
			jQuery("#submit_otp").hide();
			jQuery("#get_otp").hide();
			jQuery("#resend_otp_text").hide();
			jQuery("#resend_otp").hide();
			jQuery("#reset_pn").hide();
			jQuery("#sms_phone_info").hide();
			jQuery("#sms_box").hide();
			jQuery("#flp_sms").hide();
			jQuery("#sms_success_status").show();
			jQuery("#flp_box_link").hide();
			jQuery("#flp_box_success").show();
		}

		function sms_checkOTP_error() {
			jQuery("#sms_err").html("Error: Could not perform sms verification.");
			jQuery("#sms_err").show();
		}
	});
</script>