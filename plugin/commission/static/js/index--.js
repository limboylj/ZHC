var test = {};
define(['core', 'tpl','countUp'], function(core, tpl, count) {
	var modal = {
		id:0,
		pdata:{},
		profits:0
	};
	modal.init = function(params) {
		modal.setCompoundProfitBox()
		modal.id = params.id;
		modal.pdata = params.pdata;
		modal.profits = params.profits;
		modal.profit();
	};
	modal.setCompoundProfitBox = function(){
		var imgw = 750;
		var imgh = 982;
		var screenH = window.screen.availWidth;

		$(".compound-profit").height(imgh*screenH/imgw);
	}
	modal.profit = function(){
		modal.fixPdata();
		var pc = $('.profit-container');

		var posarr = modal.randPos();
		var tpl = $('.profit-plot').clone().show();
		console.log(modal.pdata);
		pc.html('');
		var j = 0
		var alltpl = [];
		for(var i in modal.pdata){
			var clone = tpl.clone();
			clone.css({left:posarr[j].x,top:posarr[j].y}).attr({'data-id':i,'data-val':modal.pdata[i].dividend,'id':'p'+i});
			clone.find('span').text(modal.pdata[i].dividend);
			alltpl[j] = clone;
			j++;
		}
		var k = 0
		var st = setInterval(function(){
			if(k < j){				
				pc.append(alltpl[k])
			} else {
				clearInterval(st)
				modal.addProfit()
			} 
			k++
		},90);
	}
	modal.randPos = function(){	
		var bm = parseInt(window.screen.availWidth*0.03)	
		var pw = $('.profit-plot').width()+bm;
		var ph = $('.profit-plot').height()+bm;
		var h = $(".compound-profit").height();
		var vh = h*0.7-ph;
		var bh = h*0.15;
		var w = $(".compound-profit").width();
		var vw = w*0.84-pw;
		var bw = w*0.08;
		var cdata = {vh:vh,bh:bh,vw:vw,bw:bw,pw:pw,ph:ph};
		var posArr = [];
		var x = 0;
		var y = 0;

		var j = 0
		for(var i in modal.pdata){
			x = parseInt(Math.random()*vw+bw);
			y = parseInt(Math.random()*vh+bh);			
			if(posArr.length > 1){				
				posArr[j] = modal.checkPos(posArr,x,y,cdata);
				j++
			} else {
				posArr[j] = {x:x,y:y};
				j++
			}	
		}
		return posArr;
	}
	// 检查坐标
	modal.checkPos = function(parr, x, y, cdata){
		var np = {x:x,y:y};
		for(var k in parr){
			var repos = false 
			//console.log(np)
			if(x >= parr[k].x && x < parr[k].x+cdata.pw){				
				if(y >= parr[k].y && y < parr[k].y+cdata.ph){
					repos = true;
				}
				if(y < parr[k].y && y+cdata.ph > parr[k].y){
					repos = true;
				}
				if(repos){
					np = modal.checkPos(parr, parseInt(Math.random()*cdata.vw+cdata.bw), parseInt(Math.random()*cdata.vh+cdata.bh),cdata);
				}				
			}
			if(x < parr[k].x && x+cdata.pw > parr[k].x){
				if(y >= parr[k].y && y < parr[k].y+cdata.ph){
					repos = true;
				}
				if(y < parr[k].y && y+cdata.ph > parr[k].y){
					repos = true;
				}
				if(repos){
					np = modal.checkPos(parr, parseInt(Math.random()*cdata.vw+cdata.bw), parseInt(Math.random()*cdata.vh+cdata.bh),cdata);
				}
			}		
		}
		return np;
	}
	modal.fixPdata = function(){
		for(var i in modal.pdata){
			if(modal.pdata[i].status){
				delete modal.pdata[i];
			}	
		}
	}
	modal.addProfit = function(){
		$('.compound-profit .profit-plot').bind('click',function(){

			var profit = $(this).data('val');
			var id = $(this).data('id')

			core.json('commission/dividendlog/gain', {
				id:modal.id,
				did:id		
			}, function(ret) {		
				if (ret.status != 1) {
					return
				}
				modal.animteProfit(id,profit);
			}, false, true)			
		})
	}
	modal.animteProfit = function(id,add){
		var p = $('#p'+id);
		var vh = window.screen.availHeight;
		var offset = p.offset()

		p.stop().animate({
			top:offset.top+40
		},100).animate({
			top:offset.top-vh
		},1000,'swing',function(){
			p.remove();
		})

		modal.animateCount(add)
		modal.profits += add;
	}
	modal.animateCount = function(add){
		var options = {
            useEasing: false,
            useGrouping: false,
            separator: ',',
            decimal: '.',
            prefix: '',
            suffix: ''
        };
        var fromc = modal.profits;
        var to = modal.profits+add;
        var time = 0.5
        var sumup = new CountUp("compound-profit", fromc, to, 2, time, options);
        sumup.start();
	}
	return modal
});