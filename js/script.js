HTTP_GET_VARS=new Array();
strGET=document.location.search.substr(1,document.location.search.length);
var data = null;
var user = {
	token: ''
};
if(strGET!='')
{
    gArr=strGET.split('&');
    for(i=0;i<gArr.length;++i)
    {
        v='';vArr=gArr[i].split('=');
        if(vArr.length>1){v=vArr[1];}
        HTTP_GET_VARS[unescape(vArr[0])]=unescape(v);
    }
}
function isMobile()
{
	width = $( window ).width();
	if(width < 480)
	{
		return true;
	}
	return false;
}

function GET(v)
{
	if(!HTTP_GET_VARS[v]){return 'undefined';}
	return HTTP_GET_VARS[v];
}

function collapse_wrapper(id)
{
	var $content = $('#' + id + '-wrapper .element-wrapper');
	var $label = $('#' + id + '-wrapper .wrapper-label i');
	if($content.is(':visible'))
	{
		$content.hide();
		$label.removeClass('fa-caret-down').addClass('fa-caret-right');
	}
	else
	{
		$content.show();
		$label.removeClass('fa-caret-right').addClass('fa-caret-down');
	}
}

function closeAllDialogs()
{
	$(".xhrDialog").dialog("close");
	$(".xhrDialog").dialog("destroy");
	$(".xhrDialog").remove();
	
	/*
    var $activeDialogs = $(".ui-dialog").find('.ui-dialog-content');
    
    $activeDialogs.each(function(){
    	$dia = $(this);
    	if(typeof $dia.dialog === 'function') {
    		if($dia.dialog("isOpen"))
            {
    			$dia.dialog('close');
            }
    		
    	}
    });
    */
}

$(document).ready(function(){
	$('textarea.comment').autosize();
	 $('#nojs').css('display','none');
	 $('#main').css('display','block');
	 
	 $('.moreswap').each(function(){
		 $this = $(this);
		 $this.after('<a class="moreswaplink" href="#" data-show="0">Mehr anzeigen</a>');
		 if($this.height() > 100)
		 {
			 $this.css({
				 'height':'100px',
				 'overflow':'hidden'
			 });
		 }
	 });
	 
	 $('.moreswaplink').each(function(){
		 $this = $(this);
		 $this.click(function(ev){
			 ev.preventDefault();
			 if($this.attr('data-show') == 0)
			 {
				 $this.prev().css({
					 'height':'auto',
					 'overflow':'visible'
				 });
				 $this.text('einklappen');
				 $this.attr('data-show',1);
			 }
			 else
		     {
				 $this.prev().css({
					 'height':'100px',
					 'overflow':'hidden'
				 });
				 $this.text('Mehr anzeigen');
				 $this.attr('data-show',0);
		     }
		 });
	 });
	 
	 if(isMob())
	 {
		 $("#mobilemenu, .v-mob").show();
		 $("#mainMenu, .v-desktop").hide();
	 }
	 else
	 {
		 $("#mainMenu, .v-desktop").show();
		 $("#mobilemenu, .v-mob").hide();
	 }
	 $(window).resize(function(){
		 if(isMob())
		 {
			 $("#mobilemenu, .v-mob").show();
			 $("#mainMenu, .v-desktop").hide();
		 }
		 else
		 {
			 $("#mainMenu, .v-desktop").show();
			 $("#mobilemenu, .v-mob").hide();
		 }
	 });
	 
	 $('textarea.inlabel, input.inlabel').each(function() {
	        var $this = $(this);
	        if($this.val() === '') {
	           $this.val($this.attr('title'));
	        }
	        $this.focus(function() {
	          if($this.val() === $this.attr('title')) {
	            $this.val('');
	          }
	        });
	        $this.blur(function() {
	        if($this.val() === '') {
	           $this.val($this.attr('title'));
	        }
	    });
	 });

	 infoMenu();	 
	 
	 $('#main a').tooltip({
			show:false,
			hide:false,
			content: function() {
				var el = $( this );
				if(el.attr('title').substring(0,4) == '#tt-')
				{
					id = el.attr('title').substring(4);
					return $('.' + id).html();
				}
				else
				{
					return el.attr('title');
				}
			},
			position: {
				my: "center bottom-20",
				at: "center top",
				using: function( position, feedback ) {
				$( this ).css( position );
				$( "<div>" )
					.addClass( "arrow" )
					.addClass( feedback.vertical )
					.addClass( feedback.horizontal )
					.appendTo( this );
				}
			}
		});
	 
	 //$('.select').customSelect();
	 
	 $(function() {
			$( "#dialog-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				autoOpen:false,
				buttons: {
					"unwideruflich löschen": function() {
						goTo($('#dialog-confirm-url').val());
						$( this ).dialog( "close" );
					},
					'Abbrechen': function() {
						$( this ).dialog( "close" );
					}
				}
			});
		});
	//$('.button').button();
	$('.dialog').dialog();
	$('.v-switch').buttonset();
	
	$('#topmenu').buttonset();
	$('#topmenu').first().click();
	
	addHover('#mainMenu li');
	
	$( "ul.toolbar li" ).hover(
			function() {
				$( this ).addClass( "ui-state-hover" );
			},
			function() {
				$( this ).removeClass( "ui-state-hover" );
			}
	);
	
	$( ".text, .textarea, select" ).focus(
			function() {
				$( this ).addClass( "focus" );
			}
	);
	$( ".text, .textarea, select" ).blur(
			function() {
				$( this ).removeClass( "focus" );
			}
	);
	
	$(".value").blur(function(){
		el = $(this);
		if(el.val() != '')
		{
			el.removeClass('input-error');
		}
	});
	
	$("#sendMail").dialog({
		autoOpen:false,
		modal:true,
		buttons:
		{
			"Senden":function()
			{
				$.ajax({
					dataType:"json",
					url:"xhr.php?f=sendmail&sub="+$("#sendMail input:first").val()+"&msg=" + encodeURIComponent($("#sendMail textarea").val() + '&fs=' + $('#sendmail-fs-id').val()),
					success : function(data){
						if(data.status == 1)
						{
							$("#sendMail").dialog('close');
							$("#sendMail textarea").val('');
							info(data.msg);
						}
						else
						{
							alert(data);
						}
					}
				});
			}
		}
	});
	
	$("#uploadPhoto").dialog({
		autoOpen:false,
		modal:true,
		buttons:
		{
			"Upload":function()
			{
				uploadPhoto();
			}
		}
	});
	
	$("#comment").dialog({
		autoOpen:false,
		modal:true,
		buttons:
		{
			"Speichern":function()
			{
				$.ajax({
					dataType:"json",
					url:"xhr.php?f=addComment&name="+$('#comment-name').val()+"&id="+$('#comment-id').val()+"&comment=" + encodeURIComponent($("#comment textarea").val()),
					success : function(data){
						if(data.status == 1)
						{
							$("#comment").dialog('close');
							$("#comment textarea").val('');
							info(data.msg);
						}
						else
						{
							alert(data);
						}
					}
				});
			}
		}
	});
	
	$('.toolbar-comment').click(function(){
		l = $(this).attr('attr').split(':');
		$('#comment-id').val(l[1]);
		$('#comment-name').val(l[0]);
		
		$('#comment').dialog('open');
	});
	
	$("#fancylink").fancybox({
		minWidth : 470,
		maxWidth : 470,
		minHeight: 450,
		scrolling :"auto",
		beforeClose:function(){
			g_firstChatUpdate = true;
		},
		helpers : { 
		  overlay : {closeClick: false}
		}
	});	
});
 
