var $weatherLoader = $("#weather_info_loader")
var $sunLoader = $("#sun_info_loader")
var $wrap = $("#weather_wrap")
var $weatherInfo = $("#weather_info")
var $sunInfo = $("#sun_info")
var $tabs = $('#tabs')


var wd_config = {
    city_code : "",
    city_name : "",
    country_name : "",
    lat : 0,
    lng : 0,
    user_timezone : "",
    default_timezone : "America/Winnipeg",
    default_lat : 49.899,
    default_lng : -97.137,
    default_city_name : "WINNIPEG",
    tabs : ''
}

var wd_service = {

    getWeatherDetail : function() {
        $weatherLoader.show()
        $weatherInfo.hide()
        //document.write(wd_config.lat+" "+wd_config.lng+" "+wd_config.usertimezone)
        $.ajax({
            url : '/mbweather/weather_detail_by_city.php',
            data : 'name=' + wd_config.city_name,
            dataType : 'json'
        })
            .done(function(response) {

                $('#curr_temp').html(response.curr_temp+'℃'  )
                $('#curr_cond').html(response.curr_cond || "N/A")
                $('#curr_img').html(response.curr_img)
                $('#ext_days0').html(response.ext_days0)
                $('#ext_days1').html(response.ext_days1)
                $('#ext_days2').html(response.ext_days2)
                $('#ext_cond0').html(response.ext_cond0 || "N/A")
                $('#ext_cond1').html(response.ext_cond1 || "N/A")
                $('#ext_cond2').html(response.ext_cond2 || "N/A")
                $('#ext_low_temp0').html('Low: '+response.ext_low_temp0+'℃'  )
                $('#ext_low_temp1').html('Low: '+response.ext_low_temp1+'℃'  )
                $('#ext_low_temp2').html('Low: '+response.ext_low_temp2+'℃' )
                $('#ext_high_temp0').html('High: '+response.ext_high_temp0+'℃' )
                $('#ext_high_temp1').html('High: '+response.ext_high_temp1+'℃' )
                $('#ext_high_temp2').html('High: '+response.ext_high_temp2+'℃' )
                $weatherInfo.fadeIn("slow")
            })
            .fail(function() { console.log ("getWeatherDetail Ajax Fail")})
            .then(function() { $weatherLoader.hide();$weatherInfo.fadeIn("slow")})

    },
    getSunInfo : function (){
        $sunLoader.show()
        $sunInfo.hide()
        $.ajax({
            url: '/mbweather/get_sun_info.php',
            data: 'lat=' + wd_config.lat + '&lng=' + wd_config.lng + '&timezone=' + wd_config.user_timezone,
            dataType: 'json'
        })
        .done(function(response) {
            $('#sunrise0').html("Sunrise: "+response.sunrise0)
            $('#sunrise1').html("Sunrise: "+response.sunrise1)
            $('#sunrise2').html("Sunrise: "+response.sunrise2)
            $('#sunrise3').html("Sunrise: "+response.sunrise3)
            $('#sunset0').html("Sunset: "+response.sunset0)
            $('#sunset1').html("Sunset: "+response.sunset1)
            $('#sunset2').html("Sunset: "+response.sunset2)
            $('#sunset3').html("Sunset: "+response.sunset3)

        })
        .fail(function() { console.log ("getSunInfo Ajax Fail")})
        .then(function() { $sunLoader.hide();$sunInfo.fadeIn('slow')})
    },

    getGeoInfo : function(){
        $.ajax({
            type: "GET",
            url: "/mbweather/cityinfo.xml",
            dataType: "xml"
        })
        .done(function(xml) {
            count = 0
            //console.log($(xml).text()) //debug
            $(xml).find("city").each(function() {

                if( wd_config.city_name == $(this).find("name").text().trim().toUpperCase() )
                {
                    wd_config.lat = $(this).find("latitude").text()
                    wd_config.lng = $(this).find("longitude").text()
                    wd_service.getSunInfo()

                }
            })

        })
        .fail(function() { console.log ("getGeoInfo Ajax Fail")})
    },


    init : function() {
        //wd_config.city_name = $.cookie("weatherDetailCityManitobaCN")
        //wd_config.lat = $.cookie("weatherDetailLatManitobaCN")
        //if("" == wd_config.city_name || null == wd_config.city_name) {
        //$.ajaxSetup({cache : false})


        wd_config.tabs = $tabs.tabs()


        $('.ui-state-active').removeClass("ui-tabs-selected ui-state-active")
        $.ajax({
            url: '/mbweather/query_geoip.php',
            dataType: 'json',
        })
            .done(function(response) {

                if("" == response.ip_city || null == response.ip_city  ||  'MB' != response.ip_region )
                {
                    wd_config.city_name = wd_config.default_city_name
                    wd_config.lat = wd_config.default_lat
                    wd_config.lng = wd_config.default_lng
                    wd_config.user_timezone = wd_config.default_timezone
                }
                else
                {
                    wd_config.city_name = response.ip_city
                    wd_config.country_name = response.ip_country
                    wd_config.user_timezone = response.timezone
                    wd_config.lat = response.ip_lat
                    wd_config.lng = response.ip_lng
                }


                $('#welcome_msg').html("Welcome, We forecast Manitoba weather for you.")

                wd_service.getWeatherDetail()
                wd_service.getSunInfo()


                //$('#weather_location').html(""+wd_config.city_name+" ")

            })//end ajax


        $tabs.bind('tabsselect', function(event, ui) {
            event.preventDefault()
            //event.cancelBubble = true

            var selected = $('#tabs').tabs('option', 'selected')
            wd_config.city_name = $(ui.tab).html()//.find("a").text().trim().toUpperCase()

            $('.ui-state-active').removeClass("ui-tabs-selected ui-state-active")
            $(ui.tab).addClass("ui-tabs-selected ui-state-active")

            wd_service.getWeatherDetail()
            wd_service.getGeoInfo()


            //$wrap.ready($('#weather_location').html(""+wd_config.city_name+" "))
            //$('#tabNav').find('li').attr('class','tabNotSelected')
            //$(this).attr('class','tabSelected')
        })
    }//end init
}//end service

wd_service.init()

