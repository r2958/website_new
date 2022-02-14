$(document).ready(function(){
    
    /*
    $('.backToTop a').click(function(){
         $("body").animate({ scrollTop: 0 }, 400);
        })
    var $backToTopFun = function(){
        var st = $(document).scrollTop(), winh = $(window).height();
        (st > 0)? $('.backToTop').show(): $('.backToTop').hide();
    }
    $(window).bind("scroll", $backToTopFun);
    */
    

    $('#backToTop').click(function(){
     var goTop = setInterval(function(){
      $(window).scrollTop($(window).scrollTop()/1.1)
      if($(window).scrollTop() < 1) clearInterval(goTop);
     },8);  
     return false;
    });
    $(window).scroll(function(){
     var top = $(window).scrollTop();
     if(top > 200) {
      $('.backToTop').show();
     } else {
      $('.backToTop').hide();
     }
    });
    
    $("a").bind("focus",function(){if(this.blur)this.blur();});
    
    
    //start element hover event for products list;
    $('.product_desc img').hover(function(){
           /*
            var current_img_data = $(this).attr("data_orig");
            if(current_img_data%2==0){
                this.src='./media/images/test1.jpg'
            }else{
                this.src='./media/images/test.jpg'
            }
            */
            /*
            if(this.alt=='1_s' || this.alt=='5_s' || this.alt=='6_s'){
                this.src='test1.jpg'
            }else{
                this.src='test.jpg'
            }
            */
        },
        function(){
            // this.src='./media/images/test2.jpg';
        }
        )
    //end element hover event for products list;
    
    //start top menu hover
    /*
    $("#top-nav-container a").click(function(){
        $(".nav_list").hide();
        });
        */
    

    var timer_handler = null;
    $("#top-nav-container a").hover(
        
        function(){
            clearTimeout(timer_handler);
            switch(this.id){
                case 'nav_list':
                    $('.nav_image').html('<img src="promo.jpg" style="" />');
                    break;
                case 'new_brand':
                    $('.nav_image').html('<img src="menu_2.jpg" style="" />');
                    break;
                case 'new_cloth':
                    $('.nav_image').html('<img src="menu_1.jpg" style="" />');
                    break;
                default:
                     $('.nav_image').html('<img src="promo.jpg" style="" />');
                    break;
            }
            $(".nav_content").show();
            return false;
        },
        function(){
            timer_handler = setTimeout(function(){
                $(".nav_content").hide();
                
                },500);
        }
                                    
    );
  
    
    $(".nav_content").hover(function(){
        clearTimeout(timer_handler);        
        },
        function(){
            $(".nav_content").hide();
        });
    
    $(".nav_content").hide();
    
    
    $(".cart-link").hover(function(){
            //alert('I am in');
            $.ajax({
                    type: "GET",
                    cache: false,
                    url: '/public/mycart.php',
                    data: "",
                    success: function(returndata) {
                            //alert(returndata);
                            try{
                                    $(".thread_list").html(returndata);
                                    $(".cart_content").show();
                                    
                            }catch(err){
                                    if(!(err instanceof SyntaxError)){
                                            throw new Error('Unexpected error');
                                    }
                            }
    
                    },
                    error: function() {
                            alert("更新购物车失败");
                    }
            });
            
            
        },
        function(){
            //alert('I am out');
            })
    
   
    
    //end top menu hover
    /*
    $(".image_gally ul li img").click(function(){
        switch (this.src){
            case 'http://ntest/yuyanhua/p_icon2.jpg':
                $(".mid_image").attr("src","p2.jpg");
                $(".bigzoom").attr("href","p2_b.jpg");
                //
                break;
            default :
                $(".mid_image").attr("src","p0.jpg");
                 $(".bigzoom").attr("href","p0_big.jpg");
                
                break;
            
        }
        return false;
        
        
        });
    */
    /*
    $(".submit_button").click(function(frm){
            alert('xxxxx');
            return false;
        
        });
        */


    
    $(".jqzoom").jqzoom({
                title : false,
                zoomType: 'innerzoom',
                preloadText : '图片加载中,请稍后...'
        
        });
    
    $(".product_sku").change(function(){
        $('.product_sku').css('border','1px solid #ccc');
        $('.size_error').css('display','none');
        
        });
    
    
    $(".add_to_bag").click(function(){
            $data = $('.product_sku').val();
            if($data == -1){
                $('.product_sku').css('border','1px solid red');
                $('.size_error').css('display','');
                return false;
            }
       
            $att_arr = $data.split('_');
            $str = 'ProductID='+$att_arr[0]+'&AttributeID='+$att_arr[1]+'&Qty=1';
            //alert($str);
            
            
            $url = 'mycart.php?'+$str;

            
            
            $icon =$('.zoomThumbActive').attr('rel');
            
            //var img = './media/images/p0.jpg';
            var img = '/images/details/'+$att_arr[0]+"_01_th_56x84_80.jpg";
            var html= "<img src='"+img+"' />";
            
            
            $.ajax({
                    type: "GET",
                    cache: false,
                    url: $url,
                    data: "",
                    success: function(returndata) {
                            //alert(returndata);
                            try{
                                    $(".thread_list").html(returndata);
                                    
                            }catch(err){
                                    if(!(err instanceof SyntaxError)){
                                            throw new Error('Unexpected error');
                                    }
                            }
    
                    },
                    error: function() {
                            alert("更新购物车失败");
                    }
            });

            
            

            

            var offsetEnd = $(".cart").offset();
            
            var flyStartOffset =$(".rw").offset();


            
            $(".rw").css({top : 300, left :'50%'})
                    .html(html)
                    .fadeIn()
                    .animate({top : 150, left : '70%'}, 600, function () {
                            $('.rw').fadeOut();
                            //$('.cart_content').slideDown(600);
                            $('.cart_content').show();
                             });
 
        
            
            return false;
        
        });
    
  
    $(".close").click(function(){
          $(".cart_content").slideUp(100);

      })
    
    $(".cancle_button").click(
        function(){
            closeWindow();
        }
    )
    
        
    
    /*
    $("#left_select a").click(function(){
        
            var current_class = $(this).attr('class');

            if(typeof current_class === "undefined" || current_class =='unselected' || current_class == ""){
                //
                $(this).attr("class","");
                $(this).addClass("selected");
            }else{
                $(this).attr("class","");
                $(this).addClass("unselected");
            }

        });
    
    
*/
    
    /*
    
    
          $.ajax({
                    type: "GET",
                    async:false,
                    url: 'http://n.myopen.vip.com/address/address?areaid=0&is_show_gat=0',
                    dataType : "jsonp",
                    jsonpCallback:"address_api",
                    success: function(returndata) {
                            optionStr='';
                            $.each(returndata.list,function(i,val){
                                   optionStr+='<option value='+val.id+'>'+val.name+'</option>';

                            })
			    $("#province").html(optionStr);
                    },
                    error: function() {
                            console.log("failed");
                    }
            });

	    $("#province").change(function(){
                var provinceID = '';
                provinceID = $(this).children('option:selected').val();
                purl = 'http://n.myopen.vip.com/address/address?areaid='+provinceID+'&is_show_gat=0';
                $("#area").html('<option>请选择</option>');
                //console.log(purl);
        
        
                  $.ajax({
                            type: "GET",
                            async:false,
                            url: purl,
                            dataType : "jsonp",
                            jsonpCallback:"address_api",
                            success: function(returndata) {
                                    optionStr='';
                                    $.each(returndata.list,function(i,val){
                                           optionStr+='<option value='+val.id+'>'+val.name+'</option>';
        
                                    })
                        // console.log(optionStr);
                                    $("#city").html(optionStr);
                            },
                            error: function() {
                                    console.log("failed");
                            }
                    });

	     })
        
        
        
	    $("#city").change(function(){
                var areaID = '';
                areaID = $(this).children('option:selected').val();
                //console.log(areaID);
       
                aurl = 'http://n.myopen.vip.com/address/address?areaid='+areaID+'&is_show_gat=0';
                console.log(aurl);
        
        
                  $.ajax({
                            type: "GET",
                            async:false,
                            url: aurl,
                            dataType : "jsonp",
                            jsonpCallback:"address_api",
                            success: function(returndata) {
                                    optionStr='';
                                    $.each(returndata.list,function(i,val){
                                           optionStr+='<option value='+val.id+'>'+val.name+'</option>';
        
                                    })
                         //console.log(optionStr);
                                    $("#area").html(optionStr);
                            },
                            error: function() {
                                    console.log("failed");
                            }
                    });
  

	     })
    
    */
    
    

});