function chat(fsid)
{
	chatWith(fsid);
	/*
	closeDialogs();
	showLoader();
	fancy_xhr("getMsg&id=" + fsid, false);
	*/
}
function closeDialogs()
{
	$('.dialogbox').each(function(){
        if($(this).dialog("isOpen"))
        {
        	$(this).dialog("close");
        }
     });
}
function addbanana(fsid)
{
	$('.vouch-banana').tooltip('close');
	
	$('#fsprofileratemsg-wrapper label').html($('.vouch-banana-title').html());
	$('#fsprofileratemsg-wrapper div.desc').html($('.vouch-banana-desc').html());
	$('#fsprofileratemsg').css({
		'height':'137px',
		'width':'558px'
	});
	$('#fs-profile-rate-comment').dialog('option',{
		width:600,
		height:450
	});
	$('#fs-profile-rate-comment').dialog('open');
}
function login()
{
	ajreq('login',{app:'login'});
}
function profile(id)
{
	//alert(id);
	//fancy_xhr('profile&id='+id);
	showLoader();
	$.ajax({
		dataType:"json",
		url:"xhrapp.php?app=profile&m=quickprofile&id=" + id,
		success : function(data){
			hideLoader();
			if(data.status == 1)
			{
				$('#tabs-profile').tabs("destroy");
				$('#dialog-profile-info').dialog("destroy");
				$("#u-profile").html(data.html);
				$('#tabs-profile').tabs();

				$('#dialog-profile-info').dialog({
				        closeOnEscape: false,
				        draggable: false,
				        resizable: false,
				        autoOpen: true,
				        modal: true,
				        width:470,
				        open: function() {
				            $(this).find('.ui-dialog-titlebar-close').blur();
				        }
				}).parent().find('.ui-dialog-titlebar-close').prependTo('#tabs-profile').closest('.ui-dialog').children('.ui-dialog-titlebar').remove();
				   
				$('#dialog-profile-info').css('padding','0');
				$('.ui-dialog-titlebar-close').css({
				     'position': 'absolute',
					 'right': '8px',
					 'top': '17px'
				});
				$("#tabs-profile").css({
				    "border": "none",
				    "padding": "0"
				});
				$('.vouch-banana').tooltip({
					position: {
						my: "center bottom-20",
						at: "center top",
						using: function( position, feedback ) {
						$( this ).css( position );
						$( "<div>" )
							.addClass( "arrow" )
							.addClass( feedback.vertical )
							.addClass( feedback.horizontal )
							.appendTo( this );
						}
					}
				});
				$('#fsprofileratemsg').val('');

				$('#tabs-profile').tabs('option',{
					heightStyle: 'fill'
				});

				
				/*
				$('.fsrating').jRating({
					step:true,
					length : 10, 
					decimalLength:0,
					bigStarsPath: '/css/icons/zitrone.png',
					sendRequest: false,
					rateMax: 10,
					rateInfosY: 10,
					onClick: function(el,rate){
						showLoader();
						$.ajax({
							url: "xhrapp.php?app=profile&m=rate",
							data:{
								id: id,
								rate: rate
							},
							dataType: "json",
							success: function(data){
								if(data.status == 1)
								{
									$("#ratecountlabel").html(parseInt($("#ratecountlabel").text())+1);
									
									$("#fs-profile-rate-comment").dialog("open");
								}
							},
							complete: function(){
								hideLoader();
							}
						});
					}
				});
				*/
				if(data.script != undefined)
				{
					$.globalEval(data.script);
				}
			}
			else
			{
				error(data.msg);
			}
		},
		complete:function(){
			hideLoader();
		}
	});
	
	/*
	 * helpers:  {
        overlay : {
            css : {
                'background-color' : '#fff'
            }
        }
	 */
}
 
