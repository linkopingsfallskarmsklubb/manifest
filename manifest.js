var groups = {};
var jumpers = {};
var student_jumps = {};

function fill_wishlist(result) {
  var html = "<tr><th style=\"width: 30px;\">&nbsp;</th>";
  html += "<th style=\"width: 20px;\">&nbsp;</th>";
  html += "<th>Namn</th><th>Hopptyp</th>";
  html += "<th>Flygplan</th><th>Väntat</th></tr>";

  jumpers = result;
  
  /* combine groups */
  groups = {}
  for(idx in result) {
    var jumper = result[idx];
    if(jumper.group in groups) {
      groups[jumper.group].push(idx);
    } else {
      groups[jumper.group] = [idx];
    }
  }
  
  /* add jumpers grouped together by the first jumper.
     keep track of which jumpers have been added to prevent duplicates. */
  var added = {};
  var last_color = 1;
  var max_color = 0;
  
  /* count number of colors to use */
  for(gidx in groups) {
    if(groups[gidx].length > 1)
      max_color++;
  }
  
  for(idx in result) {
    var jumper = result[idx];
    if(idx in added)
      continue;
    
    var group = groups[jumper.group];
    var group_color = 0;
    if(group.length > 1)
      group_color = last_color++;
   
    var hue = 360/max_color * (group_color-1);   
    
    for(j in group) {
      var jid = group[j];
      var jumper = result[jid];
      if(jid in added)
        continue;
        
      added[jid] = true;
      
      html += "<tr>";
      html += "<td>";
      
      if($("#options").length > 0) {
        html += "<a data-id='" + jid + "' class='edit' href='#edit' alt='Ändra reservation'>";
        html += "<i class='icon-edit'></i></a>";
        html += "<a data-id='" + jid + "' class='remove' href='#remove' alt='Ta bort reservation'>";
        html += "<i class='icon-remove'></i></a>";
      } else {
        html += "&nbsp;";
      }
      
      html += "</td>";
      if(group_color > 0)
        html += "<td style=\"background-color: hsl(" + hue + ", 100%, 75%);\">&nbsp;</td>";
      else
        html += "<td>&nbsp;</td>";
      html += "<td>" + jumper.jumper + "</td>";
      html += "<td>" + jumper.jumptype + " " 
        + jumper.altitude + " m</td>";
      html += "<td>" + jumper.aircraft + "</td>";
      html += "<td>" + format_time(jumper.waited) + "</td>";
      html += "</tr>";
    }
  }

  $("#wishlist").html(html);
  
  $(".remove").click(function() {
    jumper = jumpers[$(this).data("id")];
    if(confirm("Är du säker på att du vill ta bort '" + jumper.jumper + "'?")) {
      search_reset();
      $.getJSON("book.php?remove=" + jumper.internal, function(result) {
        refresh();
      });
    }
  });
  
  $(".edit").click(function() {
    jumper = jumpers[$(this).data("id")];
    $("#jumper-not-found").slideUp();
    $("#search").autocomplete("destroy");
    $("#jumper").html(jumper.jumper);
    $("#jumper-id").val(jumper.internal);
    load_groups();
    $("#search-box").slideUp();
    $("#options").slideDown();
    
    $("#altitude").val(jumper.altitude);
    $("#jumptype").val(jumper.jumptype_internal);
    $("#aircraft").val(jumper.aircraft);
    $("#comment").val(jumper.comment);
    $("#group").val(jumper.group);
    $("#settings-solo").slideDown();
    $("#btn-edit").show();
    $("#btn-reserve").hide();
    
    if(jumper.student) {
      $("#group-control").hide();
      $("#settings-student").show();
      $("#settings-non-student").hide();
      $("#studentjumpno").val(jumper.student.jump);
      $("#studentjumpno").data("education", jumper.student.education);
      
      var html = "";
      for(idx in student_jumps) {
        var j = student_jumps[idx];
        if(j.education != jumper.student.education)
          continue;
        if(j.jump == jumper.student.jump) {
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
  });
}

function fill_time(result) {
  var time = new Date(result.time*1000);

  var hours = time.getHours();
  var minutes = time.getMinutes();
  if(hours < 10)
    hours = "0" + hours;

  if(minutes < 10)
    minutes = "0" + minutes;

  $("#time").html(hours + ":" + minutes);
}

function fill_load(result) {
  for(loadno = 0; loadno < 3; loadno++) {
    if($("#load-" + loadno).length == 0)
      break;
    if(!(loadno in result)) {
      /* clear this box */
      $("#load-" + loadno + "-aircraft").html("");
      $("#load-" + loadno + "-loadno").html("");
      $("#load-" + loadno + "-15min").slideUp();
      $("#load-" + loadno + "-30min").slideUp();
      $("#load-" + loadno).html("");
    }
  }
  for(loadno in result) {
    load = result[loadno];
    
    if($("#load-" + loadno).length == 0)
      break;
      
    $("#load-" + loadno + "-aircraft").html(load.aircraft);
    $("#load-" + loadno + "-loadno").html("(#" + load.load + ")");
    
    if(load.call["15"] == "Y") {
      $("#load-" + loadno + "-15min").slideDown();
      $("#load-" + loadno + "-30min").slideUp();
    } else if(load.call["30"] == "Y") {
      $("#load-" + loadno + "-15min").slideUp();
      $("#load-" + loadno + "-30min").slideDown();
    } else {
      $("#load-" + loadno + "-15min").slideUp();
      $("#load-" + loadno + "-30min").slideUp();
    }
    
    var html = "<tr><th style=\"width: 20px;\">&nbsp;</th>";
    html += "<th style=\"width: 20px;\">&nbsp;</th>";
    html += "<th>Namn</th><th>Höjd</th></tr>";
    
    /* combine groups */
    var load_groups = {}
    for(idx in load.jumpers) {
      var jumper = load.jumpers[idx];
      if(jumper.group in load_groups) {
        load_groups[jumper.group].push(idx);
      } else {
        load_groups[jumper.group] = [idx];
      }
    }    
    
    /* add jumpers grouped together by the first jumper.
       keep track of which jumpers have been added to prevent duplicates. */
    var added = {};
    var last_color = 1;
    var max_color = 0;
    
    /* count number of colors to use */
    for(gidx in load_groups) {
      if(gidx == null || gidx == 0)
        continue;
      if(load_groups[gidx].length > 1)
        max_color++;
    }
    
    for(idx in load.jumpers) {
      var jumper = load.jumpers[idx];
      if(idx in added)
        continue;
      
      var group = load_groups[jumper.group];
      if(jumper.group == null)
        group = [idx];
      
      var group_color = 0;
      if(group.length > 1)
        group_color = last_color++;
        
      var hue = 360/max_color * (group_color-1);
        
      for(j in group) {
        var jumper = load.jumpers[group[j]];
        if(group[j] in added)
          continue;
        
        added[group[j]] = true;
      
        html += "<tr>";
        if(group_color > 0)
          html += "<td style=\"background-color: hsl(" + hue + ", 100%, 75%);\">&nbsp;</td>";
        else
          html += "<td>&nbsp;</td>";
        html += "<td>";
		if(jumper.student != undefined) {
			html += jumper.student.jump;
		}
        if($.inArray("LOADMASTER", jumper.roles) >= 0)
          html += "HM";
        else
          html += "&nbsp;";
        html += "</td>";
        html += "<td>" + jumper.jumper + "</td>";
        html += "<td>" + jumper.altitude + "</td>";
      }
    }
    
    $("#load-" + loadno).html(html);
  }
}

function format_time(t) {
  var str = ""
  if(t > 60*60) {
    var h = Math.floor(t/60/60);
    if(h > 1)
      str = h + " timmar, ";
    else
      str = "1 timme, ";
    t = t - h*60*60;
  }
  
  var m = Math.floor(t/60);
  str = str + m + " minuter";
  return str;
}

function load_groups() {
  var html = "";
  html += "<option selected='1' value='0'>Solo</option>";
  for(gidx in groups) {
    jumper = jumpers[groups[gidx][0]];
    html += "<option value='" + gidx + "'>" + jumper.jumper + "</option>";
  }
  $("#group").html(html);
}
  
function load_options() {
  $.getJSON('aircrafts.php', function(result) {
    var html = "";
    for(idx in result) {
      ac = result[idx];
      html += "<option value='" + ac.aircraft + "'>" + ac.aircraft + "</option>";
    }
    $("#aircraft").html(html);
  });
  $.getJSON('altitudes.php', function(result) {
    var html = "";
    for(idx in result) {
      var alt = result[idx];
      // TODO: Use unit instead of hard coded meter
      html += "<option value='" + alt + "'>" + alt + " m</option>";
    }
    $("#altitude").html(html);
  });
  $.getJSON('jumptypes.php', function(result) {
    var html = "";
    for(idx in result) {
      var jt = result[idx];
      if(jt.jumptype == 'O') {
        html += "<option selected='1' value='" + jt.jumptype + "'>" + jt.label + "</option>";
      } else {
        html += "<option value='" + jt.jumptype + "'>" + jt.label + "</option>";
      }
    }
    $("#jumptype").html(html);
  });
  $.getJSON('studentjumps.php', function(result) {
    student_jumps = result;
  });
  
  $("#comment").val("");
}

function search_reset() {
  $("#btn-edit").hide();
  $("#btn-reserve").show();
  $("#jumper").html("");
  $("#search-box").slideDown();
  $("#options").slideUp();
  $("#settings-solo").slideDown();

  $("#search").autocomplete("destroy");
  $("#search").val("");
  $("#search").autocomplete({
    source: "search.php",
    minLength: 2,
    select: function(event, ui) {
		 $(this).val(ui.item.value);
		 $(this).parents("form").submit();  // this will submit the form.
    },
    close: function(event) {
      var ev = event.originalEvent;
      if ( ev.type === "keydown" && ev.keyCode === $.ui.keyCode.ESCAPE ) {
        $( this ).val( "" );
      }
    }
  });

  $("#search").focus();
}
