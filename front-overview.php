      <div class="row">
        <div id="slides" style="display: none">
          <div id="slide1">
            <div class="span6">
              <h3>Vindhastighet</h3>
              <p id="no-weather-data">Ingen väderdata</p>
              <div id="windgraph" style="width: 100%;"></div>
            </div>
            <div class="span6">
              <h3>&nbsp;</h3>
              <div id="windgauge_now"></div>
              <div id="windgauge" style="padding-left: 50px"></div>
            </div>
          </div>
          <div id="slide1">
            <div class="span6">
              <h3>Vindriktning</h3>
              <div id="winddirection" style="width: 570px; height: 280px; margin-top: 20px; background-image: url('img/skarstad.jpg'); background-position: center;">
                <canvas id="wdcanvas" width="570" height="280" />
              </div>
            </div>
            <div class="span6">
              <h3>&nbsp;</h3>
              <table class="table" id="wdtable"></table>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="span6">
          <h3>Nästa lift <span id="load-0-loadno"></span></h3>
          <p>
          Flygplan: <span id="load-0-aircraft"></span>
          <span id="load-0-15min" style="display: none" class="label label-important">15 minuter call</span>
          <span id="load-0-30min" style="display: none" class="label label-warning">30 minuter call</span></p>
          <table class="table" id="load-0">
          </table>
        </div>
        <div class="span6">
          <h3>Liften därpå <span id="load-1-loadno"></span></h3>
          <p>
          Flygplan: <span id="load-1-aircraft"></span>
          <span id="load-1-15min" style="display: none" class="label label-important">15 minuter call</span>
          <span id="load-1-30min" style="display: none" class="label label-warning">30 minuter call</span></p>
          <table class="table" id="load-1">
          </table>
       </div>
      </div>
      <script>
var refreshTimer = 0;
var wd_ctx = null;
var altcolors = {
  "3000": '#6fc6f2',
  "1500": '#1b8ee0',
  "600": '#1270b3',
  "0": '#ff4545'
};

var wind_data = {
  "3000": 0,
  "1500": 0,
  "600": 0,
  "0": 0
};

function refresh() {
  clearTimeout(refreshTimer);
  refreshTimer = setInterval(function() {
    $.getJSON("load.php", fill_load);
    $.getJSON("time.php", fill_time);
  }, 10000);
  $.getJSON("load.php", fill_load);
  $.getJSON("time.php", fill_time);
}

google.load('visualization', '1', {packages:['gauge', 'corechart']});
google.setOnLoadCallback(drawChart);

function drawChart() {

  var windgauge_now_options = {
          width: 200*2, height: 200,
          redFrom: 11, redTo: 12,
          yellowFrom:8, yellowTo: 11,
          minorTicks: 1, max: 12,
          majorTicks: ['0','','','','4','','6','','8', '', '', '11', '12']
        };
        
  var windgauge_options = {
          width: 150*2, height: 150,
          redFrom: 11, redTo: 12,
          yellowFrom:8, yellowTo: 11,
          minorTicks: 1, max: 12,
          majorTicks: ['0','','','','4','','6','','8', '', '', '11', '12']
        };
        
  windgauge = new google.visualization.Gauge(
    document.getElementById('windgauge'));
  windgauge_now = new google.visualization.Gauge(
    document.getElementById('windgauge_now'));
    
  windgraph = new google.visualization.LineChart(
    document.getElementById('windgraph'));

  
  weather_func = function() {
    $.getJSON("weather.php", function(wdata) {
      update_wdir(wdata);
      weather = wdata.station;
      
      var data = new google.visualization.DataTable();
      data.addColumn('datetime', 'Tid');
      data.addColumn('number', 'Medel');
      data.addColumn('number', 'Max');
        
      limit = new Date().getTime() / 1000 - 3600;
      max = 0.0;
      mean = 0.0;
      nmean = 0;
      for(idx in weather) {
        row = weather[idx];
        if(limit > row.time)
          continue;
        
        lmax = parseFloat(row.wind.max);
        lmean = parseFloat(row.wind.mean);
        data.addRow([new Date(row.time*1000), lmean, lmax]);
        if(lmax > max)
          max = lmax;
          
        mean += lmean;
        nmean++;
      }
      /* do not paint empty data */
      if(nmean == 0)
        return;

      graphMax = max + 1.0;
      if(graphMax < 5.0) {
        graphMax = 5.0;
      }
        
      $("#no-weather-data").slideUp();
      mean = mean / nmean;
      
      var formatter_short = new google.visualization.DateFormat(
        {pattern: 'HH:mm'});
      formatter_short.format(data, 0);
      
      var windgraph_options = {curveType: "function",
          height: 350, 
          vAxis: {format: '#', gridlines: {count: 6}, 
                  viewWindowMode:'explicit', 
                  viewWindow: {min: 0, max: graphMax}},
          hAxis: {format: 'HH:mm'}
        };

      windgraph.draw(data, windgraph_options);
      
      var data = google.visualization.arrayToDataTable([
        ['Label', 'Value'],
        ['Tim-medel', Math.round(mean*10)/10],
        ['Tim-max', Math.round(max*10)/10],
      ]);
      windgauge.draw(data, windgauge_options);
      
      var data = google.visualization.arrayToDataTable([
        ['Label', 'Value'],
        ['Medel', Math.round(lmean*10)/10],
        ['Max', Math.round(lmax*10)/10],
      ]);
      windgauge_now.draw(data, windgauge_now_options);
    });
  };
  setInterval(weather_func, 10000);
  weather_func();
}