function ajreq(name,options,method,app)
{
	opt = {};
	if(options != undefined)
	{
		opt = options;
	}
	
	if(method == undefined)
	{
		method = "get";
	}
	
	if(app == undefined)
	{
		app = GET('page');
	}
	
	if(opt.app != undefined)
	{
		app = options.app;
	}
	
	if(opt.loader == undefined || opt.loader == true)
	{
		showLoader();
	}
	
	$.ajax({
		url:"xhrapp.php?app="+app+"&m=" + name,
		data: opt,
		dataType:'json',
		method:method,
		success:function(data){
			if(data.status == 1)
			{
				if(data.append != undefined)
				{
					$(data.append).html(data.html);
				}
				
				if(data.script != undefined)
				{
					$.globalEval( data.script );
				}				
			}
		},
		complete:function(){
			hideLoader();
		}
	});
}
var u_pulse_error_to = null;
var u_pulse_info_to = null;
function pulseError(msg,opt)
{
	if(opt == undefined)
	{
		opt = {
			sticky: false
		};
	}
	time = 2000;
	if(opt.sticky)
	{
		time = 900000;
	}
	
	$("#pulse-error").html(msg);
	$("#pulse-error").stop().fadeIn();
	u_pulse_error_to = setTimeout(function(){
		$("#pulse-error").fadeOut();
		$(document).unbind('click');
	},time);
	setTimeout(function(){
		$(document).bind('click',function() {
			$("#pulse-error").stop().fadeOut();
			$(document).unbind('click');
			clearTimeout(u_pulse_error_to);
		});
	},500);
}

function pulseSuccess(msg,opt)
{
	if(opt == undefined)
	{
		opt = {
			sticky: false
		};
	}
	time = 2000;
	if(opt.sticky)
	{
		time = 900000;
	}
	
	$("#pulse-success").html(msg);
	$("#pulse-success").stop().fadeIn();
	u_pulse_error_to = setTimeout(function(){
		$("#pulse-success").fadeOut();
		$(document).unbind('click');
	},time);
	setTimeout(function(){
		$(document).bind('click',function() {
			$("#pulse-success").stop().fadeOut();
			$(document).unbind('click');
			clearTimeout(u_pulse_error_to);
		});
	},500);
}

function pulseInfo(msg,opt)
{
	if(opt == undefined)
	{
		opt = {
			sticky: false
		};
	}
	time = 2000;
	if(opt.sticky)
	{
		time = 900000;
	}
	
	$("#pulse-info").html(msg);
	$("#pulse-info").fadeIn();
	
	u_pulse_info_to = setTimeout(function(){
		$("#pulse-info").fadeOut();
		$(document).unbind('click');
	},time);
	setTimeout(function(){
		$(document).bind('click',function() {
			$("#pulse-info").fadeOut();
			$(document).unbind('click');
			clearTimeout(u_pulse_info_to);
		});
	},500);
}
 
function addHover(sel){$(sel).hover(function() {$( this ).addClass( "hover" );},function() {$( this ).removeClass( "hover" );});}

