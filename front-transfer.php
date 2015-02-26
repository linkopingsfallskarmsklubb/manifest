    <div class="container">
      <div class="alert alert-error" style="display: none" id="jumper-not-found">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Fel!</strong> Ingen hoppare matchade sökningen.
      </div>
            
      <div class="alert alert-success" style="display: none" id="transfer-complete">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>OK!</strong> Överföring genomförd.
      </div>
      
      <div class="hero-unit">
        <h2>Överföring</h2>
        <p>
          <form class="form-horizontal" id="from-jumper-form">
            <fieldset>          
              <div id="from-search-box">
                <div class="control-group">
                  <label class="control-label">Från hoppare:</label>
                  <div class="controls">
                    <div class="input-prepend">
                      <span class="add-on"><i class="icon-search"></i></span>
                      <input id="from-search" type="text" placeholder="Hoppare"><br /><br />
                    </div>
                  </div>
                </div>
              </div>
              <div id="from-static-box" style="display: none">
                <input id="from-jumper-id" type="hidden" />
                <div class="control-group">
                  <label class="control-label">Från hoppare:</label>
                  <div class="controls">
                    <span id="from-jumper" class="plookalike"></span>&nbsp;
                  </div>
                </div>
              </div>
            </fieldset>
          </form>
          <form class="form-horizontal" id="to-jumper-form" style="display: none">
            <fieldset>          
              <div id="to-search-box">
                <div class="control-group">
                  <label class="control-label">Till hoppare:</label>
                  <div class="controls">
                    <div class="input-prepend">
                      <span class="add-on"><i class="icon-search"></i></span>
                      <input id="to-search" type="text" placeholder="Hoppare"><br /><br />
                    </div>
                  </div>
                </div>
              </div>
              <div id="to-static-box" style="display: none">
                <input id="to-jumper-id" type="hidden" />
                <div class="control-group">
                  <label class="control-label">Till hoppare:</label>
                  <div class="controls">
                    <span id="to-jumper" class="plookalike"></span>&nbsp;
                  </div>
                </div>
              </div>
            </fieldset>
          </form>
          <form class="form-horizontal" id="transfer-details-form" style="display: none">
            <fieldset>          
              <div id="transfer-details-box">
                <div class="control-group">
                  <label class="control-label">Belopp:</label>
                  <div class="controls">
                    <input type="text" value="" id="amount" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Kommentar:</label>
                  <div class="controls">
                    <input type="text" value="Webböverföring" id="comment" />
                  </div>
                </div>
              </div>
            </fieldset>
          </form>
          <form class="form-horizontal">
            <fieldset>  
              <div class="control-group">
                <div class="controls">
                  <input type="button" class="btn" value="Avbryt" id="btn-cancel" style="display: none" />
                  <input type="button" class="btn btn-primary" value="Överför" id="btn-transfer" style="display: none" />
                </div>
              </div>
              <div id="waiting" style="width: 100%">
                <div style="margin: 0 auto; width: 60px">
                  <div id="waiting-spinner"></div>
                </div>
              </div>
            </fieldset>
          </form>
        </p>
      </div>

      <script>
function transfer(data) {
  console.log("Transfer:");
  console.log(data);
  $.post("transfer.php", data, function(result) {
    result = JSON.parse(result);
    
    $('#waiting-spinner').waiting('disable');
    $('#waiting').slideUp();
    $('#from-jumper-form').slideDown();
    
    console.log("Transfer result:");
    console.log(result);
    if("error" in result) {
      alert("Fel i överföring! Felkod: " + result["error"] + "\n" +
        "Ingen överföring har registrerats. Kontakta manifestet.");
      return
    }
    
    $("#transfer-complete").slideDown();
    setTimeout(function() {
      $("#transfer-complete").slideUp();
    }, 10000);	
  });
  
  $("#options").slideUp();
  search_reset();
  $('#waiting').slideDown();
  $('#waiting-spinner').waiting({ 
    className: 'waiting-circles', 
    elements: 8, 
    radius: 20, 
    auto: true 
  });
}

