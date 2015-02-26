    <div class="container">
      <div class="alert alert-error" style="display: none" id="jumper-not-found">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Fel!</strong> Ingen hoppare matchade sökningen.
      </div>
      
      <div class="alert alert-error" style="display: none" id="jumper-error-exists">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Fel!</strong> Hoppare redan manifesterad.
      </div>
      
      <div class="alert alert-success" style="display: none" id="jumper-added">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>OK!</strong> Hoppare tillagd.
      </div>
      
      <div class="alert alert-success" style="display: none" id="jumper-edited">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>OK!</strong> Hoppare ändrad.
      </div>
      
      <div class="hero-unit">
        <h2>Manifestering</h2>
        <p>
          <form class="form-horizontal" id="search-form">
            <fieldset>          
              <div id="search-box">
                <div class="control-group">
                  <label class="control-label">Sök hoppare:</label>
                  <div class="controls">
                    <div class="input-prepend">
                      <span class="add-on"><i class="icon-search"></i></span>
                      <input id="search" type="text" placeholder="Hoppare"><br /><br />
                    </div>
                  </div>
                </div>
              </div>
              <div id="options" style="display: none">
                <input id="jumper-id" type="hidden" />
                <div class="control-group">
                  <label class="control-label">Hoppare:</label>
                  <div class="controls">
                    <p id="jumper"></p>
                  </div>
                </div>
                <div class="control-group" id="group-control">
                  <label class="control-label">Gruppering:</label>
                  <div class="controls">
                     <select id="group">
                    </select>
                  </div>
                </div>
                <div id="settings-solo">
                  <div id="settings-non-student">
                    <div class="control-group">
                      <label class="control-label">Höjd:</label>
                      <div class="controls">
                        <select id="altitude">
                        </select>
                      </div>
                    </div>
                    <div class="control-group">
                      <label class="control-label">Hopptyp:</label>
                      <div class="controls">
                        <select id="jumptype">
                        </select>
                      </div>
                    </div>
                  </div>
                  <div id="settings-student" style="display: none">
                    <div class="control-group">
                      <label class="control-label">Hopp:</label>
                      <div class="controls">
                        <select id="studentjumpno">
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="control-group">
                    <label class="control-label">Flygplan:</label>
                    <div class="controls">
                      <select id="aircraft">
                      </select>
                    </div>
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Kommentar:</label>
                  <div class="controls">
                    <textarea id="comment"></textarea>
                  </div>
                </div>
                <div class="control-group">
                  <div class="controls">
                    <input type="button" class="btn" value="Avbryt" id="btn-cancel" />
                    <input type="button" class="btn btn-primary" value="Lägg till" id="btn-reserve" />
                    <input type="button" class="btn btn-primary" value="Ändra" id="btn-edit" />
                  </div>
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
	   
      <div class="row">
        <div class="span12">
          <h2>Önskelista</h2>
          <table class="table" id="wishlist">
          </table>
        </div>
      </div>
      <script>
var refreshTimer = 0;
var autoSubmit = false;

function refresh() {
  clearTimeout(refreshTimer);
  refreshTimer = setInterval(function() {
    $.getJSON("wishlist.php", fill_wishlist);
    $.getJSON("time.php", fill_time);
  }, 10000);
  $.getJSON("wishlist.php", fill_wishlist);  
  $.getJSON("time.php", fill_time);
}

