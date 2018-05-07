define(['core', 'tpl','countUp'], function(core, tpl, count) {
	var modal = {
		id:0,
		pdata:{},
		profits:0,
		backdata:{},
		times:0,
		showfloat:2
	};
	modal.init = function(params) {
		modal.id = params.id;
		modal.pdata = params.pdata;
		modal.profits = params.profits;
		modal.backdata = params.backData;
		modal.credit8 = params.credit8;
		modal.showfloat = params.showfloat;
		modal.animateBackMoney();
		modal.profit();
		modal.initLogout();
		
	};
	modal.profit = function(){
		var pc = $('.profit-container');

		var posarr = modal.randPos();
		var tpl = $('.profit-plot').clone().show();

		pc.html('');
		var j = 0
		var alltpl = [];

		var ul_height =  $(".compound-profit").height();
		var extra_height = ul_height*0.3;
		var vertical_base_height = ul_height*0.95;

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
		var pw = $('.profit-plot').width();
		var ph = $('.profit-plot').height();
		var w = $(".compound-profit").width();
		var bw = w*0.08;
		var vw = w-bw*2-pw;
		var h = $(".compound-profit").height();
		var bh = h*0.15;
		var vh = h-bh*2-ph;
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
	modal.addProfit = function(){
		$('.compound-profit .profit-plot').bind('click',function(){
			if($(this).attr('stop')==1){
				return ;
			}
			var profit = $(this).data('val');
			var id = $(this).data('id')
			$(this).attr('stop',1); 
			core.json('commission/dividendlog/gain', {
				id:modal.id,
				did:id		
			}, function(ret) {
		console.log(ret);
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
        var sumup = new CountUp("compound-profit", fromc, to, modal.showfloat, time, options);
        sumup.start();
	}
	modal.initLogout = function() {
		$(".btn-logout").unbind('click').click(function() {
			FoxUI.confirm('当前已登录，确定要退出？', function() {
				location.href = core.getUrl('account/logout')
			})
		})
	};

	modal.animateBackMoney = function(){
		if(modal.backdata.length > 0){
			modal.backdata[0][0] = parseFloat(modal.backdata[0][0]);
			var options = {
	            useEasing: false,
	            useGrouping: false,
	            separator: ',',
	            decimal: '.',
	            prefix: '',
	            suffix: ''
	        };
	        var fromc = modal.credit8;
	        modal.credit8 += modal.backdata[0][0]*modal.backdata[0][1];
	        var to = modal.credit8;
	        var sumup = new CountUp("backcoin", fromc, to, 6, modal.backdata[0][0], options);
	        sumup.start();
	        if(modal.backdata.length>1){
	        	setTimeout(function(){
					modal.animateBackMoney();
	        	},modal.backdata[0][0]*1000);

	        	modal.backdata.shift();
	        } 			
		}
	}
	//
	//
	
	$("#withdrawamount").unbind("click").click(function(){
		var nums=$(this).data("num");
		FoxUI.alert(nums);
	})
	
	return modal
});