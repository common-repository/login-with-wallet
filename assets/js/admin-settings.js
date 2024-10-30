jQuery(document).ready(function($) {
  $(document).on('submit', '#login-with-wallet-admin-form', function (e) {
       e.preventDefault();
       // We inject some extra fields required for the security
       $('#login-with-wallet-security').val(login_with_wallet.nonce);
       // We make our call
       $.ajax({
           url: login_with_wallet.ajax_url,
           type: 'post',
           data: $(this).serialize(),
           beforeSend:function() {
             $('.login-with-wallet-settings-notification').html('Saving ...').addClass('loading');
           },
           success: function(response) {
              $('.login-with-wallet-settings-notification').html(response.data.message).removeClass('loading').addClass('show');
           }
       });

   });

   $('.login_with_wallet_wallet_types').click(function(e) {
     var t = $(this);
     if(t.is(':checked')) {
       if(t.val() == 'walletconnect'){
         $('#login_with_wallet_infura_project_id').addClass('show');
       }
     } else {
       if(t.val() == 'walletconnect') {
         $('#login_with_wallet_infura_project_id').removeClass('show');
       }
     }
     $(this).toggleClass('opened');
   });

   $('.login-with-wallet-hint-toggler').click(function(e) {
     $(this).parent().find('.login-with-wallet-hint-toggle-wrapper').toggle(500);
     $(this).toggleClass('opened');
   });
});
