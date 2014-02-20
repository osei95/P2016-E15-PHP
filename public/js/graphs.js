$(document).ready(function(){

  d3.json("datas.json", function(data){
    var width = 680,
    height = 200,
    τ = 2 * Math.PI; // http://tauday.com/tau-manifesto
    var reussite=0;
    var activity=0;
    var objectif;
    var valeur=0;
    var tab=["","%",""];

    var canvas = d3.select("#profil #informations .graphs")
        .append("svg")
        .attr("width", width)
        .attr("height", height);

    var pie=d3.layout.pie().value(function(d){ return d; });

    var arc = d3.svg.arc()
        .innerRadius(57)
        .outerRadius(60)
        .startAngle(0);

    var backgrounds = canvas.append("g").attr("transform", "translate(0,60)").selectAll("path")       
        .data(pie(data.graphs)).enter()
        .append("path").datum({endAngle: τ}).style("fill", "#4c5b6a").attr("d", arc)
        .attr("transform", function(d, i){ return ("translate(" + (i*224 + 112) + ",60)"); });

    var foregrounds = canvas.append("g").attr("transform", "translate(0,60)").selectAll("path")       
        .data(pie(data.graphs)).enter()
        .append("path").datum({endAngle: 0}).style("fill", function(d,i){ return data.graphs[i].couleur; })
        .attr("transform", function(d, i){ return ("translate(" + (i*224 + 112) + ",60)"); })
        .attr("d", arc).transition().duration(1500).attrTween("d", function(d, i) {
          var interpolate = d3.interpolate(d.endAngle, data.graphs[i].valeur/100*τ);
          return function(t) {
            d.endAngle = interpolate(t);
            return arc(d);
          };
        });

    var texts = canvas.append("g").attr("id","text-values").attr("transform", "translate(0,60)").selectAll("text").data(pie(data.graphs)).enter()
        .append("text").text(function(d,i){return (0 + tab[i]);})
        .attr("transform", function(d, i){ return ("translate(" + (i*224 + 112) + ",67)"); })
        .attr("fill","#ffffff").attr("font-size","1.5em").attr("font-family","segoe_uibold").attr("text-anchor","middle").call(varier);

    var textrestant = canvas.append("text").attr("fill","white").attr("font-size",".7em").attr("font-family","segoe_uilight").attr("transform", "translate(25,35)").text("Distance avant prochain niveau: "+data.graphs[0].restant+"km");
        
    function varier(){
      var tab2 = [0,0,0];
      var top = [data.graphs[0].texte, data.graphs[1].texte, data.graphs[2].texte]
      var myinterval0= setInterval(function(){
        if(tab2[0]<top[0]){
            tab2[0] += (top[0]/30);
              $("#text-values>text:nth-child(1)").text(Math.round(tab2[0]*10)/10);
          }else{
              $("#text-values>text:nth-child(1)").text(top[0]);
          }       
      }, 40);
      var myinterval1= setInterval(function(){
        if(tab2[1]<top[1]){
            tab2[1] += (top[1]/30);
              $("#text-values>text:nth-child(2)").text(Math.round(tab2[1])+"%");
          }else{
              $("#text-values>text:nth-child(2)").text(top[1]+"%");
          }       
      }, 40);
       var myinterval2= setInterval(function(){
        if(tab2[2]<top[2]){
            tab2[2] += (top[2]/30);
              $("#text-values>text:nth-child(3)").text(Math.round(tab2[2]*10)/10);
          }else{
              $("#text-values>text:nth-child(3)").text(top[2]);
          }       
      }, 40);
      
    }
    var maxkm = d3.max(data.sport, function(d) { return(d.km); } );
    var echelle;
    if(maxkm<8){
      echelle=1;
    }else if(maxkm<40){
      echelle=5;
    }else if(maxkm<80){
      echelle=10;
    }else if(maxkm<160){
      echelle=20;
    }else{
      echelle=50;
    }
    var maincanvas = d3.select("#activity .graphic")
        .append("svg")
        .attr("height", 460)
        .append('g')
        .attr("transform", "translate(20,120)");

    var yAxisDomain = d3.scale.linear()
      .domain([0, echelle*8])
      .range([320, 0]);

    var yAxis = d3.svg.axis().scale(yAxisDomain).tickFormat(function(d,i) { return i*echelle+" km " }).ticks(10,'Hz').tickSize(1).orient("left");

    maincanvas.append("g")
      .attr("class", "y axis")
      .attr("transform", "translate(" + width/20+","+0+") rotate(0,0,0) ")
      .attr("font-size", '10px')
      .call(yAxis);

    var graphtab = new Array();
    graphtab['x'] = new Array();
    graphtab['y'] = new Array();

    var widthScale = d3.scale.linear().domain([0, 60]).range([0, 500]);

    var generaldatas = maincanvas.append("g").attr("class","generaldatas");

    var generaldata1 = generaldatas.append("g").attr("transform", "translate(100,-50)");
    var generaldata2 = generaldatas.append("g").attr("transform", "translate(480,-50)");
    var generaldata3 = generaldatas.append("g").attr("transform", "translate(860,-50)");
    generaldata1.append("text").attr("fill","#2d3e50").attr("font-size","3em").attr("text-anchor","middle").attr("transform", "translate(0,-20)").attr("font-family","segoe_uibold").text(data.general.distance);
    generaldata2.append("text").attr("fill","#2d3e50").attr("font-size","3em").attr("text-anchor","middle").attr("transform", "translate(0,-20)").attr("font-family","segoe_uibold").text(data.general.calories);
    generaldata3.append("text").attr("fill","#2d3e50").attr("font-size","3em").attr("text-anchor","middle").attr("transform", "translate(0,-20)").attr("font-family","segoe_uibold").text(data.general.temps);
    generaldata1.append("text").attr("fill","#2d3e50").attr("font-size","1em").attr("text-anchor","middle").attr("font-family","segoe_uiregular").text("Total km parcourus");
    generaldata2.append("text").attr("fill","#2d3e50").attr("font-size","1em").attr("text-anchor","middle").attr("font-family","segoe_uiregular").text("Total calories perdues");
    generaldata3.append("text").attr("fill","#2d3e50").attr("font-size","1em").attr("text-anchor","middle").attr("font-family","segoe_uiregular").text("Temps total");
    
    var bars = maincanvas.append("g").attr("class","barres").selectAll("rect");

    var bars2 = bars.data(pie(data.sport)).enter().append('g').attr("class","rects");

      bars2.append("rect")
      .attr("height", function(d){ return((d.data.km)/(echelle*8)*320); })
      .attr("width", 50)
      .attr("x", function(d,i){ return i*60 + 45})
      .attr("y", function(d){ return(320 - (d.data.km)/(echelle*8)*320); })
      .attr("fill","rgb(181, 181, 181)");

      bars2.append("text").text(function(d){ return(d.data.date)})
      .attr("x", function(d,i){ return i*60 + 69})
      .attr("y", 334)
      .attr("text-anchor","middle")
      .attr("fill","#c0c1c1")
      .attr("font-size",".8em").attr("font-family","segoe_uiregular");


      bars.data(pie(data.sport)).enter().append("g").attr("class","circles").append("circle")
        .attr("cx", function(d,i){ graphtab['x'].push(i*60 + 69); return i*60 + 69})
        .attr("cy", function(d){ graphtab['y'].push(320 - (d.data.km)/(echelle*8)*320);return(320 - (d.data.km)/(echelle*8)*320); })
        .attr("fill","#2d3e50")
        .attr("r",4);

      var lines = bars.data(pie(data.sport)).enter().append("g").attr("class","lines");

        lines.append("line")
        .attr("stroke-width",2)
        .attr('x1',function(d,i){ return (graphtab['x'][i])})
        .attr('x2',function(d,i){ return (graphtab['x'][i+1])})
        .attr('y1',function(d,i){ return (graphtab['y'][i])})
        .attr('y2',function(d,i){ return (graphtab['y'][i+1])})
        .attr('stroke', "#2d3e50");
 

    var divcontain = bars.data(pie(data.sport)).enter().append('g')
      .attr("class","infobulle").attr("transform", function(d,i){ return "translate("+(i*60 + 40)+","+(250 - (d.data.km)/(echelle*8)*320)+")"});
      divcontain.append("rect")
        .attr("rx", 6)
        .attr("ry", 6)
        .attr("width", 160)
        .attr("height", 82)
        .attr("fill","#2d3e50")
        .attr("transform", "translate(-50,-20)");
      divcontain.append("text").attr("fill","white").attr("font-size",".9em").attr("text-anchor","middle").attr("font-family","segoe_uiregular").attr("transform", "translate(30,0)").text(function(d){ return d.data.fulldate});
      divcontain.append("text").attr("fill","white").attr("font-size",".7em").attr("text-anchor","middle").attr("font-family","segoe_uilight").attr("transform", "translate(30,20)").text(function(d){ return d.data.calories+" calories perdues"});
      divcontain.append("text").attr("fill","white").attr("font-size",".7em").attr("text-anchor","middle").attr("font-family","segoe_uilight").attr("transform", "translate(30,34)").text(function(d){ return d.data.km+" Km parcourus"});
      divcontain.append("text").attr("fill","white").attr("font-size",".7em").attr("text-anchor","middle").attr("font-family","segoe_uilight").attr("transform", "translate(30,48)").text(function(d){ return d.data.duration});

  });
  $("#activity svg").hover(function(){
      alert('ok');
      console.log($(this).parentNode().index($(this)));
  });
});