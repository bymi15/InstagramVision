function displayAlert(text, type){
  $("#alert_message").empty();
  $("#alert_message").append("<div class='alert alert-" + type + "'><a href'#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" + text + "</div>");
}

function updateStatusText(text){
  $("#status_text").empty();
  $("#status_text").append(text);
}

function resetForm(){
  $("#img_url").val("");
  $("#alert_message").val("");
}

function chooseImage(input){
    var file = input.files[0];
    var formData = new FormData();
    formData.append('upload', file);
    formData.append('file', 'file');

    $('#spinner').show();

    updateStatusText("Uploading image to server...");

    $.ajax({
      url: "processUploadImage.php",
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
          displayAlert("Done uploading image to server.", "info");
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
    $('#spinner').show();

    $('#btnUpload').prop('disabled', true);

    $.ajax({
      url: "processUploadImage.php",
      type: "POST",
      data: {
        'email' : $("#email").val(),
        'pass' : $("#pass").val(),
        'msg' : $("#msg").val(),
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
          displayAlert("<strong>Success!</strong> Your image has been uploaded: <a style="color:white;" href='" + data.value + "'>" + data.value + "</a>", "success");
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
    $('#spinner').show();

    updateStatusText("Analysing image...");

        $.ajax({
            url: "https://westus.api.cognitive.microsoft.com/vision/v1.0/describe?maxCandidates=1",
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

  var hashes = "";
  $.each(json, function(index, data) {
    if(data.tags != null){
      for(i = 0; i < data.tags.length; i++){
        hashes = hashes + "#" + data.tags[i] + " ";
      }
    }
  });

  var msg = $("#msg").val();
  $("#msg").val(msg + "\n\n" + hashes);

  displayAlert("<strong>Analysis completed!</strong> Your image has been analysed and the following tags were generated: " + hashes, "success");

  $('#spinner').hide();
  updateStatusText("");
}