function jumper_selected(which) {
  $.getJSON('search.php?details&term=' + 
    encodeURIComponent($("#"+which+"-search").val()), function(result) {
    if(result[0] == undefined) {
      $("#jumper-not-found").slideDown();
      setTimeout(function() {
        $("#jumper-not-found").slideUp();
      }, 10000);
      return;
    }
    $("#jumper-not-found").slideUp();
    $("#"+which+"-search").autocomplete("destroy");
    $("#"+which+"-jumper").html(result[0].label);
    $("#"+which+"-jumper-id").val(result[0].value);
    $("#"+which+"-search-box").hide();
    $("#"+which+"-static-box").show();
    if("from" == which) {
      $("#btn-cancel").slideDown();
      $("#to-jumper-form").slideDown();
      $("#to-search").focus();
    } else {
      $("#transfer-details-form").slideDown();
      $("#btn-transfer").slideDown();
      $("#amount").focus();
    }
  });
}

function search_reset_jumper(which) {
  $("#" + which + "-search").autocomplete("destroy");
  $("#" + which + "-search").val("");
  $("#" + which + "-search").autocomplete({
    source: "search.php",
    minLength: 2,
    select: function(event, ui) {
		 $(this).val(ui.item.value);
     $(this).parents("form").submit();
    },
    close: function(event) {
      var ev = event.originalEvent;
      if ( ev.type === "keydown" && ev.keyCode === $.ui.keyCode.ESCAPE ) {
        $( this ).val( "" );
      }
    }
  });
  
  $("#"+which+"-search-box").show();
  $("#"+which+"-static-box").hide();
}

function search_reset() {
  search_reset_jumper("from");
  search_reset_jumper("to");
  
  $("#from-search").focus();
  
  $("#to-jumper-form").hide();
  $("#btn-cancel").hide();
  $("#transfer-details-form").hide();
  $("#btn-transfer").hide();
  
  $("#amount").val("");
  $("#comment").val("Webböverföring");
}

$(function() {   

  search_reset();

  $("#from-jumper-form").submit(function() {
    jumper_selected("from");
    return false;
  });
  
  $("#to-jumper-form").submit(function() {
    jumper_selected("to");
    return false;
  });
  
  $("#transfer-details-form").submit(function() {
      
    n = $("#amount").val()
    if("" == n) {
      alert("Fyll i ett belopp");
      $("#amount").focus();
      return false;
    }

    if(!(parseInt(n, 10) > 0)) {
      alert("Felaktigt belopp");
      $("#amount").focus();
      return false;
    }
    
    data = {
      from: $("#from-jumper-id").val(),
      to: $("#to-jumper-id").val(),
      amount: $("#amount").val(),
      comment: $("#comment").val()
    };
      
    txt = "Från: " + $("#from-jumper").html() + "\n" +
      "Till: " + $("#to-jumper").html() + "\n" +
      "Belopp: " + $("#amount").val() + " kr\n" +
      "Kommentar: " + $("#comment").val() + "\n\n" +
      "Stämmer detta?";
      
    if(confirm(txt)) {
      search_reset();
      $('#from-jumper-form').hide();
      $('#waiting').slideDown();
      $('#waiting-spinner').waiting({ 
        className: 'waiting-circles', 
        elements: 8, 
        radius: 20, 
        auto: true 
      });
      transfer(data);
    }
    return false;
  });


  $("#btn-cancel").click(function() {
    search_reset();
  });
  
  $("#btn-transfer").click(function() {
    $("#transfer-details-form").submit();
  });
  
  $(document).keyup(function(e) {
    if(e.keyCode == 27) {
      search_reset();
    }
  });
  
  $(document).keypress(function(e) {
    if(e.which == 13) {
        if($("#btn-transfer").is(":visible")) {
          $("#btn-transfer").click();
        }
    }
  });
});
      </script>