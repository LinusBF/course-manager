/**
 * Created by Linus on 2017-12-09.
 */
jQuery(document).ready(function ($) {

  function createNewStripeSession(courseId, subscribe, callback) {
    let data = {
      'action': 'cm_create_stripe_session',
      'cm_course_id': courseId,
      'cm_subscribe': subscribe,
    };

    jQuery.post(landing_page.ajaxurl, data, function (response) {
      response = JSON.parse(response);
      if(response['status'] === "Success") {
        callback(response["data"]);
      } else{
        console.log("Failed at getting sessionId");
        $('#stripe-error').innerText = response['msg'];
      }
    });
  }

  $('#stripe-button').on('click', function (event) {
    event.preventDefault();

    var courseId = $("#course-id-for-stripe").val();
    var stripePubKey = $("#stripe-public-key").val();
    var subscribe = $("#subscribe").is(':checked');
    createNewStripeSession(courseId, subscribe, function (sessionId) {
      var stripe = Stripe(stripePubKey);
      stripe.redirectToCheckout({
        sessionId: sessionId
      }).then(function (result) {
        if (result.error) {
          $('#stripe-error').innerText = result.error.message;
        } else {
          console.log('Stripe redirect successful');
        }
      });
    });
  });
});