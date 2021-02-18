(function ($) {
  Drupal.behaviors.guidelineBehavior = {
    attach: function (context, settings) {
      $('.guide').stop().animate({'margin-right':'-430px'},1000);
      $("body").css("overflow-x", "hidden");

      function toggleDivs() {
        var $inner = $(".guide");
        if ($inner.css("margin-right") === "-430px") {
            $inner.animate({'margin-right': '0'});
        }
        else {
            $inner.animate({'margin-right': "-430px"});
        }
      }

      $(".guideButton").bind("click", function(){
        toggleDivs();
      });
    }
  };
})(jQuery);