function update_wdir(weather) {

  var html = "<tr><th style=\"width: 30px;\">&nbsp;</th>";
  html += "<th>Höjd</th><th>Riktning</th>";
  html += "<th>Hastighet</th><th>Temperatur</th>";
  html += "</tr>";
  
  latest = 0;
  latest_idx = 0;
  for(idx in weather.station) {
    if(weather.station[idx].time > latest) {
      latest = weather.station[idx].time;
      latest_idx = idx;
    }
  }
  
  gnd_speed = weather.station[idx].wind.mean;
  gnd_temp = weather.station[idx].temperature;
  gnd_dir = weather.station[idx].wind.direction;
  
  altitudes = [];
  for(altitude in weather.lfv) {
    altitudes.push(parseInt(altitude));
  }
  altitudes.sort(function(a,b){return b - a});
  
  for(idx in altitudes) {
    altitude = altitudes[idx];
    
    html += "<tr>";
    html += "<td style='background-color: " + altcolors[altitude] + "'>&nbsp;</td>";
    html += "<td>" + altitude + "</td>";
    html += "<td>" + weather.lfv[altitude].direction + " °</td>";
    html += "<td>" + weather.lfv[altitude].speed + " m/s</td>";
    html += "<td>" + weather.lfv[altitude].temperature + " °C</td></tr>";
    wind_data[altitude] = weather.lfv[altitude].direction;
  }
  
  html += "<tr>";
  html += "<td style='background-color: " + altcolors[0] + "'>&nbsp;</td>";
  html += "<td>Mark</td>";
  html += "<td>" + gnd_dir + " °</td>";
  html += "<td>" + gnd_speed + " m/s</td>";
  html += "<td>" + gnd_temp + " °C</td></tr>";
  wind_data[0] = gnd_dir;
  
  $("#wdtable").html(html);
}

function draw_needle(x, y, color, dir) {

  wd_ctx.save();
  //wd_ctx.translate(x, y);
  wd_ctx.translate(215, 120);
  wd_ctx.rotate(dir + Math.PI);
  for(i = 0; i < 2; i++) {
    wd_ctx.beginPath();
    wd_ctx.moveTo(0, 10);
    wd_ctx.lineTo(0, 110);
    wd_ctx.moveTo(0, 10);
    wd_ctx.lineTo(-5, 15);
    wd_ctx.moveTo(0, 10);
    wd_ctx.lineTo(5, 15);
    wd_ctx.lineWidth = 4-i;
    if(i == 0)
      wd_ctx.strokeStyle = 'black';
    else
      wd_ctx.strokeStyle = color;
    wd_ctx.stroke();
  }
  wd_ctx.restore();
}
 
function draw_wd() {
  wd_ctx.clearRect(0, 0, 570, 290);
  
  wd_ctx.save();
  wd_ctx.translate(70, 20);
  
  draw_needle(50, 50, '#6fc6f2', Math.PI/180 * wind_data[3000]);
  draw_needle(150, 100, '#1b8ee0', Math.PI/180 * wind_data[1500]);
  draw_needle(250, 150, '#1270b3', Math.PI/180 * wind_data[600]);
  draw_needle(350, 200, '#ff4545', Math.PI/180 * wind_data[0]);
  
  wd_ctx.restore();
}

$(function() {
  $('#slides').slidesjs({
    width: 940,
    height: 290,
    pagination: false,
    generatePagination: false,
    play: {
      active: true,
      auto: true,
      interval: 20000,
      swap: true
    },
    callback: {
      loaded: function(){
        $('.slidesjs-navigation').hide(0);
      }
    }
  });
  refresh();
  
  wd_ctx = document.getElementById('wdcanvas').getContext('2d');
  setInterval(draw_wd, 10000);
});
      </script>