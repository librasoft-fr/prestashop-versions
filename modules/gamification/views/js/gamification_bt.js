$(document).ready( function () {
  if (typeof admin_gamification_ajax_url !== 'undefined') {
    gamificationTasks();
  }
});

function gamificationTasks()
{
  $.ajax({
    type: 'POST',
    url: admin_gamification_ajax_url,
    dataType: 'json',
    data: {
      controller : 'AdminGamification',
      action : 'gamificationTasks',
      ajax : true,
      id_tab : current_id_tab,
    },
    success: function(jsonData)
    {
      if (jsonData.advices_to_display.advices.length)
      {
        for (var i in jsonData.advices_to_display.advices)
        {
          ok = false;
          selector = jsonData.advices_to_display.advices[i].selector.split(',');
          for (var j in selector)
          {
            if (!ok)
            {
              if (jsonData.advices_to_display.advices[i].location == 'after')
                $(selector[j]).after(jsonData.advices_to_display.advices[i].html);
              else
                $(selector[j]).before(jsonData.advices_to_display.advices[i].html);

              if ($(selector[j]).length)
                ok = true;
            }
          }
        }
        //display close button only for last version of the module
        $('.gamification_close').show();

        $('.gamification_close').on('click', function () {
          if (confirm(hide_advice))
            adviceCloseClick($(this).attr('id'));
          return false;
        });
      }

      if (typeof jsonData.advices_premium_to_display != 'undefined')
      {
        $('#hookDashboardZoneTwo > section:eq(0)').after('<div id="premium_advice_container" class="row"></div>');
        for (var p in jsonData.advices_premium_to_display.advices)
          if (jsonData.advices_premium_to_display.advices[p] != null && typeof jsonData.advices_premium_to_display.advices[p].html != 'undefined')
            $('#premium_advice_container').append(jsonData.advices_premium_to_display.advices[p].html);

        $('.gamification_premium_close').on('click', function () {
          var $adviceContainer = $(this).parent();
          var $btn = $(this);
          $adviceContainer.find('.gamification-close-confirmation').removeClass('hide');
          $adviceContainer.find('button').on('click',function(e){
            e.preventDefault();
            if ($(this).data('advice') == 'cancel' ) {
              $adviceContainer.find('.gamification-close-confirmation').addClass('hide');
            }
            else if ($(this).data('advice') == 'delete' ) {
              adviceCloseClick($btn.attr('id'));
            }
          });
          return false;
        });
      }

      var fancybox = $('.gamification_fancybox');
      if (fancybox.fancybox) {
        fancybox.fancybox();
      }

      $(".preactivationLink").on('click', function(e) {
        e.preventDefault();
        preactivationLinkClick($(this).attr('rel'), $(this).attr('href'));
      });

    }
  });
}

function preactivationLinkClick(module, href) {
  $.ajax({
    url : admin_gamification_ajax_url,
    data : {
      ajax : "1",
      controller : "AdminGamification",
      action : "savePreactivationRequest",
      module : module,
    },
    type: 'POST',
    success : function(jsonData){
      window.location.href = href;
    },
    error : function(jsonData){
      window.location.href = href;
    }
  });
}

function adviceCloseClick(id_advice) {
  $.ajax({
    url : admin_gamification_ajax_url,
    data : {
      ajax : "1",
      controller : "AdminGamification",
      action : "closeAdvice",
      id_advice : id_advice,
    },
    type: 'POST'
  });

  $('#wrap_id_advice_'+id_advice).fadeOut();
  $('#wrap_id_advice_'+id_advice).html('<img src="'+advice_hide_url+id_advice+'.png"/>');
}
