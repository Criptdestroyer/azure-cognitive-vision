<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;


if(isset($_POST['submit'])){
    $connectionString = "DefaultEndpointsProtocol=https;AccountName=mywebapp;AccountKey=5xr1D0rHi0ot4Notm4yBjfvV1z5XjwEQGtaarY07Cc+cgvwCN2kTLbzJd22GS5pTRoNTwBvqDnbpxbdHKoavLw==;EndpointSuffix=core.windows.net";
    // Create blob client.
    $blobClient = BlobRestProxy::createBlobService($connectionString);
    $target_dir = "image/";
    $target_file = $target_dir . basename($_FILES["inputImage"]["name"]);

    if (move_uploaded_file($_FILES["inputImage"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["inputImage"]["name"]). " has been uploaded.<br>";
    } else {
        ?>
            <script>
                alert("Sorry, there was an error uploading your file.");
                window.location.href = "index.php";
            </script>
        <?php
    } 
    $fileToUpload = $target_file;

    if (!isset($_GET["Cleanup"])) {
        // Create container options object.
        $createContainerOptions = new CreateContainerOptions();
        $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

        // Set container metadata.
        $createContainerOptions->addMetaData("key1", "value1");
        $createContainerOptions->addMetaData("key2", "value2");

        $containerName = "blockblobs".generateRandomString();

        try {
            // Create container.
            $blobClient->createContainer($containerName, $createContainerOptions);

            // Getting local file so that we can upload it to Azure
            $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
            fclose($myfile);
            
            # Upload file as a block blob
            echo "Uploading BlockBlob: ".PHP_EOL;
            echo $_FILES["inputImage"]["name"];
            echo "<br />";
            
            $content = fopen($fileToUpload, "r");

            //Upload blob
            $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

            // List blobs.
            $listBlobsOptions = new ListBlobsOptions();
            $listBlobsOptions->setPrefix($target_file);

            echo "These are the blobs present in the container: ";
            $str = "";
            do{
                $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
                foreach ($result->getBlobs() as $blob)
                {
                    $str = $blob->getUrl();
                }
            
                $listBlobsOptions->setContinuationToken($result->getContinuationToken());
            } while($result->getContinuationToken());
            echo $str."<br />";
            session_start();
            $_SESSION['url'] = $str;
            header("Location: index.php");

            // Get blob.
            // echo "This is the content of the blob uploaded: ";
            // $blob = $blobClient->getBlob($containerName, $fileToUpload);
            // fpassthru($blob->getContentStream());
            // echo "<br />";
        }
        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        catch(InvalidArgumentTypeException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
    } 
    else 
    {

        try{
            // Delete container.
            echo "Deleting Container".PHP_EOL;
            echo $_GET["containerName"].PHP_EOL;
            echo "<br />";
            $blobClient->deleteContainer($_GET["containerName"]);
        }
        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
    }
}
    ?>
<!-- 


<form method="post" action="phpQS.php?Cleanup&containerName=<?php echo $containerName; ?>">
    <button type="submit">Press to clean up all resources created by this sample</button>
</form> -->
