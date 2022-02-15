$(function(){
    var q = '';
    var currentLink = window.location.href;
    var search_element = {
        elem :$("#s_price"),
        query :'pp'
    }
    
    
    
    search_element.elem.find('a').each(function(c_index) {

        if(currentLink.indexOf(search_element.query)>=0){
            var select_class = currentLink.split("&"+search_element.query+"=")[1].split("|"); 
        }
        if( typeof select_class != 'undefined'){
            var id_tostring = ''+c_index                  //将数字转换为字符串
            if($.inArray(id_tostring,select_class) >= 0){
                this.style.backgroundColor="#ccc";
            }
            
            /*
            if(select_class.indexOf(c_index) >= 0){   //fix bugs 存在子匹配
                this.style.backgroundColor="#ccc";
            }
            */
        }
        $(this).click(function(){         
            var reg = new RegExp(search_element.query+"=");
            if(reg.test(currentLink)){
                var url_array = currentLink.split("&"+search_element.query+"=");
                var q_ids = url_array[1].split("|");
                //console.log(q_ids);
                var id_tostring = ''+c_index
                var index = q_ids.indexOf(id_tostring);
                console.log(index)
                if(index >=0){
                    q_ids.splice(index,1);
                    
                }else{ 
                    q_ids.push(c_index);
                }
                if(q_ids && q_ids.length>0){
                    currentLink = url_array[0]+"&"+search_element.query+"="+q_ids.join('|');
                }else{
                    currentLink = url_array[0];
                }
                //console.log(q_ids);
            }else{
                currentLink=currentLink+'&'+search_element.query+'='+c_index;
            }
            //console.log(currentLink)
            window.location.href = currentLink;
         
            if(this.style.backgroundColor == "rgb(204, 204, 204)"){
                this.style.backgroundColor="#FFF";
            }else{
                this.style.backgroundColor="#ccc";
            }
            //this.style.backgroundColor="#ccc";

            })
        })

    
    
    
})