<!DOCTYPE html>
<html>
<head>
    <title>Analyze Sample</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>
<body>
 
<script type="text/javascript">
    function processImage() {
        var subscriptionKey = "1b3020af6f944f32a2089960e2561639";
        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
 
        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };
 
        // Display the image.
        var sourceImageUrl = document.getElementById("inputImage").value;
        console.log(sourceImageUrl);
        document.querySelector("#sourceImage").src = sourceImageUrl;
 
        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),
 
            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
 
            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            var temp = JSON.stringify(data, null, 2);
            var obj = JSON.parse(''+temp);
            $("#responseTextArea").text(obj.description.captions[0].text);
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    };
</script>
 
<h1>Analyze image:</h1>
Upload image, to <strong>Analyze image</strong>.
<br><br>

<form action="phpQS.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="inputImage">
    <br><br>
    <input type="submit" name="submit" value="upload">
</form>
<br>


<br><br>
<div id="wrapper" style="width:1020px; display:table;">
    <div id="imageDiv" style="width:420px; display:table-cell;">
        Source image:
        <?php
            session_start();
            if(isset($_SESSION['url'])){
                ?>
                    <input id="inputImage" value="<?=$_SESSION['url']?>">
                    <script>
                        $(document).ready(function() {
                            processImage()
                        });
                    </script>
                <?php        
            }
            session_destroy();
        ?>
        <br><br>
        <img id="sourceImage" width="400" />
    </div>
    <div id="jsonOutput" style="width:600px; display:table-cell;">
        Response:
        <br><br>
        <h1 id="responseTextArea" class="UIInput"></h1>
    </div>
</div>
</body>
</html>