function infoMenu()
{
	addHover("div#msgBar .bar-item");
	
	$("div#msgBar .bar-msg").click(function(event){
		event.stopPropagation();
		$("#msgbar-messages").toggle();
		$("#msgbar-infos").hide();
		$("#msgbar-basket").hide();
	});
	
	$("#msgbar-messages").click(function(event){
		event.stopPropagation();
	});
	
	$(document).click(function() {
		$("#msgbar-messages").hide();
		$("#msgbar-infos").hide();
	});
	/*msg bar infos*/
	$("div#msgBar .bar-info").click(function(event){
		event.stopPropagation();
		$("#msgbar-infos").toggle();
		$("#msgbar-messages").hide();
		$("#msgbar-basket").hide();
	});
	
	/*msg bar basket*/
	$("div#msgBar .bar-basket").click(function(event){
		event.stopPropagation();
		
		$("#msgbar-infos").hide();
		$("#msgbar-messages").hide();
		if($('#msgbar-basket').is(":visible"))
		{
			$("#msgbar-basket").hide();
			g_interval_newBasket = setInterval(function(){
				ajreq("update",{app:"basket",loader:false});
			},10000);
		}
		else
		{
			clearInterval(g_interval_newBasket);
			if($("#msgbar-basket ul li.msg").length == 0)
			{
				$("#msgbar-basket ul").prepend('<li class="loading">&nbsp;</li>');
			}
			ajreq("loadupdates",{app:"basket",loader:false});
			$("#msgbar-basket").show();
			
		}
		
		event.stopPropagation();
	});
	
	
	$("#msgBar-badge span.bar-info").mouseover(function(){
		$("#msgBar .bar-info").trigger('mouseover');
	});
	$("#msgBar-badge span.bar-info").mouseout(function(){
		$("#msgBar .bar-info").trigger('mouseout');
	});
	
	$("#msgBar-badge span.bar-msg").mouseover(function(){
		$("#msgBar .bar-msg").trigger('mouseover');
	});
	$("#msgBar-badge span.bar-msg").mouseout(function(){
		$("#msgBar .bar-msg").trigger('mouseout');
	});
	
	$("#msgBar-badge span.bar-basket").mouseover(function(){
		$("#msgBar .bar-item.bar-basket").trigger('mouseover');
	});
	$("#msgBar-badge span.bar-basket").mouseout(function(){
		$("#msgBar .bar-item.bar-basket").trigger('mouseout');
	});
	$("#msgBar-badge span.bar-basket").click(function(){
		$("#msgBar .bar-item.bar-basket").trigger('click');
	});
	
	$("#msgBar-badge span.bar-info").click(function(event){
		event.stopPropagation();
		$("div#msgBar .bar-info").trigger('click');
	});
	$("#msgBar-badge span.bar-msg").click(function(event){
		event.stopPropagation();
		$("div#msgBar .bar-msg").trigger('click');
	});
	
	$('#msgBar .bar-item').on("touchstart", function(ev) {
		$(this).addClass('hover');
	});
	$('#msgBar .bar-item').on("touchend", function(ev) {
		$(this).removeClass('hover');
	});
	
	init_chat();
	init_infos();
}
function init_infos()
{
	$.ajax({
		dataType:"json",
		url:"xhr.php?f=getGlocke",
		success : function(data){
			hideLoader();
			if(data.status == 1)
			{
				//alert(data.count);
				$("#msgbar-infos ul").html(data.html);
				if(data.count > 0)
				{
					$('#msgBar-badge .bar-info').html(data.count);
					$('#msgBar-badge .bar-info').css({opacity:1});
				}
			}
			else if(data.status == 0)
			{
				$('#msgBar-badge .bar-info').html('0');
				$("#msgbar-infos ul").html(data.html);
				$('#msgBar-badge .bar-info').css({opacity:0});
			}
		}
	});
}
var g_interval_newMsg = null;
var g_interval_newBasket = null;
function init_chat()
{    
	g_interval_newMsg = checkNewMsg(false);
	ajreq('update',{app:'basket',loader:false});
	g_interval_newBasket = setInterval(function(){
		ajreq('update',{app:'basket',loader:false});
	},10000);
	/*
	setInterval(function(){
		//http://localhost/xhr.php?f=getNewMsg
		checkNewMsg();
		
	},10000);
	*/
	/*
	setInterval(function(){
		if(chatIsOpen())
		{
			updateChat();
		}
	},2000);*/
    
}

function aNotify()
{
	//$('#xhr-chat-notify')[0].play();
}