function cartremove(prodID){
    //$(".thread_list").html("<p>更新中...</p>");

    var pid = prodID.split('-');
    
    
    var $url = '/public/mycart.php?func=remove&ProductID='+pid[0]+'&AttributeID='+pid[1];
    $.ajax({
            type: "GET",
            cache: false,
            url: $url,
            data: "",
            success: function(returndata) {
                    try{
                        $(".thread_list").html(returndata);
                            
                    }catch(err){
                        if(!(err instanceof SyntaxError)){
                                throw new Error('Unexpected error');
                        }
                    }

            },
            error: function() {
                    alert("更新购物车失败");
            }
    });
    
    
}


function submitForm(frm){
    alert('xxxxx');
    return false;
}


// obj 当前被点击的对象，msg 对象的类型
function openWindow(obj,msg){
    //console.log(obj.type);
    
    if(obj.type ==='add_addr'){
        $('.white_content').css('width','800px')
        $('.white_content').css('height','500px')
        $('.white_content').html($('#J_popaddress_template').html());
        
    }
    
    var white_contet_width = eval($('.white_content').css('width').substr(0,3));
    var currentWindowWidth = window.document.body.clientWidth;
    var toleft = (currentWindowWidth-white_contet_width)/2

    $('.white_content').css('left',toleft)
    $('.white_content').css('display','block');
    document.getElementById('fade').style.display='block';
    
    var $url;
    console.log(obj.type);
    switch(obj.type){
        case 'add_addr':
            msg = '添加新地址';
            break;
        case 'orderpay':
            msg = '已经完成支付？';
            $('.white_content').html($('#J_popdelwindow_template').html());
            break;
        case '':
            break;
        default:
            msg = '确认要删除吗？';
            $('.white_content').html($('#J_popdelwindow_template').html());
    }
    console.log(msg);
    $("#pop_msg").html('<h3>'+msg+'</h3>');
    /*
    if (typeof msg == "undefined") {
        msg="确认要删除吗？";
    }
    */
    
    //start pay type dialog
    if (typeof obj != "undefined" && obj.type == "orderpay") {
        //code
        switch (obj.topage) {
            //case
            case 'unionpay':
            case 'aplipay':
            case 'paypal':
                $url = '/public/order/check_order_status.php';
                break;
            case 'doc':
                $url = '';
                break;
            default:
                $url = '';
        }
        
        if (msg !="") {
            $("#pop_msg").html('<h3>'+msg+'</h3>');
        }
        //
        document.getElementById('light').style.display='block';
        document.getElementById('fade').style.display='block';
        $(".confirm_button").click(
            function(){
                //alert('checking');
                var $html = '<div style="height:100px;line-height:100px;padding-top:50px;"><img src="/public/media/images/zoomloader.gif" /></div>';
                $("#pop_msg").html($html);
                $.ajax({
                        type: "GET",
                        cache: false,
                        url: $url,
                        data: "",
                        success: function(rs) {
                           // var redata = eval(returndata);
                           var order = eval('(' + rs + ')');
                            if(order.order_status==1){
                                closeWindow();
                                window.location.href='/public/users/user.php';
                                return;
                            }else{
                                $("#pop_msg").html($html+"失败");
                            }
                        },
                        error: function() {
                                closeWindow();
                                alert("订单支付失败了，请重试");
                                return;
                        }
                });
                
            }
        )
    }
    //end pay type dialog
    
    
    
    
    

    

    var orderID;
    if (typeof obj != "undefined" && obj.type=="remove_order") {
        //code
        orderID = obj.getAttribute('value');
        var $url= '/public/users/userapi.php?func=removeOrder&orderID='+orderID;
        
    }
    


    



    $(".confirm_button").click(
        function(){
            closeWindow();
            if ($url == "") {
                //code
                window.location.href="/public/order/detail.php";
                return;
            }
            
            $.ajax({
                    type: "GET",
                    cache: false,
                    url: $url,
                    data: "",
                    success: function(returndata) {
                        if(returndata.code==200){
                        }
                    },
                    error: function() {
                            alert("删除订单返回出错了");
                    }
            });
             setTimeout(function(){
                window.location.reload();
             },1000);
        }
    )
    

    

    
    
}
function closeWindow(){
    document.getElementById('light').style.display='none';
    document.getElementById('fade').style.display='none';
}

function deleteOrder(){
    
}











