/**
* Google Maps User Manager
* @author  Kucher Denis   <deniskutcher@gmail.com>
* @copyright Copyright &copy; 2017 Denis Kucher 
* @created 2017.11.28
*/
$.widget('nm.googlemapsusermanager', {

    options: {
        dummy: 'dummy',
        markers:[],
        markerPr:null,
        infowindow:null,
        contentInfowindow:'',
        users:{},
        map:null,
    },

    _create: function () {
        var widget = this;
        
        this.element.on('click','.switch-lang button',function(e){
            e.preventDefault();
            widget.changeLang($(this));
        });

        this.element.on('click','#manage-users',function(e){
            e.preventDefault();
            widget.clearMarkers();
            widget.showManager();
        });
        
        this.element.on('click','#back-to-map',function(e){
            e.preventDefault();
            widget.showMap();
            widget.clearMarkers();
            widget.getUsers(false);
        });

        this.element.on('click','a.edit-user',function(e){
            e.preventDefault();
            var id = $(this).closest('tr').data('id');
            widget.getUser(id);
        });

        this.element.on('click','#back-to-usermanager',function(e){
            e.preventDefault();
            $('#add-user-block').hide();
            $('#usermanager').show();
        });

        this.element.on('click','.delete-user',function(e){
            e.preventDefault();
            var id = $(this).closest('tr').data('id');
            widget.deleteUser(id);
            widget.options.markers.splice(id, 1);
        });

        this.element.on('click','#add-user',function(e){
            e.preventDefault();
            widget.clearFormForAddUser();
            widget.showAddUserFrom();
        });

        this.element.on('click','button.send-add',function(e){
            e.preventDefault();
            widget.clearFormError();
            widget.addNewUser();
        });

        this.element.on('click','button.send-edit',function(e){
            e.preventDefault();
            var id = $(this).closest('form.form-horizontal').attr('data-id');
            widget.clearFormError();
            widget.updateUser(id);
        });

        this.element.on('click','#update-users',function(e){
            widget.clearMarkers();
            widget.getUsers(false);
        });

        this.element.on('change','#users-select',function(e){
            var id = $(this).val();
            widget.showUserMap(widget.options.users[id]);
        });
        
        this._initApp();
    },

    _initApp:function(){
        var widget = this;
        widget.drawBlockAddUser();
        widget.drawBlockUserManager();
        widget.drawBlockApp();

        widget.getUsers(true, function(){widget.initMap()});
        widget.addUserForm();
    },
	
    drawBlockAddUser:function(){
        var widget = this;
        widget.element.append(
            $('<div/>',{id:'add-user-block'}).css('display','none').append(
                $('<form/>',{class:'form-horizontal', role:'form'})
            )
        )
    },

    drawBlockUserManager:function(){
        var widget = this;
        widget.element.append(
            $('<div/>',{id:'usermanager'}).css('display','none').append(
                $('<div/>',{class:'header-usermanager'}).append(
                    $('<a/>',{href:'#', id:'back-to-map'}).text('Back'),
                    $('<a/>',{href:'#', id:'add-user'}).text('Add new user')
                ),
                $('<div/>',{class:'user-table-block'}).append(
                    $('<table/>',{id:'user-table', class:'table table-bordered'}).append(
                        $('<thead/>').append(
                            $('<tr/>').append(
                                $('<td/>').text('Image'),
                                $('<td/>').text('Name'),
                                $('<td/>').text('Address'),
                                $('<td/>').text('Action')
                            )
                        ),
                        $('<tbody/>')
                    )
                )
            )
        )
    },

    drawBlockApp:function(){
        var widget = this;
        widget.element.append(
            $('<div/>',{id:'application'}).append(
                $('<div/>',{id:'application'}).append(
                    $('<div/>',{id:'header'}).append(
                        $('<a/>',{href:'#', id:'manage-users'}).text('Manage users'),
                    ),
                    $('<p/>',{class:'title'}).text('Выберите пользователя:'),
                    $('<div/>',{id:'users'}).append(
                        $('<select/>',{name:'users-select',id:'users-select'}).append(
                            $('<option/>',{id:'default-option'}).text('----')
                        )
                    ),
                    $('<button/>',{id:'update-users',class:'btn btn-primary', type:'submit'}).text('Update users'),
                    $('<div/>',{id:'map'})
                )
            )
        );
    },

    getUsers:function(createmarkers, _callBackFn){
        var widget = this;
        widget.options.users = [];
        sendRequest({
            action: 'googlemaps.getusers',
            successHandler: function (_callbackParams) {
                var response = _callbackParams.response;
                if (!response.success) {
                    alert(response.message);
                }
                else{
                    var resp = response.data.users;
                    for (var i = 0; i < resp.length; i++) {
                        widget.options.users[resp[i].id] = resp[i];
                    }
                    widget.refreshSelectUser(widget.options.users);
                    if (createmarkers) widget.createMarkers();
                    if (_callBackFn) _callBackFn();
                }
            }
        });
    },

    initMap:function (){
        var widget =this;
        for (var i in widget.options.users) {
            var lat = Number(widget.options.users[i].lat);
            var lng = Number(widget.options.users[i].lng);
            break;
        };
        var latLng = {lat: lat, lng: lng};
        widget.options.map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: latLng,
            mapTypeId: 'terrain'
        });
        widget.options.infowindow = new google.maps.InfoWindow({
            content: widget.options.contentInfowindow
        });
    },

    showUserMap:function (_user){
        var widget = this;
        var marker = widget.options.markers[_user.id];
        if (widget.options.markerPr) widget.setMarkerMap(null,widget.options.markerPr);
        widget.setMarkerMap(widget.options.map, marker);
        widget.options.markerPr = widget.options.markers[_user.id];
        widget.showMessage(marker,_user);
    },
    
    // Adds a marker to the map and push to the array.
    addMarker:function(user) {
        var widget =this;
        var location = {lat:Number(user.lat),lng:Number(user.lng)};
        var marker = new google.maps.Marker({
          position: location,
            animation: google.maps.Animation.DROP,
            map: widget.options.map
        });
        widget.options.markers[user.id] = marker;
    },

    createMarkers:function (){
        var widget = this;
        widget.deleteMarkers();
        widget.options.markers.length = 0;
        for (var i in widget.options.users) {
            var user = widget.options.users[i];
            var marker = new google.maps.Marker({
                position: {lat:Number(user.lat),lng:Number(user.lng)},
                map: widget.options.map
            });
        widget.options.markers[user.id] = marker;
        }
    },

    setMapOnAll:function (_map) {
        var widget = this;
        // var map = _map || widget.options.map;
        for (var i in widget.options.markers) {
            widget.options.markers[i].setMap(_map);
        }
    },

    setMarkerMap:function (map, marker) {
        var widget = this;
        marker.setMap(map);
    },
    
    // Removes the markers from the map, but keeps them in the array.
    clearMarkers:function () {
        var widget = this;
       widget.setMapOnAll(null);
    },

    // Deletes all markers in the array by removing references to them.
    deleteMarkers:function () {
        var widget = this;
        widget.clearMarkers();
        widget.options.markers = [];
        // markers.length = 0;
    },

    showMessage:function (_marker, user){
        var widget = this;
        var contentInfowindow = '<div id="content">'+
            '<h5 id="firstHeading" class="firstHeading">'+user.name+'</h5>'+
            '<div id="bodyContent">'+
            '<p>'+user.index+' '+user.country+' '+user.city+'<br>'+user.street+','+user.house+'</p>'+
            '</div>'+
            '</div>';
        widget.options.infowindow.setContent(contentInfowindow);
        widget.options.infowindow.open(map, _marker);
        _marker.addListener('click', function() {
            widget.options.infowindow.open(map, _marker);
        });
    },
  
    addNewUser:function (){
        var widget = this;
        var form = $('#add-user-block form');
        var data = form.serializeObject();
        sendRequest({
            action: 'googlemaps.createuser',
            data: {data:data},
    
            successHandler: function (_callbackParams) {
                var response = _callbackParams.response;
                if (!response.success) {
                    var errors = JSON.parse(response.message);
                    $.each(errors, function(i, val) {
                        widget.element.find('form #input'+i).closest('div.form-group').addClass('has-error');
                        widget.element.find('form #input'+i).next().text(val).css('color','red');
                    });
                    
                }
                else{
                    var user = response.data.user;
                    widget.drawRowTable(user);
                    widget.addMarker(user);
                    $('#add-user-block').hide();
                    $('#usermanager').show();
                }
            }
        });
    },

    updateUser:function (_id){
        var widget =this;
        var form = $('#add-user-block form');
        var data = form.serializeObject();
        data['id'] = _id;
        sendRequest({
            action: 'googlemaps.updateuser',
            data: {data:data},
    
            successHandler: function (_callbackParams) {
                var response = _callbackParams.response;
                if (!response.success) {
                    var errors = JSON.parse(response.message);
                    $.each(errors, function(i, val) {
                        widget.element.find('form #input'+i).closest('div.form-group').addClass('has-error');
                        widget.element.find('form #input'+i).next().text(val).css('color','red');
                    });
                }
                else{
                    widget.getUsers(true, function(){widget.showManager()});
                    $('#add-user-block').hide();
                    $('#usermanager').show();
                }
            }
        });
    },

    clearFormError:function(){
        var widget = this;
        var form = widget.element.find('#add-user-block form');
        form.find('span.error-form').text('');
        form.find('div.form-group').removeClass('has-error');
    },

    showAddUserFrom:function (){
        var widget = this;
        $('#usermanager').hide();
        $('#add-user-block').show();
    },

    addUserForm:function (){
        $('#add-user-block').prepend(
            $('<div/>',{id:'header-add-user-block'}).append(
                $('<a/>',{id:'back-to-usermanager', href:'#'}).text('Back')
            )
        );
        var form = $('#add-user-block form').empty();
        form.append(
            $('<h1/>',{class:'col-sm-offset-3 col-sm-6'}).text('Add user'),
            $('<div/>',{class:'form-group responsive-label'}).append(
                $('<label/>',{class:'col-sm-offset-3 col-sm-6 control-label'}).attr('for','inputname').text('Name').css('text-align','left'),
                $('<div/>',{class:'col-sm-offset-3 col-sm-6'}).append(
                    $('<input/>',{class:'form-control', type:'text', id:'inputname', name:'name'}),
                    $('<span/>',{class:'error-form'})
                )
            ),
            $('<div/>',{class:'form-group responsive-label'}).append(
                $('<label/>',{class:'col-sm-offset-3 col-sm-6 control-label'}).attr('for','inputaddress').text('Address Example:12345 Ukraine Kharkiv Plekhanivska,135/139').css('text-align','left'),
                $('<div/>',{class:'col-sm-offset-3 col-sm-6'}).append(
                    $('<input/>',{class:'form-control', type:'text', id:'inputaddress', name:'address'}),
                    $('<span/>',{class:'error-form'})
                )
            ),
            $('<div/>',{class:'footer-form col-sm-offset-3 col-sm-6'}).append(
                $('<button>',{class:'btn btn-primary send-edit'}).text('Submit')
            )
        );
    },

    deleteUser:function (_id){
        var widget = this;
        sendRequest({
            action: 'googlemaps.deluser',
            data: {id: _id},
    
          successHandler: function (_callbackParams) {
                var response = _callbackParams.response;
                if (!response.success) {
                    alert(response.message);
                }
                else{
                    $('tr[data-id='+_id+']').remove();
                }
            }
        });
    },

    getUser:function (_id){
        var widget = this;
        sendRequest({
            action: 'googlemaps.getuser',
            data: {id: _id},
        
              successHandler: function (_callbackParams) {
                var response = _callbackParams.response;
                if (!response.success) {
                    alert(response.message);
                }
                else{
                    var user = response.data.user;
                    widget.editUserForm(user);
                }
            }
        });
    },

    clearFormForAddUser:function (){
        var widget = this;
        var form = $('#add-user-block form'); 
        form.find('#inputname').val('');
        form.find('#inputaddress').val('');
        form.find('button').removeClass('send-edit').addClass('send-add');
        form.find('h1').text('Add user');
    },

    editUserForm:function (_user){
        var widget = this;
        $('#usermanager').hide();
        var form = $('#add-user-block form'); 
        var address = _user.index+' '+_user.country+' '+_user.city+' '+_user.street+', '+_user.house;
        form.attr('data-id',_user.id);
        form.find('button').removeClass('send-add').addClass('send-edit');
        $('#inputname').val(_user.name);
        $('#inputaddress').val(address);
        form.find('h1').text('Edit user');
        $('#add-user-block').show();
    },

    showManager:function (){
        var widget = this;
        $('#application').hide();
        $('#usermanager').show();
        var tbody = $('#user-table tbody').empty();
        for (var i in widget.options.users) {
            var user = widget.options.users[i];
            widget.drawRowTable(user);
        }
    },

    showMap:function (){
        var widget = this;
        $('#usermanager').hide();
        $('#application').show();
    },
  
    drawRowTable:function (_user){
        var widget = this;
        var tbody = $('#user-table tbody');
        var address = _user.index+' '+_user.country+' '+_user.city+' '+_user.street+', '+_user.house;
        tbody.append(
            $('<tr/>').attr('data-id',_user.id).append(
                $('<td/>').text(_user.image),
                $('<td/>').text(_user.name),
                $('<td/>').text(address),
                $('<td/>').append(
                    $('<a/>',{href:'#', class:'edit-user'}).text('Edit'),
                    $('<a/>',{href:'#', class:'delete-user'}).text('Delete')
                )   
            )
        )
    },

    refreshSelectUser:function (_items){
        var widget = this;
        var selectEl = $('#users-select');
        selectEl.find('option:not(#default-option)').remove();
        for (var i in _items) {
            var user = _items[i];
            selectEl.append(
                $('<option/>').attr({'data-lat':user.lat,'data-lng':user.lng}).text(user.name).val(user.id)
            )
        };
        selectEl.find('#default-option').attr({'selected':'selected','disabled':'disabled'});
    }

});
(function($) {      // поиск и удаление класса по шаблону // $('p').removeClassWild("status_*");
    $.fn.removeClassWild = function(mask) {
        return this.removeClass(function(index, cls) {
            var re = mask.replace(/\*/g, '\\S+');
            return (cls.match(new RegExp('\\b' + re + '', 'g')) || []).join(' ');
        });
    };
})(jQuery);
