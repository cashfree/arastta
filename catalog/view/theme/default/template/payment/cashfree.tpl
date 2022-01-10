<script>
"use strict";!function(){var e,t;window.Pippin||(t=3e5*Math.ceil(new Date/3e5),(e=document.createElement("script")).type="text/javascript",e.async=!0,e.crossorigin="anonymous",e.src="https://sdk.cashfree.com/js/pippin/1.0.0/pippin.min.js?v="+t,(t=document.getElementsByTagName("script")[0]).parentNode.insertBefore(e,t))}();
</script>
<script>
    var cashfree_data = {
        order_token: "<?php echo $order_token; ?>",
        environment: "<?php echo $environment; ?>",
        return_url: "<?php echo $return_url; ?>"
    };
    function cashfreeSubmit(data){
    console.log(data)
        const return_url = cashfree_data["return_url"];
        const  successCallback  =  function (data) {
            $.post(return_url, data, function(returnUrl, status) {
                $(location).prop('href', returnUrl)
            })
        }
        //Create Failure Callback
        const  failureCallback  =  function (data) {
            $.post(return_url, data, function(returnUrl, status) {
                $(location).prop('href', returnUrl)
            })
        }
        Pippin(cashfree_data["environment"], cashfree_data["order_token"], successCallback, failureCallback);
    }
</script>
<?php if ($testmode) { ?>
<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $text_testmode; ?></div>
<?php } ?>
<div class="buttons">
  <div class="pull-right">
        <input type="submit" onclick="cashfreeSubmit(this);" value="<?php echo $button_confirm; ?>" class="btn btn-primary" />
  </div>
</div>