function save_wish(edit) {

  if($("#studentjumpno").is(":visible")) {
    var sj = null;
    for(idx in student_jumps) {
      if(student_jumps[idx].jump == $("#studentjumpno").val() &&
          student_jumps[idx].education == $("#studentjumpno").data("education")) {
        sj = student_jumps[idx];
        break;
      }
    }
    data = {
      edit: edit,
      altitude: sj.altitude,
      aircraft: $("#aircraft").val(),
      jumper: $("#jumper-id").val(),
      jumptype: sj.jumptype,
      student: $("#studentjumpno").val(),
      group: 0,
      comment: $("#comment").val()
    };
  } else {
    data = {
      edit: edit,
      altitude: $("#altitude").val(),
      aircraft: $("#aircraft").val(),
      jumper: $("#jumper-id").val(),
      jumptype: $("#jumptype").val(),
      group: $("#group").val(),
      comment: $("#comment").val()
    };
  }
  $.post("book.php", data, function(result) {
    result = JSON.parse(result);
    
    $('#waiting-spinner').waiting('disable');
    $('#waiting').slideUp();
    
    if("error" in result) {
      $("#jumper-error-" + result.error).slideDown();
      setTimeout(function() {
        $("#jumper-error-" + result.error).slideUp();
      }, 10000);		
    } else {
      refresh();
      if(!edit) {
        $("#jumper-added").slideDown();
        setTimeout(function() {
          $("#jumper-added").slideUp();
        }, 3000);		
      } else {
        $("#jumper-edited").slideDown();
        setTimeout(function() {
          $("#jumper-edited").slideUp();
        }, 3000);	
      }
    }
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

$(function() {
  refresh();
  
  load_options();
  search_reset();
  
  $("#group").change(function() {
    if($(this).val() == 0) {
      $("#settings-solo").slideDown();
    } else {
      $("#settings-solo").slideUp();
    }
  });

  $("#search-form").submit(function() {
    if($("#btn-reserve").is(":visible")) {
      return false;
    }
    // Refresh options
    load_options();
    $('#waiting').slideDown();
    $('#waiting-spinner').waiting({ 
      className: 'waiting-circles', 
      elements: 8, 
      radius: 20, 
      auto: true 
    });
    $.getJSON('search.php?details&term=' + 
      encodeURIComponent($("#search").val()), function(result) {
      $('#waiting-spinner').waiting('disable');
      $('#waiting').slideUp();
      if(result[0] == undefined) {
        $("#jumper-not-found").slideDown();
        setTimeout(function() {
          $("#jumper-not-found").slideUp();
        }, 10000);
        return;
      }
      $("#jumper-not-found").slideUp();
      $("#search").autocomplete("destroy");
      $("#jumper").html(result[0].label);
      $("#jumper-id").val(result[0].value);
      load_groups();
      $("#search-box").slideUp();
      $("#options").slideDown();
      
      jumper = result[0];
      if(jumper.student) {
        $("#group-control").hide();
        $("#settings-student").show();
        $("#settings-non-student").hide();
        $("#studentjumpno").val(jumper.student.nextjump);
        
        $("#studentjumpno").data("education", jumper.student.education);
        var html = "";
        for(idx in student_jumps) {
          var j = student_jumps[idx];
          if(j.education != jumper.student.education)
            continue;
          if(j.jump == jumper.student.nextjump) {
            html += "<option selected='1' value='" + j.jump + "'>" + j.jump + " - " + j.program + "</option>";
          } else {
            html += "<option value='" + j.jump + "'>" + j.jump + " - " + j.program + "</option>";
          }
        }
        $("#studentjumpno").html(html);
      } else {
        $("#group-control").show();
        $("#settings-non-student").show();
        $("#settings-student").hide();
      }


      if(autoSubmit) {
        autoSubmit = false;
        save_wish(false);
      }
    });
    return false;
  });


  $("#btn-cancel").click(function() {
    search_reset();
  });
  
  $("#btn-edit").click(function() {
    save_wish(true);
  });
  
  $("#btn-reserve").click(function() {
    save_wish(false);
  });
  
  $(document).keyup(function(e) {
    if(e.keyCode == 27) {
      search_reset();
    }
  });
  $(document).keypress(function(e) {
      console.log(e);
    if(e.which == 55 && e.altKey) {
      $("#search").val("1010524");
      autoSubmit = true;
      $("#search-form").submit();
    }
    if(e.which == 51 && e.altKey) {
      $("#search").val("1020990");
      autoSubmit = true;
      $("#search-form").submit();
    }
    if(e.which == 167 && e.altKey) {
      $("#search").val("1021572");
      autoSubmit = true;
      $("#search-form").submit();
    }
   if(e.which == 48 && e.altKey) {
      $("#search").val("23921");
      autoSubmit = true;
      $("#search-form").submit();
    }
    if(e.which == 13) {
        if($("#btn-reserve").is(":visible")) {
          $("#btn-reserve").click();
        } else if($("#btn-edit").is(":visible")) {
          $("#btn-edit").click();
        }
    }
  });

<?php
  if(isset($_GET['book'])) {
?>
      $("#search").val("<?php echo $_GET['book'];?>");
      autoSubmit = true;
      $("#search-form").submit();
<?php
  }
?>
});
      </script>