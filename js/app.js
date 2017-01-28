function chooseImage(input){
    $("#error_message").empty();

    var file = input.files[0];
    var formData = new FormData();
    formData.append('upload', file);
    formData.append('file', 'file');

    $('#spinner').show();

    $.ajax({
      url: "processUploadImage.php",
      data: formData,
      type: "POST",
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(data){
        if(data.err){
          $("#error_message").append(data.value);
        }else{
          $("#img_url").val(data.value);
          $('<input type="hidden">').attr({
              id: 'upload_name',
              name: 'upload_name',
              value: data.value2
          }).appendTo('form');

          analyseImage(data.value);
        }
      },
      error:function(ts){
        $("#error_message").append('Error occurred during ajax request.');
        $("#error_message").append(ts.responseText);
      }
    });
}

function uploadImage(){
    $("#error_message").empty();

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
        'upload_name' : $("#upload_name").val()
      },
      dataType: 'json',
      success: function(data){
        if(data.err){
          $("#error_message").append(data.value);
        }else{
          alert("Success!<br>" + data.value);
          //bootstrap alert
          //display successfully uploaded file
        }
      },
      error:function(ts){
        $("#error_message").append('Error occurred during ajax request.');
        $("#error_message").append(ts.responseText);
      },
      complete: function(){
        $('#spinner').hide();
        $('#btnUpload').prop('disabled', false);
      }
      });
}

function analyseImage(imgURL){
    $("#error_message").empty();

    $('#spinner').show();

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
            $('#spinner').hide();
            $("#error_message").append('Error! Could not analyse the image.');
        });
}

function generateHashes(json){
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

  alert("Tags were added: " + hashes);

  $('#spinner').hide();
}
