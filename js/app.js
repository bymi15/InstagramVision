//global variable
var tags = [];

$(document).ready(function() {
  $('input[type="range"]').rangeslider({
    polyfill : false,
    onInit : function() {
        this.output = $( '<div class="range-output text-center" />' ).insertAfter( this.$range ).html( this.$element.val() + "%" );
    },
    onSlide : function( position, value ) {
        this.output.html( value + "%" );
        updateHashTags();
    }
  });

  $("input:checkbox").change(function(){
    updateExtraHashes();
  });
});

function updateExtraHashes(){
  var hash1 = $("#include_hash_tags_1");
  var hash2 = $("#include_hash_tags_2");
  var hash3 = $("#include_hash_tags_3");
  var hash4 = $("#include_hash_tags_4");

  var hashes = "";

  if(hash1.prop('checked')){
    hashes = hashes + "#love #instagood #me #tbt #cute #follow #followme #photooftheday #happy #amazing #smile #follow4follow #like4like #look #instalike #picoftheday ";
  }
  if(hash2.prop('checked')){
    hashes = hashes + "#nature #sky #sun #summer #beach #beautiful #pretty #sunset #sunrise #blue #flowers #night #tree #twilight #clouds ";
  }
  if(hash3.prop('checked')){
    hashes = hashes + "#animals #animal #pet #dog #cat #dogs #cats #photooftheday #pets #nature #petstagram #petsagram ";
  }
  if(hash4.prop('checked')){
    hashes = hashes + "#food #foodporn #yum #instafood #yummy #amazing #sweet #dinner #lunch #breakfast #fresh #tasty #delicious #foodpics #hungry ";
  }

  $("#extra_hash_tags").val(hashes);
}

function updateHashTags(){
  var hashes = "";

  if(tags != null){
    var val = $('input[type="range"]').val() / 100;

    for(var i = 0; i < tags.length; i++){
        //hashes = hashes + "#" + tags[i]['name'] + " ";
      if(tags[i]['confidence'] >= val){
        hashes = hashes + "#" + tags[i]['name'] + " ";
      }
    }

    $("#hash_tags").val(hashes);

    return hashes;
  }
}

function displayAlert(text, type){
  $("#alert_message").empty();
  $("#status_text").empty();
  
  var alert = $("<div id='alert_dialog' class='alert alert-" + type + "'><a href'#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" + text + "</div>").hide();
  
  
  $("#alert_message").append(alert);
  
  alert.fadeIn(2000);
        
  window.setTimeout(function () {
    // close the dialog
    alert.fadeTo(300, 0.5).slideUp(2000, function () {
      alert.alert('close');
    });
  }, 4000);
}

function updateStatusText(text){
  $("#alert_message").empty();
  $("#status_text").empty();
  $("#status_text").append(text);
}

function resetForm(){
  $("#img_url").val("");
  $("#msg").val("");
}

function validateHashes(hashes){
  if(hashes.trim().split(" ").length >= 30){
    return false;
  }
  return true;
}

function validateCaption(caption){
  if(caption.length >= 2100){
    return false;
  }
  return true;
}

function chooseImage(input){
    var file = input.files[0];
    var formData = new FormData();
    formData.append('upload', file);
    formData.append('file', 'file');

    $('#spinner').show();

    updateStatusText("Uploading image to server...");

    $.ajax({
      url: "server.php",
      data: formData,
      type: "POST",
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(data){
        $('#spinner').hide();
        if(data.err){
          updateStatusText("");
          displayAlert(data.value, "danger");
        }else{
          updateStatusText("");
          displayAlert("Done uploading image to server.", "success");
          $("#img_url").val(data.value);
          $('<input type="hidden">').attr({
              id: 'delete_hash',
              name: 'delete_hash',
              value: data.value2
          }).appendTo('form');
        }
      },
      error:function(ts){
        updateStatusText("");
        $('#spinner').hide();
        displayAlert(ts.responseText, "danger");
      }
    });
}

function uploadImage(){
    if(!$("#img_url").val().trim()){
      displayAlert("Please upload an image.", "danger");
      return;
    }else if(!validateHashes($("#hash_tags").val() + $("#extra_hash_tags").val())){
      displayAlert("Instagram does not allow more than 30 hash tags per post.", "danger");
      return;
    }else if(!validateCaption($("#msg").val() + $("#hash_tags").val() + $("#extra_hash_tags").val())){
      displayAlert("Instagram does not allow more than 2200 characters per post.", "danger");
      return;
    }

    $('#spinner').show();

    $('#btnUpload').prop('disabled', true);

    updateStatusText("Uploading image to instagram...");

    $.ajax({
      url: "server.php",
      type: "POST",
      data: {
        'email' : $("#email").val(),
        'pass' : $("#pass").val(),
        'msg' : $("#msg").val(),
        'hash_tags' : $("#hash_tags").val(),
        'extra_hash_tags' : $("#extra_hash_tags").val(),
        'img_url' : $("#img_url").val(),
        'img_format' : $('input[name=img_format]:checked', '#form').val(),
        'delete_hash' : $("#delete_hash").val()
      },
      dataType: 'json',
      success: function(data){
        $('#btnUpload').prop('disabled', false);
        $('#spinner').hide();
        if(data.err){
          updateStatusText("");
          displayAlert(data.value, "danger");
        }else{
          updateStatusText("");
          resetForm();
          displayAlert("<strong>Success!</strong> Your image has been uploaded: <a style='color:white;' href='" + data.value + "'>" + data.value + "</a>", "success");
        }
      },
      error:function(ts){
        $('#btnUpload').prop('disabled', false);
        $('#spinner').hide();
        updateStatusText("");
        displayAlert('Error occurred during ajax request.', "danger");
        displayAlert(ts.responseText, "danger");
      }
    });
}

function analyseImage(imgURL){
    if(!imgURL.trim()){
      displayAlert("Please upload an image.", "danger");
      return;
    }

    $('#spinner').show();

    updateStatusText("Analysing image...");

        $.ajax({
            url: "https://westus.api.cognitive.microsoft.com/vision/v1.0/tag",
            dataType: 'json',
            beforeSend: function(xhrObj){
                // Request headers
                xhrObj.setRequestHeader("Content-Type", "application/json");
                xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key", "a9497404e2a94e69b8c254c867908f0d");
            },
            type: "POST",
            // Request body
            data: '{"url": "' + imgURL + '"}',
        })
        .done(function(data) {
            generateHashes(data);
        })
        .fail(function() {
            updateStatusText("");
            $('#spinner').hide();
            $("#error_message").append('Error! Could not analyse the image.');
        });
}

function generateHashes(json){
  updateStatusText("Generating image hash tags...");
  var jsonString = JSON.stringify(json);

  var data = JSON.parse(jsonString);
  tags = data['tags'];

  var hashes = updateHashTags();

  displayAlert("<strong>Analysis completed!</strong> Your image has been analysed and the following tags were generated: " + hashes, "success");

  $('#spinner').hide();
}