function chatIsOpen()
{
	if($(".fancybox-opened input#xhr_sender_id").length > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function checkEmail(email) {

    var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    if (!filter.test(email)) 
    {
    	return false;
    }
    else
    {
    	return true;
    }
}
function img(photo)
{
	if(photo.length > 3)
	{
		return 'images/med_q_'+photo;
	}
	else
	{
		return 'img/med_q_avatar.png';
	}
}

function xhr_chat_scroll()
{
	setTimeout(function(){$("#xhr-chat-focus").focus();$('#msganswer').focus();},100);
}
var g_firstChatUpdate=true;
function updateChat()
{
	$.ajax({
		dataType:"json",
		url:"xhr.php?f=updateChat&fsid=" + $("#xhr_sender_id").val() + '&fu=' + g_firstChatUpdate,
		success : function(data){
			hideLoader();
			if(data.status == 1)
			{
				$("ul#xv_message").html(data.html);

				if(data.script != undefined)
				{
					$.globalEval(data.script);
				}
				
				
				if(g_firstChatUpdate)
				{
					$("#scrollbar1").tinyscrollbar_update('bottom');
					g_firstChatUpdate = false;
					
				}
				
				//$(".tinyscroll").css({"overflow":"hidden","height":"400px"});
				
			}
			else if(data.status == 0)
			{
				
				//$("ul#xv_message").html(data.html);
			}
		}
	});
}

function startChat(fsid)
{
	xhr_chat = setInterval(function(){
		alert('get ');
	},1000);
}
function stopChat()
{
	alert('stoppe');
	clearInterval(xhr_chat);
}
function fancy_xhr(func,loader)
{
	if(loader == undefined)
	{
		loader = true;
	}
	if(loader)
	{
		showLoader();
	}
	
	$.ajax({
		dataType:"json",
		url:"xhr.php?f=" + func,
		success : function(data){
			if(data.status == 1)
			{
				$("#fancy").html(data.html);
				$("#fancylink").trigger("click");
				
				if(data.script != undefined)
				{
					$.globalEval(data.script);
				}
			}
			else
			{
				error(data.msg);
			}
		},
		complete:function(){
			if(loader)
			{
				hideLoader();
			}
		}
	});
}

function fancy(content,title,subtitle)
{
	t = '';
	s = '';
	if(title != undefined)
	{
		t = '<h3>'+title+'</h3>';
	}
	if(subtitle != undefined)
	{
		s = '<p class="subtitle">'+subtitle+'</p>';
	}
	$("#fancy").html('<div class="popbox">'+t+s+content+'</div>');
	$("#fancylink").trigger("click");
}

function isMob()
{
	if($( window ).width() < 768)
	{
		return true;
	}
	
	return false;
}

function checkNewMsg(sound)
{
	if(sound == undefined)
	{
		sound = true;
	}
	mob = 0;
	if(isMob())
	{
		mob = 1;
	}
	
	$.ajax({
		dataType:"json",
		url:"xhr.php?f=getNewMsg&mob="+mob,
		success : function(data){
			if(data.status == 1)
			{
				if(data.script != undefined)
				{
					if(sound)
					{
						$.globalEval(data.script);
					}
				}
				$("#msgbar-messages ul").html(data.html);
				$("#msgBar-badge span.bar-msg").html(data.count);
				$("#msgBar-badge span.bar-msg").css({opacity:1});
				/*
				$("#msgbar-messages ul li.msg a").click(function(ev){
					showLoader();
					fancy_xhr("getMsg&id=" + parseInt($(this).attr('href').replace('#','')),false);
					ev.preventDefault();
				});
				*/
				
			}
			else if(data.status == 0)
			{
				$("#msgBar-badge span.bar-msg").css({opacity:0});
				$("#msgbar-messages ul").html(data.html);
			}
			$(".ui-delbuddyreq").click(function(ev){ev.stopPropagation();});
		}
	});
}

function hideBadge()
{
	$('#msgBar-badge').hide();
}
function showBadge()
{
	$('#msgBar-badge').show();
}

function xhrf(func)
{
	showLoader();
	$.ajax({
		dataType:"json",
		url:"xhr.php?f=" + func,
		success : function(data){
			hideLoader();
			if(data.status == 1)
			{
				hideLoader();
			}
		},
		complete:function(){
			hideLoader();
		}
	});
}
 
function reload()
{
	location.reload();
}

function v_field(content,title,id)
{
	return '<div id="'+id+'"><div class="head ui-widget-header ui-corner-top">'+title+'</div><div class="ui-widget ui-widget-content ui-corner-bottom margin-bottom ui-padding">'+content+'</div></div>';
}

function v_hidden(name,val)
{
	return '<input type="hidden" name="'+name+'" class="'+name+'" value="'+val+'" />';
}

function hiddenDialog(id,table,title)
{
	$("#" + id).dialog({
		autoOpen:false,
		modal:true,
		title:title,
		buttons:
		{
			"Speichern":function()
			{
				showLoader();
				$.ajax({
					dataType:"json",
					url:"xhr.php?f=update_"+table+"&" + $('#' + id + ' form').serialize(),
					success : function(data){
						$("#" + id).dialog('close');
						
						if(data.script != undefined)
						{
							$.globalEval(data.script);
						}
						
					},
					complete : function(){
						hideLoader();
					}
				});
			}
		}
	});
}
 
function openPhotoDialog(fs_id)
{
	$("#uploadPhoto-fs_id").val(fs_id);
	$("#uploadPhoto").dialog('open');
}
 
function sendMail(fs_id)
{
	$('#sendmail-fs-id').val(fs_id);
	$("#sendMail").dialog("open");
}


function info(txt)
{
	if($('#info-msg').length == 0)
	{
		$('#top').after('<div class="ui-widget ui-msg"><div class="ui-state-highlight ui-corner-all ui-padding"><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span><ul id="info-msg">'+txt+'</ul><div class="clear"></div></div></div>');
	}
	else
	{
		$('#info-msg').append('<li>'+txt+'</li>');
	}

}
function error(txt)
{
	pulseError(txt);
	/*
	if($('#error-msg').length == 0)
	{
		$('#top').after('<div class="ui-widget ui-msg"><div class="ui-state-error ui-corner-all ui-padding"><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span><ul id="error-msg">'+txt+'</ul><div class="clear"></div></div></div>');
	}
	else
	{
		$('#error-msg').append('<li>'+txt+'</li>');
	}*/
}
 
function uploadPhoto()
{
	$('#uploadPhoto form').submit();
}

function uploadPhotoReady(id,file)
{
	fs_id = $('#uploadPhoto-fs_id').val();
	$("#miniq-"+id).attr('src',file);
	//$('#uploadPhoto-preview').html('<img width="200" src="images/'+fs_id+'.'+ext+'" />');
	$("#uploadPhoto").dialog('close');
	info('Foto erfolgreich hochgeladen!');
}

function addSelect(id)
{
	if($("#" + id + "neu").val().length > 0)
	{
		$.ajax({
			dataType:"json",
			url:"xhr.php?f=add" + ucfirst(id) + "&neu=" + encodeURIComponent($("#" + id + "neu").val()),
			success : function(data){
				
				$("#" + id).append('<option value="'+data.id+'">'+data.name+'</option>');
	
				$("#" + id + "neu").val("");
				$("#" + id + "-dialog").dialog( "close" );
				$("#" + id + " option").removeAttr("selected");
				$("#" + id + " option").last().attr("selected",true);
			}
		});
	}
}

function betrieb(id)
{
	goTo('?page=betrieb&id='+id);
}

function goTo(url)
{
	if(url != '#')
	{
		document.location.href = url;
	}
}

function ucfirst (str) {

	  str += '';
	  var f = str.charAt(0).toUpperCase();
	  return f + str.substr(1);
}

function accordionNext(id,max)
{
	current = $('#' + id).accordion( "option", "active" );
	if(current < max)
	{
		$('#' + id).accordion("option","active",(current+1));
	}
	
}

function showComment(id)
{
	$("#dialog-comment").html($("#comment-" + id).val());
	
	$("#dialog-comment").dialog('option', 'title', $("#comment-title-" + id).val());
	$("#dialog-comment").dialog('open');
	
}

function ajaxconfirm(url,question,title)
{
	if(question != undefined)
	{
		$('#dialog-confirm-msg').html(question);
	}
	if(title != undefined)
	{
		$('#dialog-confirm').dialog('option','title',title);
	}
	
	$('#dialog-confirm-url').val(url);
	$('#dialog-confirm').dialog('open');
	/*
	if(confirm(question))
	{
		goTo(url);
	}
	*/
}

function ifconfirm(url,question,title)
{
	if(question != undefined)
	{
		$('#dialog-confirm-msg').html(question);
	}
	if(title != undefined)
	{
		$('#dialog-confirm').dialog('option','title',title);
	}
	
	$('#dialog-confirm-url').val(url);
	$('#dialog-confirm').dialog('open');
	/*
	if(confirm(question))
	{
		goTo(url);
	}
	*/
}

function picFinish(img,id)
{
	$('#'+id+'-action').val('upload');
	//$("#fotoupload").dialog('close');
	$.fancybox.close();
	d = new Date();
	imgp = img+'?'+d.getTime();
	$('#'+id+'-open').html('<img src="images/'+imgp+'" /><input type="hidden" name="photo" value="'+img+'" />');
	hideLoader();
	reload();
	//$('#fotouploadopen').children('span').html('Foto bearbeiten');
}
function pic_error(msg,id)
{
	msg = '<div class="ui-widget"><div style="padding: 15px;" class="ui-state-error ui-corner-all"><p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span><strong>Fehler:</strong> ' + msg + '</p></div></div>';
	$('#'+id+'-placeholder').html(msg);
	hideLoader();
}
function fotoupload(file,id)
{
	$('#' + id + '-file').val(file);
	d = new Date();
	img = file+'?'+d.getTime();
	
	$('#'+id+'-placeholder').html('<img src="./tmp/'+img+'" />');
	jcrop = $('#'+id+'-placeholder img').Jcrop({
         setSelect:   [ 100, 0, 400, 400 ],
         aspectRatio: 35 / 45,
         onSelect: function(c){
        		$('#'+id+'-x').val(c.x);
        		$('#'+id+'-y').val(c.y);
        		$('#'+id+'-w').val(c.w);
        		$('#'+id+'-h').val(c.h);
         }
     });
	 $('#'+id+'-save').show();
	 $('#'+id+'-save').button().click(function(){
		 showLoader();
		 $('#'+id+'-action').val('crop');
		 $('#'+id+'-form')[0].submit();
		 return false;
	 });
	 
	 $('#'+id+'-placeholder').css('height','auto');
	 hideLoader();
	 setTimeout(function(){
		 $.fancybox.update();
		 $.fancybox.reposition();
		 $.fancybox.toggle();
	 },200);
	 
}

function closeBox()
{
	$.fancybox.close();
}

function pictureReady(id,img)
{
	$('#' + id + '-preview').html('<img src="images/'+id+'/thumb_'+img+'" />');
	$('#' + id).val(id + '/' + img);
	
	$.fancybox.close();
	hideLoader();
}

function pictureCrop(id,img)
{

	ratio = $.parseJSON($('#' + id + '-ratio').val());
	ratio_val = $.parseJSON($('#' + id + '-ratio-val').val());
	
	ratio_i = parseInt($('#' + id + '-ratio-i').val());
	
	if(ratio[ratio_i] != undefined)
	{
		$('#' + id + '-ratio-i').val((ratio_i+1));
		if($('#' + id + '-ratio'))
		{
			//ratio = parseInt($('#' + id + '-ratio').val());
		}
		//alert(id+';'+path);
		$('#' + id + '-crop').html('<img src="images/' + id + '/' + img + '" /><br /><span id="'+id+'-crop-save">Speichern</span>');
		$('#' + id + '-crop img').Jcrop({
	        setSelect:   [ 100, 0, 400, 400 ],
	        aspectRatio: ratio[ratio_i],
	        onSelect: function(c){
	       		$('#'+id+'-x').val(c.x);
	       		$('#'+id+'-y').val(c.y);
	       		$('#'+id+'-w').val(c.w);
	       		$('#'+id+'-h').val(c.h);
	        }
	    });
		hideLoader();
		setTimeout(function(){
			 $.fancybox.update();
			 $.fancybox.reposition();
			 $.fancybox.toggle();
		 },200);
		
		$('#'+id+'-crop-save').button().click(function(){
			ratio_val[ratio_val.length] = {
				x:Math.round($('#'+id+'-x').val()),
				y:Math.round($('#'+id+'-y').val()),
				w:Math.round($('#'+id+'-w').val()),
				h:Math.round($('#'+id+'-h').val())
			}
			$('#' + id + '-ratio-val').val(JSON.stringify(ratio_val));
			
			pictureCrop(id,img);
		});
	}
	else
	{
		showLoader();
		$('#'+id+'-form').attr('action','xhr.php?f=pictureCrop&id='+id+'&img='+img);
		$('#'+id+'-form').submit();
	}
}
function nl2br (str, is_xhtml) {
	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function checkOnline(saver)
{
	$.ajax({
		dataType:"json",
		url:"xhr.php?f=checkOnline",
		data: "saver="+saver,
		success : function(data){
			
			if(data.status == 1)
			{
				for(i=0;i<data.saver.length;i++)
				{
					$(".saver-ampel-"+data.saver[i].id).removeClass("ampel-grau ampel-gruen");
					
					if(data.saver[i].online)
					{
						$(".saver-ampel-"+data.saver[i].id).addClass("ampel-gruen");
						if($(".saver-ampel-"+data.saver[i].id).length > 0  && $(".saver-ampel-"+data.saver[i].id).prev().attr('title') != undefined)
						{
							$(".saver-ampel-"+data.saver[i].id).prev().attr('title',$(".saver-ampel-"+data.saver[i].id).prev().attr('title').replace('offline','online'));
						}
					}
					else if($(".saver-ampel-"+data.saver[i].id).length > 0)
					{
						$(".saver-ampel-"+data.saver[i].id).addClass("ampel-grau");
						$(".saver-ampel-"+data.saver[i].id).prev().attr('title',$(".saver-ampel-"+data.saver[i].id).prev().attr('title').replace('online','offline'));
					}
					$(".saver-ampel-"+data.saver[i].id).prev().tooltip();
				}
			}
		}
	});
}

function u_loadCoords(addressdata,func)
{
	anschrift = "";
	if(addressdata.str != undefined)
	{
		anschrift = addressdata.str + ' ' + addressdata.hsnr;
	}
	else
	{
		tmp = addressdata.anschrift.split("/");
		anschrift = tmp[0];
		
	}
	address = encodeURIComponent(anschrift +', '+ addressdata.plz+', '+addressdata.stadt+', Deutschland');
	
	url = "http://maps.google.com/maps/api/geocode/json?address="+address+"&sensor=false&region=DE&language=de";
	
	showLoader();
	$(document).ready(function(){
	    $.getJSON(url,
	        function(data){
	    		
	            if(data.status == 'OK')
	            {
	            	
	            	for(i=0;i<data.results.length;i++)
	            	{
	            		check = false;
	            		for(y=0;y<data.results[i].address_components.length;y++)
	            		{
	            			if(data.results[i].address_components[y].long_name == addressdata.plz)
	            			{
	            				check = true;
	            			}
	            		}
	            		if(check)
	            		{
	            			$("#pulse-error").hide();
	            			hideLoader();
	            			func(data.results[i].geometry.location.lat,data.results[i].geometry.location.lng);
	            			return true;
	            			break;
	            		}
	            	}
	            }
	            
	            hideLoader();
	            
	            pulseError("<strong>Die Koordinaten konnten nicht berechnet werden</strong><br />sind alle Eingaben Richtig? Ohne Koordinaten wird die Adresse nicht auf der Karte zu sehen sein");
	        });
	  });
}

function showLoader()
{
	$.fancybox.showLoading();
}
function hideLoader()
{
	$.fancybox.hideLoading();
}

function betriebRequest(id)
{
	showLoader();
	$.ajax({
		url: "xhr.php?f=betriebRequest",
		data: { "id": id },
		dataType: "json",
		success: function(data) {
			if(data.status == 1)
			{
				pulseInfo(data.msg);
			}
		},
		complete: function(){
			hideLoader();
		}
	});
	
}

function checkAllCb(sel)
{
	$("input[type=\'checkbox\']").prop("checked", sel);
}

function becomeBezirk()
{
	$("#becomeBezirk-link").fancybox({	
			minWidth : 390,
			maxWidth : 400
		});
	$("#becomeBezirk-link").trigger('click');
}
var search = {
	initiated: false,
	isSearching:false,
	index: false,
	$icon: null,
	$searchbar: null,
	$result:null,
	$indexResult:null,
	$input: null,
	
	init: function(){
		
		this.$icon = $('#searchbar i');
		this.$searchbar = $('#searchbar');
		this.initiated = true;
		this.$result = $('#searchbar .result');
		this.$indexResult = $('#searchbar .index');
		this.$input = $('#searchbar input:first');
			
		if(user.token != undefined && user.token.length > 4)
		{
			$.getJSON( "/cache/searchindex/" + user.token + ".json", function( data ) {
				search.index = data;
			});
		}
		
		this.$input.keyup(function(){
			
			if(search.index !== false && search.index.length > 0)
			{
				search.indexSearch();
			}
			
			if(search.$input.val().length > 3)
			{
				search.start();
			}
			else if(search.$input.val().length == 0)
			{
				search.$result.html('');
				search.$indexResult.html('');
			}
		});
	},
	open: function(){
		if(!this.initiated)
		{
			this.init();
		}
		if(search.$searchbar.is(':visible'))
		{
			search.$searchbar.hide();
		}
		else
		{
			search.$searchbar.show();
			search.$input[0].focus();
		}
		
	},
	indexSearch: function(){
		search.$indexResult.html('');
		for(i=0;i<search.index.length;i++)
		{
			var hasTitle = false;
			for(y=0;y<search.index[i].result.length;y++)
			{
				check = false;
				
				for(x=0;x<search.index[i].result[y].search.length;x++)
				{
					if(
						search.index[i].result[y].search[x].toLowerCase() . 
						indexOf(search.$input.val().toLowerCase())
						>= 0
					)
					{
						check = true;
						x = (search.index[i].result[y].search.length+1);
					}
				}
				if(check)
				{
					if(!hasTitle)
					{
						hasTitle = true;
						search.$indexResult.append('<li class="title">' + search.index[i].title + '</li>');
					}
					click = '';
					href = '#';
					if(search.index[i].result[y].click != undefined)
					{
						click = ' onclick="' + search.index[i].result[y].click + ';return false;"';
					}
					else
					{
						href = search.index[i].result[y].href;
					}
					search.$indexResult.append('<li class="corner-all"><a class="corner-all" href="' + href + '"' + click + '><span class="n">' + search.index[i].result[y].name + '</span><span class="t">' + search.index[i].result[y].teaser + '</span></li>');
				}
			}
		}
	},
	showLoader: function()
	{
		this.$icon.removeClass('fa-search').addClass('fa-spin fa-circle-o-notch');
	},
	hideLoader: function()
	{
		this.$icon.removeClass('fa-spin fa-circle-o-notch').addClass('fa-search');
	},
	showResult: function(result){
		search.$result.html('');
		for(i=0;i<result.length;i++)
		{
			search.$result.append('<li class="title">' + result[i].title + '</li>');
			for(y=0;y<result[i].result.length;y++)
			{
				search.$result.append('<li class="corner-all"><a class="corner-all" href="#" onclick="' + result[i].result[y].click + 'return false;"><span class="n">' + result[i].result[y].name + '</span><span class="t">' + result[i].result[y].teaser + '</span></li>');
			}
		}
	},
	noResult: function()
	{
		search.$result.html('<li class="title">Kein Ergebnis</li>');
	},
	start: function()
	{
		this.showLoader();
		
		if(!this.isSearching)
		{
			this.isSearching = true;
			$.ajax({
				url: 'xhrapp.php?app=search&m=search&s=' + encodeURIComponent(search.$input.val()),
				dataType: 'json',
				success: function(data){
					if(data.result != undefined && data.result.length > 0)
					{
						search.showResult(data.result);
					}
					else
					{
						search.noResult();
					}
				},
				complete: function(){
					setTimeout(function(){
						search.isSearching = false;
						search.hideLoader();
					},200);
				}
			});
		}
	}
};

jQuery.fn.extend({ 
    disableSelection : function() { 
            return this.each(function() { 
                    this.onselectstart = function() { return false; }; 
                    this.unselectable = "on"; 
                    jQuery(this).css('user-select', 'none'); 
                    jQuery(this).css('-o-user-select', 'none'); 
                    jQuery(this).css('-moz-user-select', 'none'); 
                    jQuery(this).css('-khtml-user-select', 'none'); 
                    jQuery(this).css('-webkit-user-select', 'none'); 
            });
return this; 
    } 
});